use ext_php_rs::prelude::*;

#[php_enum]
#[php(name = "Typst\\ImageFormat")]
#[derive(Clone, Copy, PartialEq, Eq)]
pub enum ImageFormat {
    #[php(value = "png")]
    Png,
    #[php(value = "jpeg")]
    Jpeg,
}
