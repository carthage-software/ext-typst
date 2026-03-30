use std::collections::HashMap;
use std::path::{Path, PathBuf};
use std::sync::Arc;
use std::time::SystemTime;

use chrono::Datelike;
use chrono::Timelike;
use parking_lot::Mutex;
use typst::Library;
use typst::LibraryExt;
use typst::World;
use typst::diag::FileError;
use typst::diag::FileResult;
use typst::diag::PackageError;
use typst::foundations::Bytes;
use typst::foundations::Datetime;
use typst::foundations::Dict;
use typst::syntax::FileId;
use typst::syntax::Source;
use typst::text::Font;
use typst::text::FontBook;
use typst::utils::LazyHash;
use typst_syntax::package::PackageSpec;

use super::fonts::FontManagerInner;

struct CachedFile {
    mtime: SystemTime,
    bytes: Bytes,
}

struct CachedSource {
    mtime: SystemTime,
    source: Source,
}

pub struct TypstWorld {
    root: PathBuf,
    main_id: FileId,
    main_source: Source,
    library: LazyHash<Library>,
    book: LazyHash<FontBook>,
    font_inner: Arc<FontManagerInner>,
    /// The generation at which we last rebuilt the book.
    book_generation: u64,
    package_dir: Option<PathBuf>,
    cache_size: usize,
    file_cache: Mutex<HashMap<FileId, CachedFile>>,
    source_cache: Mutex<HashMap<FileId, CachedSource>>,
    path_cache: Mutex<HashMap<FileId, FileResult<PathBuf>>>,
}

impl TypstWorld {
    pub fn new(
        root: PathBuf,
        font_inner: Arc<FontManagerInner>,
        package_dir: Option<PathBuf>,
        cache_size: usize,
    ) -> Self {
        let main_id = FileId::new(None, typst::syntax::VirtualPath::new("/main.typ"));
        let main_source = Source::new(main_id, String::new());
        let library = LazyHash::new(Library::builder().build());
        let book = font_inner.build_book();
        let book_generation = font_inner.generation();

        Self {
            root,
            main_id,
            main_source,
            library,
            book,
            font_inner,
            book_generation,
            package_dir,
            cache_size,
            file_cache: Mutex::new(HashMap::new()),
            source_cache: Mutex::new(HashMap::new()),
            path_cache: Mutex::new(HashMap::new()),
        }
    }

    /// Configure the world for compiling the given source.
    ///
    /// Takes primitive types so the internal module stays decoupled from
    /// the PHP-facing `Source` class.
    pub fn set_source(&mut self, file_id: FileId, text: &str, root: &Path, inputs: Dict) {
        if self.root != root {
            root.clone_into(&mut self.root);
            self.path_cache.lock().clear();
        }

        self.main_id = file_id;
        self.main_source = Source::new(self.main_id, text.to_string());
        self.library = LazyHash::new(Library::builder().with_inputs(inputs).build());

        // Rebuild font book if fonts were added since last compilation.
        let current_gen = self.font_inner.generation();
        if current_gen != self.book_generation {
            self.book = self.font_inner.build_book();
            self.book_generation = current_gen;
        }
    }

    pub fn font_inner(&self) -> &Arc<FontManagerInner> {
        &self.font_inner
    }

    pub fn package_dir(&self) -> Option<&Path> {
        self.package_dir.as_deref()
    }

    pub fn cache_size(&self) -> usize {
        self.cache_size
    }

    /// Clears all internal caches (file, source, path) and returns
    /// the total number of entries that were cleared.
    pub fn clear_cache(&self) -> usize {
        let files = {
            let mut cache = self.file_cache.lock();
            let n = cache.len();
            cache.clear();
            n
        };
        let sources = {
            let mut cache = self.source_cache.lock();
            let n = cache.len();
            cache.clear();
            n
        };
        let paths = {
            let mut cache = self.path_cache.lock();
            let n = cache.len();
            cache.clear();
            n
        };
        files + sources + paths
    }

    fn resolve_path(&self, id: FileId) -> FileResult<PathBuf> {
        let mut cache = self.path_cache.lock();
        if let Some(result) = cache.get(&id) {
            return result.clone();
        }

        let result = self.resolve_path_inner(id);
        cache.insert(id, result.clone());
        result
    }

    fn resolve_path_inner(&self, id: FileId) -> FileResult<PathBuf> {
        if let Some(package) = id.package() {
            let package_root = self.resolve_package(package)?;
            id.vpath()
                .resolve(&package_root)
                .ok_or(FileError::AccessDenied)
        } else {
            id.vpath()
                .resolve(&self.root)
                .ok_or(FileError::AccessDenied)
        }
    }

    fn resolve_package(&self, spec: &PackageSpec) -> FileResult<PathBuf> {
        let package_dir = self.package_dir.as_ref().ok_or_else(|| {
            FileError::Package(PackageError::Other(Some(
                "No package directory configured. Set packageDir in SourceResolver.".into(),
            )))
        })?;

        let dir = package_dir
            .join(spec.namespace.as_str())
            .join(spec.name.as_str())
            .join(spec.version.to_string());

        if dir.is_dir() {
            Ok(dir)
        } else {
            Err(FileError::Package(PackageError::NotFound(spec.clone())))
        }
    }

    fn evict_if_full<V>(cache: &mut HashMap<FileId, V>, limit: usize) {
        if limit == 0 || cache.len() >= limit {
            cache.clear();
        }
    }

    fn read_file_cached(&self, id: FileId) -> FileResult<Bytes> {
        if self.cache_size == 0 {
            return self.read_file_uncached(id);
        }

        let path = self.resolve_path(id)?;
        let mtime = std::fs::metadata(&path)
            .and_then(|m| m.modified())
            .map_err(|e| FileError::from_io(e, &path))?;

        {
            let cache = self.file_cache.lock();
            if let Some(entry) = cache.get(&id)
                && entry.mtime == mtime
            {
                return Ok(entry.bytes.clone());
            }
        }

        let data = std::fs::read(&path).map_err(|e| FileError::from_io(e, &path))?;
        let bytes = Bytes::new(data);

        let mut cache = self.file_cache.lock();
        Self::evict_if_full(&mut cache, self.cache_size);
        cache.insert(
            id,
            CachedFile {
                mtime,
                bytes: bytes.clone(),
            },
        );

        Ok(bytes)
    }

    fn read_file_uncached(&self, id: FileId) -> FileResult<Bytes> {
        let path = self.resolve_path(id)?;
        let data = std::fs::read(&path).map_err(|e| FileError::from_io(e, &path))?;
        Ok(Bytes::new(data))
    }

    fn read_source_cached(&self, id: FileId) -> FileResult<Source> {
        if self.cache_size == 0 {
            return self.read_source_uncached(id);
        }

        let path = self.resolve_path(id)?;
        let mtime = std::fs::metadata(&path)
            .and_then(|m| m.modified())
            .map_err(|e| FileError::from_io(e, &path))?;

        {
            let cache = self.source_cache.lock();
            if let Some(entry) = cache.get(&id)
                && entry.mtime == mtime
            {
                return Ok(entry.source.clone());
            }
        }

        let content = std::fs::read_to_string(&path).map_err(|e| FileError::from_io(e, &path))?;
        let source = Source::new(id, content);

        let mut cache = self.source_cache.lock();
        Self::evict_if_full(&mut cache, self.cache_size);
        cache.insert(
            id,
            CachedSource {
                mtime,
                source: source.clone(),
            },
        );

        Ok(source)
    }

    fn read_source_uncached(&self, id: FileId) -> FileResult<Source> {
        let path = self.resolve_path(id)?;
        let content = std::fs::read_to_string(&path).map_err(|e| FileError::from_io(e, &path))?;
        Ok(Source::new(id, content))
    }
}

impl World for TypstWorld {
    fn library(&self) -> &LazyHash<Library> {
        &self.library
    }

    fn book(&self) -> &LazyHash<FontBook> {
        &self.book
    }

    fn main(&self) -> FileId {
        self.main_id
    }

    fn source(&self, id: FileId) -> FileResult<Source> {
        if id == self.main_id {
            return Ok(self.main_source.clone());
        }

        self.read_source_cached(id)
    }

    fn file(&self, id: FileId) -> FileResult<Bytes> {
        self.read_file_cached(id)
    }

    fn font(&self, index: usize) -> Option<Font> {
        self.font_inner.font(index)
    }

    #[allow(clippy::cast_possible_truncation)]
    fn today(&self, offset: Option<i64>) -> Option<Datetime> {
        let naive = if let Some(offset) = offset {
            let utc = chrono::Utc::now().naive_utc();
            utc + chrono::Duration::hours(offset)
        } else {
            chrono::Local::now().naive_local()
        };

        Datetime::from_ymd_hms(
            naive.year(),
            naive.month() as u8,
            naive.day() as u8,
            naive.hour() as u8,
            naive.minute() as u8,
            naive.second() as u8,
        )
    }
}

// SAFETY: All interior-mutable state (`file_cache`, `source_cache`, `path_cache`)
// is protected by `parking_lot::Mutex`, which is itself `Send + Sync`. The remaining
// fields are either immutable or inherently `Send + Sync`.
unsafe impl Send for TypstWorld {}
unsafe impl Sync for TypstWorld {}
