use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use super::enums::ImageFormat;
use super::error;

#[php_class]
#[php(name = "Typst\\ImageOptions")]
#[php(flags = ClassFlags::Final)]
#[php(readonly)]
#[derive(Clone)]
pub struct ImageOptions {
    #[php(prop)]
    pub format: ImageFormat,
    #[php(prop)]
    pub quality: u8,
    #[php(prop)]
    pub dpi: f64,
}

#[php_impl]
impl ImageOptions {
    #[php(defaults(format = None, quality = None, dpi = None))]
    pub fn __construct(
        format: Option<ImageFormat>,
        quality: Option<i64>,
        dpi: Option<f64>,
    ) -> PhpResult<Self> {
        let quality = quality.unwrap_or(85);
        if !(1..=100).contains(&quality) {
            return Err(error::throw_invalid_argument(format!(
                "Quality must be between 1 and 100, got {quality}"
            )));
        }

        let dpi = dpi.unwrap_or(144.0);
        if !dpi.is_finite() || dpi <= 0.0 {
            return Err(error::throw_invalid_argument(
                "DPI must be a finite positive number".to_string(),
            ));
        }

        #[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
        Ok(Self {
            format: format.unwrap_or(ImageFormat::Png),
            quality: quality as u8,
            dpi,
        })
    }

    #[php(getter)]
    pub fn get_format(&self) -> ImageFormat {
        self.format
    }

    #[allow(clippy::cast_lossless)]
    #[php(getter)]
    pub fn get_quality(&self) -> i64 {
        self.quality as i64
    }

    #[php(getter)]
    pub fn get_dpi(&self) -> f64 {
        self.dpi
    }

    #[php(name = "withFormat")]
    pub fn with_format(&self, format: ImageFormat) -> Self {
        let mut new = self.clone();
        new.format = format;
        new
    }

    #[php(name = "withQuality")]
    pub fn with_quality(&self, quality: i64) -> PhpResult<Self> {
        if !(1..=100).contains(&quality) {
            return Err(error::throw_invalid_argument(format!(
                "Quality must be between 1 and 100, got {quality}"
            )));
        }

        let mut new = self.clone();
        #[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
        {
            new.quality = quality as u8;
        }
        Ok(new)
    }

    #[php(name = "withDpi")]
    pub fn with_dpi(&self, dpi: f64) -> PhpResult<Self> {
        if !dpi.is_finite() || dpi <= 0.0 {
            return Err(error::throw_invalid_argument(
                "DPI must be a finite positive number".to_string(),
            ));
        }

        let mut new = self.clone();
        new.dpi = dpi;
        Ok(new)
    }
}

impl Default for ImageOptions {
    fn default() -> Self {
        Self {
            format: ImageFormat::Png,
            quality: 85,
            dpi: 144.0,
        }
    }
}
