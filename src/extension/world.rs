use std::collections::HashMap;
use std::path::PathBuf;
use std::sync::Arc;
use std::sync::atomic::{AtomicI64, Ordering};

use ext_php_rs::binary::Binary;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use crate::internal::fonts::{FontManagerInner, SharedFonts};

use super::error;
use super::source::{self, Source};

static NEXT_WORLD_ID: AtomicI64 = AtomicI64::new(1);

/// PHP-facing `Typst\World` class.
///
/// The compilation environment: manages fonts, source loading, and configuration.
/// Shared across `Compiler` and `Inspector` instances.
#[php_class]
#[php(name = "Typst\\World")]
#[php(flags = ClassFlags::Final)]
pub struct World {
    id: i64,
    configured_root: PathBuf,
    package_dir: Option<PathBuf>,
    cache_size: usize,
    font_inner: Arc<FontManagerInner>,
}

impl World {
    pub fn id(&self) -> i64 {
        self.id
    }

    pub fn configured_root(&self) -> &std::path::Path {
        &self.configured_root
    }

    pub fn package_dir(&self) -> Option<&std::path::Path> {
        self.package_dir.as_deref()
    }

    pub fn cache_size(&self) -> usize {
        self.cache_size
    }

    pub fn font_inner(&self) -> &Arc<FontManagerInner> {
        &self.font_inner
    }

    /// Create a clone of this world's configuration.
    /// Shares base `SharedFonts` via Arc, copies instance fonts.
    /// The clone keeps the same world ID so sources created by the original
    /// remain compatible.
    pub fn clone_from(original: &Self) -> Self {
        Self {
            id: original.id,
            configured_root: original.configured_root.clone(),
            package_dir: original.package_dir.clone(),
            cache_size: original.cache_size,
            font_inner: FontManagerInner::clone_from(&original.font_inner),
        }
    }
}

#[php_impl]
impl World {
    #[php(defaults(
        template_dir = None,
        cache_size = None,
        embed_default_fonts = None,
        font_dirs = None,
        package_dir = None
    ))]
    pub fn __construct(
        template_dir: Option<&str>,
        cache_size: Option<i64>,
        embed_default_fonts: Option<bool>,
        font_dirs: Option<Vec<String>>,
        package_dir: Option<String>,
    ) -> PhpResult<Self> {
        let cache_size = cache_size.unwrap_or(64);
        if cache_size < 0 {
            return Err(error::throw_invalid_argument(format!(
                "Cache size must be >= 0, got {cache_size}"
            )));
        }

        let configured_root = template_dir.as_ref().map_or_else(
            || std::env::current_dir().unwrap_or_else(|_| PathBuf::from(".")),
            PathBuf::from,
        );

        let embed_default = embed_default_fonts.unwrap_or(true);
        let dirs: Vec<PathBuf> = font_dirs
            .unwrap_or_default()
            .into_iter()
            .map(PathBuf::from)
            .collect();

        let shared =
            SharedFonts::new(&dirs, embed_default).map_err(error::throw_invalid_argument)?;
        let font_inner = Arc::new(FontManagerInner::new(Arc::new(shared)));

        #[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
        Ok(Self {
            id: NEXT_WORLD_ID.fetch_add(1, Ordering::Relaxed),
            configured_root,
            package_dir: package_dir.map(PathBuf::from),
            cache_size: cache_size as usize,
            font_inner,
        })
    }

    #[php(name = "addFontData")]
    pub fn add_font_data(&self, data: Binary<u8>) -> PhpResult {
        self.font_inner
            .add_font_data(data.into())
            .map_err(|e| error::throw_runtime(error::RuntimeException::FONT_INVALID, e))
    }

    #[php(name = "addFontFile")]
    pub fn add_font_file(&self, path: String) -> PhpResult {
        let path = PathBuf::from(path);
        let data = std::fs::read(&path).map_err(|e| {
            error::throw_runtime(
                error::RuntimeException::FILE_NOT_FOUND,
                format!("Failed to read font file '{}': {e}", path.display()),
            )
        })?;

        self.font_inner
            .add_font_data(data)
            .map_err(|e| error::throw_runtime(error::RuntimeException::FONT_INVALID, e))
    }

    #[php(name = "loadString")]
    pub fn load_string(&self, input: String) -> Source {
        source::load_string(input, &self.configured_root, self.id)
    }

    #[php(name = "loadFile")]
    pub fn load_file(&self, path: &str) -> PhpResult<Source> {
        source::load_file(path, Some(&self.configured_root), self.id)
    }

    /// Returns the font family names available in this world.
    #[php(name = "getFontFamilies")]
    pub fn get_font_families(&self) -> Vec<String> {
        self.font_inner.font_families()
    }

    #[php(name = "__debugInfo")]
    pub fn debug_info(&self) -> HashMap<String, String> {
        let mut map = HashMap::new();
        map.insert(
            "templateDir".to_string(),
            self.configured_root.display().to_string(),
        );
        map.insert(
            "packageDir".to_string(),
            self.package_dir
                .as_ref()
                .map_or_else(|| "(none)".to_string(), |p| p.display().to_string()),
        );
        map.insert("cacheSize".to_string(), self.cache_size.to_string());
        map.insert(
            "sharedFonts".to_string(),
            self.font_inner.shared_font_count().to_string(),
        );
        map.insert(
            "instanceFonts".to_string(),
            self.font_inner.instance_font_count().to_string(),
        );
        map
    }
}
