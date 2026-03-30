use ext_php_rs::prelude::*;

#[php_enum]
#[php(name = "Typst\\Diagnostic\\Severity")]
#[derive(Clone, Copy, PartialEq, Eq)]
pub enum Severity {
    #[php(value = 0)]
    Error,
    #[php(value = 1)]
    Warning,
}
