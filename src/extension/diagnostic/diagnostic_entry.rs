use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use crate::extension::enums::Severity;

use super::SourceSpan;

#[php_class]
#[php(name = "Typst\\Diagnostic\\Diagnostic")]
#[php(flags = ClassFlags::Final)]
#[derive(Clone)]
pub struct Diagnostic {
    pub severity: Severity,
    pub message: String,
    pub span: Option<SourceSpan>,
    pub hints: Vec<String>,
}

impl Diagnostic {
    pub fn from_typst(diag: &typst::diag::SourceDiagnostic, world: &dyn typst::World) -> Self {
        let span = if diag.span.is_detached() {
            None
        } else {
            Self::resolve_span(diag.span, world)
        };

        let severity = match diag.severity {
            typst::diag::Severity::Error => Severity::Error,
            typst::diag::Severity::Warning => Severity::Warning,
        };

        Self {
            severity,
            message: diag.message.to_string(),
            span,
            hints: diag.hints.iter().map(ToString::to_string).collect(),
        }
    }

    #[allow(clippy::cast_possible_wrap)]
    fn resolve_span(span: typst::syntax::Span, world: &dyn typst::World) -> Option<SourceSpan> {
        let id = span.id()?;
        let source = world.source(id).ok()?;
        let range = source.range(span)?;
        let lines = source.lines();
        let line = lines.byte_to_line(range.start)?;
        let column = lines.byte_to_column(range.start)?;
        let text = source.text().get(range.clone()).unwrap_or("").to_string();

        let file = id.vpath().as_rootless_path().to_string_lossy().to_string();

        Some(SourceSpan {
            file,
            line: (line + 1) as i64,
            column: (column + 1) as i64,
            text,
        })
    }
}

#[php_impl]
impl Diagnostic {
    pub fn severity(&self) -> Severity {
        self.severity
    }

    pub fn message(&self) -> String {
        self.message.clone()
    }

    pub fn span(&self) -> Option<SourceSpan> {
        self.span.clone()
    }

    pub fn hints(&self) -> Vec<String> {
        self.hints.clone()
    }

    pub fn __to_string(&self) -> String {
        let severity_str = match self.severity {
            Severity::Error => "error",
            Severity::Warning => "warning",
        };
        let location = self
            .span
            .as_ref()
            .map(|s| format!(" (at {}:{}:{})", s.file, s.line, s.column))
            .unwrap_or_default();

        format!("{severity_str}: {}{location}", self.message)
    }
}
