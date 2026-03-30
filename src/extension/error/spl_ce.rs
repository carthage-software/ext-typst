use ext_php_rs::zend::ClassEntry;

// SAFETY: These are PHP C API globals defined by the SPL extension,
// which is always loaded before user extensions. The pointers are
// valid for the entire lifetime of the PHP process.
unsafe extern "C" {
    static spl_ce_RuntimeException: *mut ext_php_rs::ffi::zend_class_entry;
    static spl_ce_LogicException: *mut ext_php_rs::ffi::zend_class_entry;
    static spl_ce_InvalidArgumentException: *mut ext_php_rs::ffi::zend_class_entry;
    static spl_ce_OutOfBoundsException: *mut ext_php_rs::ffi::zend_class_entry;
}

pub fn runtime_exception() -> &'static ClassEntry {
    // SAFETY: SPL is always loaded; the pointer is non-null and valid.
    unsafe { spl_ce_RuntimeException.as_ref() }.unwrap()
}

pub fn logic_exception() -> &'static ClassEntry {
    // SAFETY: SPL is always loaded; the pointer is non-null and valid.
    unsafe { spl_ce_LogicException.as_ref() }.unwrap()
}

pub fn invalid_argument_exception() -> &'static ClassEntry {
    // SAFETY: SPL is always loaded; the pointer is non-null and valid.
    unsafe { spl_ce_InvalidArgumentException.as_ref() }.unwrap()
}

pub fn out_of_bounds_exception() -> &'static ClassEntry {
    // SAFETY: SPL is always loaded; the pointer is non-null and valid.
    unsafe { spl_ce_OutOfBoundsException.as_ref() }.unwrap()
}
