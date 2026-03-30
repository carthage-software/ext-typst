use ext_php_rs::prelude::*;

#[php_enum]
#[php(name = "Typst\\PdfVersion")]
#[derive(Clone, Copy, PartialEq, Eq)]
pub enum PdfVersion {
    /// PDF 1.4
    #[php(value = "1.4")]
    V1_4,
    /// PDF 1.5
    #[php(value = "1.5")]
    V1_5,
    /// PDF 1.6
    #[php(value = "1.6")]
    V1_6,
    /// PDF 1.7
    #[php(value = "1.7")]
    V1_7,
    /// PDF 2.0
    #[php(value = "2.0")]
    V2_0,
}

impl PdfVersion {
    pub fn to_typst(self) -> typst_pdf::PdfStandard {
        match self {
            Self::V1_4 => typst_pdf::PdfStandard::V_1_4,
            Self::V1_5 => typst_pdf::PdfStandard::V_1_5,
            Self::V1_6 => typst_pdf::PdfStandard::V_1_6,
            Self::V1_7 => typst_pdf::PdfStandard::V_1_7,
            Self::V2_0 => typst_pdf::PdfStandard::V_2_0,
        }
    }
}

#[php_enum]
#[php(name = "Typst\\PdfValidator")]
#[derive(Clone, Copy, PartialEq, Eq)]
pub enum PdfValidator {
    /// PDF/A-1b (archival, basic)
    #[php(value = "a-1b")]
    A1b,
    /// PDF/A-1a (archival, accessible)
    #[php(value = "a-1a")]
    A1a,
    /// PDF/A-2b (archival, basic)
    #[php(value = "a-2b")]
    A2b,
    /// PDF/A-2u (archival, Unicode)
    #[php(value = "a-2u")]
    A2u,
    /// PDF/A-2a (archival, accessible)
    #[php(value = "a-2a")]
    A2a,
    /// PDF/A-3b (archival, basic)
    #[php(value = "a-3b")]
    A3b,
    /// PDF/A-3u (archival, Unicode)
    #[php(value = "a-3u")]
    A3u,
    /// PDF/A-3a (archival, accessible)
    #[php(value = "a-3a")]
    A3a,
    /// PDF/A-4
    #[php(value = "a-4")]
    A4,
    /// PDF/A-4f
    #[php(value = "a-4f")]
    A4f,
    /// PDF/A-4e
    #[php(value = "a-4e")]
    A4e,
    /// PDF/UA-1 (universal accessibility)
    #[php(value = "ua-1")]
    Ua1,
}

impl PdfValidator {
    pub fn to_typst(self) -> typst_pdf::PdfStandard {
        match self {
            Self::A1b => typst_pdf::PdfStandard::A_1b,
            Self::A1a => typst_pdf::PdfStandard::A_1a,
            Self::A2b => typst_pdf::PdfStandard::A_2b,
            Self::A2u => typst_pdf::PdfStandard::A_2u,
            Self::A2a => typst_pdf::PdfStandard::A_2a,
            Self::A3b => typst_pdf::PdfStandard::A_3b,
            Self::A3u => typst_pdf::PdfStandard::A_3u,
            Self::A3a => typst_pdf::PdfStandard::A_3a,
            Self::A4 => typst_pdf::PdfStandard::A_4,
            Self::A4f => typst_pdf::PdfStandard::A_4f,
            Self::A4e => typst_pdf::PdfStandard::A_4e,
            Self::Ua1 => typst_pdf::PdfStandard::Ua_1,
        }
    }
}
