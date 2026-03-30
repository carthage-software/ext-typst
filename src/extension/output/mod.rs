mod image;
mod pdf;
mod svg;

pub use self::image::Image;
pub use self::pdf::Pdf;
pub use self::svg::Svg;

use ext_php_rs::binary::Binary;
use ext_php_rs::prelude::*;

use super::error;

#[php_interface]
#[php(name = "Typst\\Output\\OutputInterface")]
#[allow(dead_code)]
pub trait OutputInterface {
    #[php(defaults(offset = None, limit = None))]
    fn bytes(&self, offset: Option<i64>, limit: Option<i64>) -> PhpResult<Binary<u8>>;
    fn size(&self) -> i64;
    fn save(&self, path: String) -> PhpResult;
    #[php(name = "__toString")]
    fn __to_string(&self) -> Binary<u8>;
}

#[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
pub fn slice_data(data: &[u8], offset: Option<i64>, limit: Option<i64>) -> PhpResult<Binary<u8>> {
    let offset = offset.unwrap_or(0);
    if offset < 0 {
        return Err(error::throw_invalid_argument(format!(
            "Offset must be non-negative, got {offset}."
        )));
    }

    let offset = offset as usize;
    if offset > data.len() {
        return Err(error::throw_out_of_bounds(format!(
            "Offset {offset} is beyond data size {}.",
            data.len()
        )));
    }

    if let Some(limit) = limit {
        if limit < 0 {
            return Err(error::throw_invalid_argument(format!(
                "Limit must be non-negative, got {limit}."
            )));
        }

        let end = (offset + limit as usize).min(data.len());
        Ok(data[offset..end].to_vec().into())
    } else {
        Ok(data[offset..].to_vec().into())
    }
}
