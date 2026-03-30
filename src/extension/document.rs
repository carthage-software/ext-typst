use std::sync::Arc;

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use image::ExtendedColorType;
use image::ImageEncoder;
use image::codecs::jpeg::JpegEncoder;
use typst::layout::PagedDocument;

use super::enums::ImageFormat;
use super::error;
use super::error::RuntimeException;
use super::image_options::ImageOptions;
use super::output::{Image, Pdf, Svg};
use super::pdf_options::PdfOptions;

#[php_class]
#[php(name = "Typst\\Document")]
#[php(flags = ClassFlags::Final)]
pub struct Document {
    inner: Arc<PagedDocument>,
}

impl Document {
    pub fn from_paged(doc: PagedDocument) -> Self {
        Self {
            inner: Arc::new(doc),
        }
    }

    pub fn from_arc(doc: Arc<PagedDocument>) -> Self {
        Self { inner: doc }
    }

    pub fn inner_arc(&self) -> &Arc<PagedDocument> {
        &self.inner
    }
}

#[php_impl]
impl Document {
    #[allow(clippy::cast_possible_wrap)]
    pub fn page_count(&self) -> i64 {
        self.inner.pages.len() as i64
    }

    /// Returns the width of a page in typographic points.
    #[php(name = "pageWidth")]
    #[php(defaults(page = None))]
    pub fn page_width(&self, page: Option<i64>) -> PhpResult<f64> {
        let (page_idx, _) = self.resolve_page(page)?;
        Ok(self.inner.pages[page_idx].frame.width().to_pt())
    }

    /// Returns the height of a page in typographic points.
    #[php(name = "pageHeight")]
    #[php(defaults(page = None))]
    pub fn page_height(&self, page: Option<i64>) -> PhpResult<f64> {
        let (page_idx, _) = self.resolve_page(page)?;
        Ok(self.inner.pages[page_idx].frame.height().to_pt())
    }

    #[php(defaults(options = None))]
    pub fn to_pdf(&self, options: Option<&PdfOptions>) -> PhpResult<Pdf> {
        let default_opts;
        let opts = if let Some(o) = options {
            o
        } else {
            default_opts = PdfOptions::default();
            &default_opts
        };

        let ident_storage = opts.identifier.clone();
        let typst_options = opts.to_typst(ident_storage.as_ref())?;

        let data = typst_pdf::pdf(&self.inner, &typst_options).map_err(|e| {
            let messages: Vec<String> = e.iter().map(|d| d.message.to_string()).collect();
            error::throw_runtime(
                RuntimeException::ENCODING_FAILED,
                format!("PDF export failed: {}", messages.join(", ")),
            )
        })?;

        let page_count = if let Some(ref ranges) = typst_options.page_ranges {
            self.inner
                .pages
                .iter()
                .enumerate()
                .filter(|(i, _)| ranges.includes_page_index(*i))
                .count()
        } else {
            self.inner.pages.len()
        };

        Ok(Pdf::from_raw(data, page_count))
    }

    #[php(defaults(page = None, options = None))]
    pub fn to_image(&self, page: Option<i64>, options: Option<&ImageOptions>) -> PhpResult<Image> {
        let (page_idx, _) = self.resolve_page(page)?;
        let opts = options.cloned().unwrap_or_default();
        self.render_page(&opts, page_idx)
    }

    #[php(defaults(options = None))]
    pub fn to_images(&self, options: Option<&ImageOptions>) -> PhpResult<Vec<Image>> {
        let opts = options.cloned().unwrap_or_default();
        let mut images = Vec::with_capacity(self.inner.pages.len());
        for i in 0..self.inner.pages.len() {
            images.push(self.render_page(&opts, i)?);
        }

        Ok(images)
    }

    #[php(defaults(page = None))]
    pub fn to_svg(&self, page: Option<i64>) -> PhpResult<Svg> {
        let (page_idx, _) = self.resolve_page(page)?;
        let svg_str = typst_svg::svg(&self.inner.pages[page_idx]);
        Ok(Svg::from_raw(svg_str))
    }

    pub fn to_svgs(&self) -> Vec<Svg> {
        self.inner
            .pages
            .iter()
            .map(|page| Svg::from_raw(typst_svg::svg(page)))
            .collect()
    }
}

impl Document {
    fn resolve_page(&self, page: Option<i64>) -> PhpResult<(usize, i64)> {
        let page = page.unwrap_or(0);
        if page < 0 {
            return Err(error::throw_invalid_argument(format!(
                "Page index must be non-negative, got {page}"
            )));
        }

        #[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
        let page_idx = page as usize;
        if page_idx >= self.inner.pages.len() {
            return Err(error::throw_out_of_bounds(format!(
                "Page index {page_idx} out of range (document has {} pages)",
                self.inner.pages.len()
            )));
        }

        Ok((page_idx, page))
    }

    fn render_page(&self, opts: &ImageOptions, page_idx: usize) -> PhpResult<Image> {
        if page_idx >= self.inner.pages.len() {
            return Err(error::throw_out_of_bounds(format!(
                "Page index {page_idx} out of range (document has {} pages)",
                self.inner.pages.len()
            )));
        }

        #[allow(clippy::cast_possible_truncation)]
        let pixel_per_pt = (opts.dpi / 72.0) as f32;
        let pixmap = typst_render::render(&self.inner.pages[page_idx], pixel_per_pt);
        let width = pixmap.width();
        let height = pixmap.height();

        let data = match opts.format {
            ImageFormat::Png => pixmap.encode_png().map_err(|e| {
                error::throw_runtime(
                    RuntimeException::ENCODING_FAILED,
                    format!("PNG encoding failed: {e}"),
                )
            })?,
            ImageFormat::Jpeg => {
                let rgba = pixmap.data();
                let rgb: Vec<u8> = rgba
                    .chunks_exact(4)
                    .flat_map(|px| [px[0], px[1], px[2]])
                    .collect();

                let mut buf = Vec::new();
                let encoder = JpegEncoder::new_with_quality(&mut buf, opts.quality);

                encoder
                    .write_image(&rgb, width, height, ExtendedColorType::Rgb8)
                    .map_err(|e| {
                        error::throw_runtime(
                            RuntimeException::ENCODING_FAILED,
                            format!("JPEG encoding failed: {e}"),
                        )
                    })?;
                buf
            }
        };

        Ok(Image::from_raw(data, opts.format, width, height))
    }
}
