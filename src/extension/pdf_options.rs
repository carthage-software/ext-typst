use std::num::NonZeroUsize;

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use typst::foundations::Datetime;
use typst::foundations::Smart;
use typst_pdf::Timestamp;

use super::enums::{PdfValidator, PdfVersion};
use super::error;

#[php_class]
#[php(name = "Typst\\PdfOptions")]
#[php(flags = ClassFlags::Final)]
#[php(readonly)]
#[derive(Clone)]
pub struct PdfOptions {
    #[php(prop)]
    pub identifier: Option<String>,
    #[php(prop)]
    pub timestamp: Option<i64>,
    #[php(prop)]
    pub first_page: Option<i64>,
    #[php(prop)]
    pub last_page: Option<i64>,
    #[php(prop)]
    pub version: Option<PdfVersion>,
    #[php(prop)]
    pub validator: Option<PdfValidator>,
    #[php(prop)]
    pub tagged: bool,
}

#[php_impl]
impl PdfOptions {
    #[php(defaults(
        identifier = None,
        timestamp = None,
        first_page = None,
        last_page = None,
        version = None,
        validator = None,
        tagged = None
    ))]
    pub fn __construct(
        identifier: Option<String>,
        timestamp: Option<i64>,
        first_page: Option<i64>,
        last_page: Option<i64>,
        version: Option<PdfVersion>,
        validator: Option<PdfValidator>,
        tagged: Option<bool>,
    ) -> PhpResult<Self> {
        validate_first_page(first_page)?;
        validate_last_page(last_page)?;
        validate_page_range(first_page, last_page)?;
        validate_standards(version, validator)?;

        Ok(Self {
            identifier,
            timestamp,
            first_page,
            last_page,
            version,
            validator,
            tagged: tagged.unwrap_or(true),
        })
    }

    #[php(getter)]
    pub fn get_identifier(&self) -> Option<String> {
        self.identifier.clone()
    }

    #[php(getter)]
    pub fn get_timestamp(&self) -> Option<i64> {
        self.timestamp
    }

    #[php(getter)]
    pub fn get_first_page(&self) -> Option<i64> {
        self.first_page
    }

    #[php(getter)]
    pub fn get_last_page(&self) -> Option<i64> {
        self.last_page
    }

    #[php(getter)]
    pub fn get_version(&self) -> Option<PdfVersion> {
        self.version
    }

    #[php(getter)]
    pub fn get_validator(&self) -> Option<PdfValidator> {
        self.validator
    }

    #[php(getter)]
    pub fn get_tagged(&self) -> bool {
        self.tagged
    }

    #[php(name = "withIdentifier")]
    pub fn with_identifier(&self, identifier: Option<String>) -> Self {
        let mut new = self.clone();
        new.identifier = identifier;
        new
    }

    #[php(name = "withTimestamp")]
    pub fn with_timestamp(&self, timestamp: Option<i64>) -> Self {
        let mut new = self.clone();
        new.timestamp = timestamp;
        new
    }

    #[php(name = "withFirstPage")]
    pub fn with_first_page(&self, first_page: Option<i64>) -> PhpResult<Self> {
        validate_first_page(first_page)?;
        validate_page_range(first_page, self.last_page)?;

        let mut new = self.clone();
        new.first_page = first_page;
        Ok(new)
    }

    #[php(name = "withLastPage")]
    pub fn with_last_page(&self, last_page: Option<i64>) -> PhpResult<Self> {
        validate_last_page(last_page)?;
        validate_page_range(self.first_page, last_page)?;

        let mut new = self.clone();
        new.last_page = last_page;
        Ok(new)
    }

    #[php(name = "withVersion")]
    pub fn with_version(&self, version: Option<PdfVersion>) -> PhpResult<Self> {
        validate_standards(version, self.validator)?;

        let mut new = self.clone();
        new.version = version;
        Ok(new)
    }

    #[php(name = "withValidator")]
    pub fn with_validator(&self, validator: Option<PdfValidator>) -> PhpResult<Self> {
        validate_standards(self.version, validator)?;

        let mut new = self.clone();
        new.validator = validator;
        Ok(new)
    }

    #[php(name = "withTagged")]
    pub fn with_tagged(&self, tagged: bool) -> Self {
        let mut new = self.clone();
        new.tagged = tagged;
        new
    }
}

impl PdfOptions {
    /// Build `typst_pdf::PdfOptions` from the PHP options.
    ///
    /// The returned struct borrows `ident_storage` for its lifetime.
    pub fn to_typst<'a>(
        &self,
        ident_storage: Option<&'a String>,
    ) -> PhpResult<typst_pdf::PdfOptions<'a>> {
        let ident = match ident_storage {
            Some(s) => Smart::Custom(s.as_str()),
            None => Smart::Auto,
        };

        let timestamp = self.timestamp.and_then(unix_to_timestamp);
        let page_ranges = self.build_page_ranges();
        let standards = build_standards(self.version, self.validator)?;

        Ok(typst_pdf::PdfOptions {
            ident,
            timestamp,
            page_ranges,
            standards,
            tagged: self.tagged,
        })
    }

    fn build_page_ranges(&self) -> Option<typst::layout::PageRanges> {
        if self.first_page.is_none() && self.last_page.is_none() {
            return None;
        }

        // PageRanges uses 1-based NonZeroUsize
        #[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
        let start = self
            .first_page
            .map(|p| NonZeroUsize::new((p as usize) + 1).unwrap_or(NonZeroUsize::MIN));

        #[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
        let end = self
            .last_page
            .map(|p| NonZeroUsize::new((p as usize) + 1).unwrap_or(NonZeroUsize::MIN));

        let range = start..=end;
        Some(typst::layout::PageRanges::new(vec![range]))
    }
}

impl Default for PdfOptions {
    fn default() -> Self {
        Self {
            identifier: None,
            timestamp: None,
            first_page: None,
            last_page: None,
            version: None,
            validator: None,
            tagged: true,
        }
    }
}

fn validate_first_page(first_page: Option<i64>) -> PhpResult<()> {
    if let Some(fp) = first_page
        && fp < 0
    {
        return Err(error::throw_invalid_argument(format!(
            "First page must be non-negative, got {fp}"
        )));
    }
    Ok(())
}

fn validate_last_page(last_page: Option<i64>) -> PhpResult<()> {
    if let Some(lp) = last_page
        && lp < 0
    {
        return Err(error::throw_invalid_argument(format!(
            "Last page must be non-negative, got {lp}"
        )));
    }
    Ok(())
}

fn validate_page_range(first_page: Option<i64>, last_page: Option<i64>) -> PhpResult<()> {
    if let (Some(fp), Some(lp)) = (first_page, last_page)
        && fp > lp
    {
        return Err(error::throw_invalid_argument(format!(
            "First page ({fp}) must not be greater than last page ({lp})"
        )));
    }
    Ok(())
}

fn validate_standards(
    version: Option<PdfVersion>,
    validator: Option<PdfValidator>,
) -> PhpResult<()> {
    // Only validate when both are set; PdfStandards::new will check compatibility.
    if version.is_some() && validator.is_some() {
        build_standards(version, validator)?;
    }
    Ok(())
}

fn build_standards(
    version: Option<PdfVersion>,
    validator: Option<PdfValidator>,
) -> PhpResult<typst_pdf::PdfStandards> {
    let mut list = Vec::with_capacity(2);
    if let Some(v) = version {
        list.push(v.to_typst());
    }
    if let Some(v) = validator {
        list.push(v.to_typst());
    }

    if list.is_empty() {
        return Ok(typst_pdf::PdfStandards::default());
    }

    typst_pdf::PdfStandards::new(&list).map_err(|e| {
        error::throw_invalid_argument(format!("Incompatible PDF version and validator: {e}"))
    })
}

#[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
fn unix_to_timestamp(unix: i64) -> Option<Timestamp> {
    use chrono::{Datelike, Timelike};

    let utc = chrono::DateTime::from_timestamp(unix, 0)?;
    let dt = Datetime::from_ymd_hms(
        utc.year(),
        utc.month() as u8,
        utc.day() as u8,
        utc.hour() as u8,
        utc.minute() as u8,
        utc.second() as u8,
    )?;

    Some(Timestamp::new_utc(dt))
}
