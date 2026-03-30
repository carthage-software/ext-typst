use ext_php_rs::prelude::*;
use ext_php_rs::zend::ce;

#[php_interface]
#[php(name = "Typst\\Exception\\ExceptionInterface")]
#[php(extends(ce = ce::throwable, stub = "\\Throwable"))]
#[allow(dead_code)]
pub trait ExceptionInterface {}
