use std::sync::Arc;

use ext_php_rs::class::RegisteredClass;
use ext_php_rs::ffi::zend_objects_clone_members;
use ext_php_rs::types::{ZendClassObject, ZendObject};
use ext_php_rs::zend::ZendObjectHandlers;

use crate::internal::world::TypstWorld;

use super::compiler::Compiler;
use super::image_options::ImageOptions;
use super::inspector::Inspector;
use super::world::World;

/// Install all clone handlers during module startup.
pub fn patch_all_clone_handlers() {
    patch_clone::<World>(clone_world);
    patch_clone::<Compiler>(clone_compiler);
    patch_clone::<Inspector>(clone_inspector);
    patch_clone::<ImageOptions>(clone_image_options);
}

/// Disable cloning for a PHP class by removing its clone handler.
pub fn disable_clone<T: RegisteredClass>() {
    let handlers = T::get_metadata().handlers();
    let handlers_ptr = std::ptr::from_ref::<ZendObjectHandlers>(handlers).cast_mut();
    // SAFETY: the handlers struct is allocated once during module init and lives
    // for the entire process lifetime.
    unsafe {
        (*handlers_ptr).clone_obj = None;
    }
}

fn patch_clone<T: RegisteredClass>(
    handler: unsafe extern "C" fn(*mut ZendObject) -> *mut ZendObject,
) {
    let handlers = T::get_metadata().handlers();
    let handlers_ptr = std::ptr::from_ref::<ZendObjectHandlers>(handlers).cast_mut();
    // SAFETY: the handlers struct is allocated once during module init and lives
    // for the entire process lifetime.
    unsafe {
        (*handlers_ptr).clone_obj = Some(handler);
    }
}

unsafe extern "C" fn clone_world(object: *mut ZendObject) -> *mut ZendObject {
    // SAFETY: called by the zend engine with a valid, non-null object pointer.
    let obj = unsafe { object.as_ref().unwrap() };
    let class_obj = ZendClassObject::<World>::from_zend_obj(obj).unwrap();
    let original = &**class_obj;

    clone_into_new::<World>(object, World::clone_from(original))
}

unsafe extern "C" fn clone_compiler(object: *mut ZendObject) -> *mut ZendObject {
    // SAFETY: called by the zend engine with a valid, non-null object pointer.
    let obj = unsafe { object.as_ref().unwrap() };
    let class_obj = ZendClassObject::<Compiler>::from_zend_obj(obj).unwrap();
    let original = &**class_obj;

    let php_world = World::clone_from(original.php_world());
    let fi = original.typst_world().font_inner();
    let world = TypstWorld::new(
        php_world.configured_root().to_owned(),
        Arc::clone(fi),
        original.typst_world().package_dir().map(ToOwned::to_owned),
        original.typst_world().cache_size(),
    );

    clone_into_new::<Compiler>(object, Compiler::from_parts(world, php_world))
}

unsafe extern "C" fn clone_inspector(object: *mut ZendObject) -> *mut ZendObject {
    // SAFETY: called by the zend engine with a valid, non-null object pointer.
    let obj = unsafe { object.as_ref().unwrap() };
    let class_obj = ZendClassObject::<Inspector>::from_zend_obj(obj).unwrap();
    let original = &**class_obj;

    let php_world = World::clone_from(original.php_world());
    let fi = original.typst_world().font_inner();
    let world = TypstWorld::new(
        php_world.configured_root().to_owned(),
        Arc::clone(fi),
        original.typst_world().package_dir().map(ToOwned::to_owned),
        original.typst_world().cache_size(),
    );

    clone_into_new::<Inspector>(object, Inspector::from_parts(world, php_world))
}

unsafe extern "C" fn clone_image_options(object: *mut ZendObject) -> *mut ZendObject {
    // SAFETY: called by the zend engine with a valid, non-null object pointer.
    let obj = unsafe { object.as_ref().unwrap() };
    let class_obj = ZendClassObject::<ImageOptions>::from_zend_obj(obj).unwrap();
    let cloned = (*class_obj).clone();

    clone_into_new::<ImageOptions>(object, cloned)
}

/// Allocate a new `ZendClassObject<T>`, copy zend members, return the raw object pointer.
fn clone_into_new<T: RegisteredClass>(source: *mut ZendObject, value: T) -> *mut ZendObject {
    let mut new = ZendClassObject::<T>::new(value);
    // SAFETY: copies internal zend object members (properties, etc.) from the
    // source object to the newly allocated clone.
    unsafe {
        zend_objects_clone_members(&raw mut new.std, source);
    }

    let raw = new.into_raw();
    // SAFETY: `raw` is a valid pointer to a ZendClassObject<T>;
    // we return a pointer to its embedded `std` (ZendObject) field.
    (&raw mut raw.std).cast::<ZendObject>()
}
