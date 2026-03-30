use std::sync::Arc;

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use typst::layout::PagedDocument;

use crate::extension::enums::Severity;

use super::Diagnostic;
use crate::extension::document::Document;

#[php_class]
#[php(name = "Typst\\Diagnostic\\CompilationResult")]
#[php(flags = ClassFlags::Final)]
pub struct CompilationResult {
    success: bool,
    document: Option<Arc<PagedDocument>>,
    diagnostics: Vec<Diagnostic>,
}

impl CompilationResult {
    pub fn new_success(document: &Document, warnings: Vec<Diagnostic>) -> Self {
        Self {
            success: true,
            document: Some(Arc::clone(document.inner_arc())),
            diagnostics: warnings,
        }
    }

    pub fn new_failure(errors: Vec<Diagnostic>, warnings: Vec<Diagnostic>) -> Self {
        let mut diagnostics = errors;
        diagnostics.extend(warnings);
        Self {
            success: false,
            document: None,
            diagnostics,
        }
    }
}

#[php_impl]
impl CompilationResult {
    #[php(name = "getDocument")]
    pub fn get_document(&self) -> Option<Document> {
        self.document
            .as_ref()
            .map(|d| Document::from_arc(Arc::clone(d)))
    }

    pub fn success(&self) -> bool {
        self.success
    }

    pub fn diagnostics(&self) -> Vec<Diagnostic> {
        self.diagnostics.clone()
    }

    pub fn warnings(&self) -> Vec<Diagnostic> {
        self.diagnostics
            .iter()
            .filter(|d| d.severity == Severity::Warning)
            .cloned()
            .collect()
    }

    pub fn errors(&self) -> Vec<Diagnostic> {
        self.diagnostics
            .iter()
            .filter(|d| d.severity == Severity::Error)
            .cloned()
            .collect()
    }

    pub fn has_warnings(&self) -> bool {
        self.diagnostics
            .iter()
            .any(|d| d.severity == Severity::Warning)
    }

    pub fn has_errors(&self) -> bool {
        self.diagnostics
            .iter()
            .any(|d| d.severity == Severity::Error)
    }
}
