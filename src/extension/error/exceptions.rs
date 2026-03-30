use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;

use super::exception_interface::PhpInterfaceExceptionInterface;
use super::spl_ce;

#[php_class]
#[php(name = "Typst\\Exception\\RuntimeException")]
#[php(extends(ce = spl_ce::runtime_exception, stub = "\\RuntimeException"))]
#[php(implements(PhpInterfaceExceptionInterface))]
#[php(flags = ClassFlags::Final)]
#[derive(Default)]
pub struct RuntimeException;

#[php_impl]
impl RuntimeException {
    pub const COMPILATION_FAILED: i64 = 1;
    pub const FILE_NOT_FOUND: i64 = 2;
    pub const WRITE_FAILED: i64 = 3;
    pub const FONT_INVALID: i64 = 4;
    pub const ENCODING_FAILED: i64 = 5;
}

#[php_class]
#[php(name = "Typst\\Exception\\LogicException")]
#[php(extends(ce = spl_ce::logic_exception, stub = "\\LogicException"))]
#[php(implements(PhpInterfaceExceptionInterface))]
#[php(flags = ClassFlags::Final)]
#[derive(Default)]
pub struct LogicException;

#[php_impl]
impl LogicException {}

#[php_class]
#[php(name = "Typst\\Exception\\InvalidArgumentException")]
#[php(extends(ce = spl_ce::invalid_argument_exception, stub = "\\InvalidArgumentException"))]
#[php(implements(PhpInterfaceExceptionInterface))]
#[php(flags = ClassFlags::Final)]
#[derive(Default)]
pub struct InvalidArgumentException;

#[php_impl]
impl InvalidArgumentException {}

#[php_class]
#[php(name = "Typst\\Exception\\OutOfBoundsException")]
#[php(extends(ce = spl_ce::out_of_bounds_exception, stub = "\\OutOfBoundsException"))]
#[php(implements(PhpInterfaceExceptionInterface))]
#[php(flags = ClassFlags::Final)]
#[derive(Default)]
pub struct OutOfBoundsException;

#[php_impl]
impl OutOfBoundsException {}
