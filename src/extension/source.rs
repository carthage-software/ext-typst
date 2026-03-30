use std::path::{Path, PathBuf};
use std::sync::atomic::{AtomicI64, Ordering};

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use typst::syntax::{FileId, VirtualPath};

use super::error;

static NEXT_SOURCE_ID: AtomicI64 = AtomicI64::new(1);

#[php_class]
#[php(name = "Typst\\Source")]
#[php(flags = ClassFlags::Final)]
pub struct Source {
    id: i64,
    world_id: i64,
    file_id: FileId,
    text: String,
    root: PathBuf,
}

impl Source {
    pub fn new(text: String, root: PathBuf, file_id: FileId, world_id: i64) -> Self {
        Self {
            id: NEXT_SOURCE_ID.fetch_add(1, Ordering::Relaxed),
            world_id,
            file_id,
            text,
            root,
        }
    }

    pub fn world_id(&self) -> i64 {
        self.world_id
    }

    pub fn file_id(&self) -> FileId {
        self.file_id
    }

    pub fn text(&self) -> &str {
        &self.text
    }

    pub fn root(&self) -> &Path {
        &self.root
    }
}

#[php_impl]
impl Source {
    #[php(name = "getId")]
    pub fn get_id(&self) -> i64 {
        self.id
    }

    #[php(name = "getText")]
    pub fn get_text(&self) -> String {
        self.text.clone()
    }
}

/// Creates a `Source` from a string, using the given root as the template directory.
pub fn load_string(source: String, root: &Path, world_id: i64) -> Source {
    let file_id = FileId::new(None, VirtualPath::new("/main.typ"));

    Source::new(source, root.to_owned(), file_id, world_id)
}

/// Creates a `Source` from a file path.
///
/// If `path` is relative and `root` is provided, resolves it relative to `root`.
pub fn load_file(path: &str, root: Option<&Path>, world_id: i64) -> PhpResult<Source> {
    let file_path = PathBuf::from(path);
    let file_path = if file_path.is_relative() {
        if let Some(root) = root {
            root.join(&file_path)
        } else {
            file_path
        }
    } else {
        file_path
    };

    let abs_path = std::fs::canonicalize(&file_path).map_err(|e| {
        error::throw_runtime(
            error::RuntimeException::FILE_NOT_FOUND,
            format!("Failed to resolve '{}': {e}", file_path.display()),
        )
    })?;

    let content = std::fs::read_to_string(&abs_path).map_err(|e| {
        error::throw_runtime(
            error::RuntimeException::FILE_NOT_FOUND,
            format!("Failed to read '{}': {e}", abs_path.display()),
        )
    })?;

    let root = abs_path.parent().unwrap_or(Path::new(".")).to_owned();

    let file_name = abs_path.file_name().unwrap_or_default().to_string_lossy();
    let vpath = VirtualPath::new(format!("/{file_name}"));
    let file_id = FileId::new(None, vpath);

    Ok(Source::new(content, root, file_id, world_id))
}
