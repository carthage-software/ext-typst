use ext_php_rs::prelude::*;
use ext_php_rs::types::ZendHashTable;
use ext_php_rs::types::Zval;
use ext_php_rs::types::array::ArrayKey;

use typst::foundations::{Array, Dict, Str, Value};

use super::error;

pub fn convert_inputs(inputs: Option<&ZendHashTable>) -> PhpResult<Dict> {
    let Some(table) = inputs else {
        return Ok(Dict::new());
    };

    let mut dict = Dict::new();
    for (key, val) in table {
        let key_str = array_key_to_string(&key);
        let value = convert_zval(val, &key_str)?;
        dict.insert(Str::from(key_str.as_str()), value);
    }

    Ok(dict)
}

fn convert_zval(zval: &Zval, path: &str) -> PhpResult<Value> {
    if zval.is_null() {
        return Ok(Value::None);
    }

    if zval.is_bool() {
        return Ok(Value::Bool(zval.bool().unwrap_or(false)));
    }

    if zval.is_long() {
        return Ok(Value::Int(zval.long().unwrap_or(0)));
    }

    if zval.is_double() {
        return Ok(Value::Float(zval.double().unwrap_or(0.0)));
    }

    if zval.is_string() {
        let s = zval.str().unwrap_or("");
        return Ok(Value::Str(Str::from(s)));
    }

    if zval.is_array() {
        let table = zval.array().unwrap();
        return convert_array(table, path);
    }

    if zval.is_object() {
        let class_name = zval
            .object()
            .and_then(|o| o.get_class_name().ok())
            .unwrap_or_else(|| "object".to_string());

        return Err(error::throw_invalid_argument(format!(
            "Unsupported input value type: {class_name} at key '{path}'"
        )));
    }

    if zval.is_resource() {
        return Err(error::throw_invalid_argument(format!(
            "Unsupported input value type: resource at key '{path}'"
        )));
    }

    Err(error::throw_invalid_argument(format!(
        "Unsupported input value type at key '{path}'"
    )))
}

fn convert_array(table: &ZendHashTable, path: &str) -> PhpResult<Value> {
    if table.is_empty() {
        return Ok(Value::Dict(Dict::new()));
    }

    if table.has_sequential_keys() {
        let mut arr = Array::new();
        for (key, val) in table {
            let child_path = format_child_path(path, &key);
            arr.push(convert_zval(val, &child_path)?);
        }
        return Ok(Value::Array(arr));
    }

    let mut dict = Dict::new();
    for (key, val) in table {
        let key_str = array_key_to_string(&key);
        let child_path = format_child_path(path, &key);
        dict.insert(Str::from(key_str.as_str()), convert_zval(val, &child_path)?);
    }

    Ok(Value::Dict(dict))
}

fn array_key_to_string(key: &ArrayKey<'_>) -> String {
    match key {
        ArrayKey::Long(i) => i.to_string(),
        ArrayKey::String(s) => s.clone(),
        ArrayKey::Str(s) => s.to_string(),
    }
}

fn format_child_path(parent: &str, key: &ArrayKey<'_>) -> String {
    let key_str = array_key_to_string(key);

    if parent.is_empty() {
        key_str
    } else {
        format!("{parent}.{key_str}")
    }
}
