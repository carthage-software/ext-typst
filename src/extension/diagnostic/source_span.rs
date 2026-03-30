use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

#[php_class]
#[php(name = "Typst\\Diagnostic\\SourceSpan")]
#[php(flags = ClassFlags::Final)]
#[derive(Clone)]
pub struct SourceSpan {
    pub file: String,
    pub line: i64,
    pub column: i64,
    pub text: String,
}

#[php_impl]
impl SourceSpan {
    pub fn file(&self) -> String {
        self.file.clone()
    }

    pub fn line(&self) -> i64 {
        self.line
    }

    pub fn column(&self) -> i64 {
        self.column
    }

    pub fn text(&self) -> String {
        self.text.clone()
    }
}
