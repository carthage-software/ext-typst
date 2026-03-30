use ext_php_rs::binary::Binary;
use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use crate::extension::enums::ImageFormat;
use crate::extension::error;

#[php_class]
#[php(name = "Typst\\Output\\Image")]
#[php(implements(super::PhpInterfaceOutputInterface))]
#[php(flags = ClassFlags::Final)]
pub struct Image {
    data: Vec<u8>,
    format: ImageFormat,
    width: u32,
    height: u32,
}

impl Image {
    pub fn from_raw(data: Vec<u8>, format: ImageFormat, width: u32, height: u32) -> Self {
        Self {
            data,
            format,
            width,
            height,
        }
    }
}

#[php_impl]
impl Image {
    #[php(defaults(offset = None, limit = None))]
    pub fn bytes(&self, offset: Option<i64>, limit: Option<i64>) -> PhpResult<Binary<u8>> {
        super::slice_data(&self.data, offset, limit)
    }

    #[allow(clippy::cast_possible_wrap)]
    pub fn size(&self) -> i64 {
        self.data.len() as i64
    }

    pub fn format(&self) -> ImageFormat {
        self.format
    }

    pub fn width(&self) -> i64 {
        i64::from(self.width)
    }

    pub fn height(&self) -> i64 {
        i64::from(self.height)
    }

    pub fn save(&self, path: &str) -> PhpResult {
        std::fs::write(path, &self.data).map_err(|e| {
            error::throw_runtime(
                error::RuntimeException::WRITE_FAILED,
                format!("Failed to write image to '{path}': {e}"),
            )
        })?;
        Ok(())
    }

    pub fn __to_string(&self) -> Binary<u8> {
        self.data.clone().into()
    }
}
