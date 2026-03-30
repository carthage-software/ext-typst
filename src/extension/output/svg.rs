use ext_php_rs::binary::Binary;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use crate::extension::error;

#[php_class]
#[php(name = "Typst\\Output\\Svg")]
#[php(implements(super::PhpInterfaceOutputInterface))]
#[php(flags = ClassFlags::Final)]
pub struct Svg {
    data: Vec<u8>,
}

impl Svg {
    pub fn from_raw(data: String) -> Self {
        Self {
            data: data.into_bytes(),
        }
    }
}

#[php_impl]
impl Svg {
    #[php(defaults(offset = None, limit = None))]
    pub fn bytes(&self, offset: Option<i64>, limit: Option<i64>) -> PhpResult<Binary<u8>> {
        super::slice_data(&self.data, offset, limit)
    }

    #[allow(clippy::cast_possible_wrap)]
    pub fn size(&self) -> i64 {
        self.data.len() as i64
    }

    pub fn save(&self, path: &str) -> PhpResult {
        std::fs::write(path, &self.data).map_err(|e| {
            error::throw_runtime(
                error::RuntimeException::WRITE_FAILED,
                format!("Failed to write SVG to '{path}': {e}"),
            )
        })?;

        Ok(())
    }

    pub fn __to_string(&self) -> Binary<u8> {
        self.data.clone().into()
    }
}
