mod exception_interface;
mod exceptions;
mod spl_ce;

pub use exception_interface::PhpInterfaceExceptionInterface;
pub use exceptions::{
    InvalidArgumentException, LogicException, OutOfBoundsException, RuntimeException,
};

use ext_php_rs::class::RegisteredClass;
use ext_php_rs::prelude::*;

#[allow(clippy::cast_possible_truncation)] // error codes are small constants (1-6)
pub fn throw_runtime(code: i64, message: String) -> PhpException {
    PhpException::new(message, code as i32, RuntimeException::get_metadata().ce())
}

#[allow(dead_code)]
pub fn throw_logic(message: String) -> PhpException {
    PhpException::from_class::<LogicException>(message)
}

pub fn throw_invalid_argument(message: String) -> PhpException {
    PhpException::from_class::<InvalidArgumentException>(message)
}

pub fn throw_out_of_bounds(message: String) -> PhpException {
    PhpException::from_class::<OutOfBoundsException>(message)
}
