use ext_php_rs::binary::Binary;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use crate::extension::error;

#[php_class]
#[php(name = "Typst\\Output\\Pdf")]
#[php(implements(super::PhpInterfaceOutputInterface))]
#[php(flags = ClassFlags::Final)]
pub struct Pdf {
    data: Vec<u8>,
    page_count: usize,
}

impl Pdf {
    pub fn from_raw(data: Vec<u8>, page_count: usize) -> Self {
        Self { data, page_count }
    }
}

#[php_impl]
#[allow(clippy::cast_possible_wrap)]
impl Pdf {
    #[php(defaults(offset = None, limit = None))]
    pub fn bytes(&self, offset: Option<i64>, limit: Option<i64>) -> PhpResult<Binary<u8>> {
        super::slice_data(&self.data, offset, limit)
    }

    pub fn size(&self) -> i64 {
        self.data.len() as i64
    }

    pub fn page_count(&self) -> i64 {
        self.page_count as i64
    }

    pub fn save(&self, path: &str) -> PhpResult {
        std::fs::write(path, &self.data).map_err(|e| {
            error::throw_runtime(
                error::RuntimeException::WRITE_FAILED,
                format!("Failed to write PDF to '{path}': {e}"),
            )
        })?;
        Ok(())
    }

    pub fn __to_string(&self) -> Binary<u8> {
        self.data.clone().into()
    }
}
