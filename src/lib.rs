#![cfg_attr(windows, feature(abi_vectorcall))]

mod extension;
mod internal;

use ext_php_rs::prelude::*;

use extension::clone;

#[php_function]
#[php(name = "Typst\\version")]
#[allow(clippy::must_use_candidate)]
pub fn typst_ext_version() -> &'static str {
    env!("CARGO_PKG_VERSION")
}

#[php_function]
#[php(name = "Typst\\typst_version")]
#[allow(clippy::must_use_candidate)]
pub fn typst_engine_version() -> &'static str {
    env!("TYPST_VERSION")
}

#[php_module]
#[php(startup = startup)]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .interface::<extension::error::PhpInterfaceExceptionInterface>()
        .class::<extension::error::RuntimeException>()
        .class::<extension::error::LogicException>()
        .class::<extension::error::InvalidArgumentException>()
        .class::<extension::error::OutOfBoundsException>()
        .enumeration::<extension::enums::Severity>()
        .enumeration::<extension::enums::ImageFormat>()
        .enumeration::<extension::enums::PdfVersion>()
        .enumeration::<extension::enums::PdfValidator>()
        .class::<extension::image_options::ImageOptions>()
        .class::<extension::pdf_options::PdfOptions>()
        .class::<extension::diagnostic::CompilationResult>()
        .class::<extension::diagnostic::Diagnostic>()
        .class::<extension::diagnostic::SourceSpan>()
        .interface::<extension::output::PhpInterfaceOutputInterface>()
        .class::<extension::output::Pdf>()
        .class::<extension::output::Image>()
        .class::<extension::output::Svg>()
        .class::<extension::source::Source>()
        .class::<extension::world::World>()
        .class::<extension::document::Document>()
        .class::<extension::compiler::Compiler>()
        .class::<extension::inspector::Inspector>()
        .class::<extension::pending::PendingDocument>()
        .function(wrap_function!(typst_ext_version))
        .function(wrap_function!(typst_engine_version))
}

fn startup(_ty: i32, _mod_num: i32) -> i32 {
    clone::patch_all_clone_handlers();

    clone::disable_clone::<extension::source::Source>();
    clone::disable_clone::<extension::document::Document>();
    clone::disable_clone::<extension::diagnostic::CompilationResult>();
    clone::disable_clone::<extension::diagnostic::Diagnostic>();
    clone::disable_clone::<extension::diagnostic::SourceSpan>();
    clone::disable_clone::<extension::output::Pdf>();
    clone::disable_clone::<extension::output::Image>();
    clone::disable_clone::<extension::output::Svg>();
    clone::disable_clone::<extension::pending::PendingDocument>();
    clone::disable_clone::<extension::pdf_options::PdfOptions>();

    0
}
