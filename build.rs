fn main() {
    // Emit PHP version cfg flags (php81, php82, php83, etc.) so that
    // version-gated attributes like #[php(readonly)] compile correctly.
    let php = ext_php_rs_build::find_php().expect("Failed to find PHP");
    let info = ext_php_rs_build::PHPInfo::get(&php).expect("Failed to get PHP info");
    let version: ext_php_rs_build::ApiVersion = info
        .zend_version()
        .expect("Failed to get Zend version")
        .try_into()
        .expect("Unrecognized PHP API version");
    ext_php_rs_build::emit_check_cfg();
    ext_php_rs_build::emit_php_cfg_flags(version);

    // Extract typst version from Cargo.lock for the version() method.
    let lock = std::fs::read_to_string("Cargo.lock").unwrap_or_default();
    let mut in_typst = false;
    let mut found = false;
    for line in lock.lines() {
        if line == "[[package]]" {
            in_typst = false;
        }
        if line == r#"name = "typst""# {
            in_typst = true;
        }
        if in_typst && let Some(ver) = line.strip_prefix("version = \"") {
            let ver = ver.trim_end_matches('"');
            println!("cargo::rustc-env=TYPST_VERSION={ver}");
            found = true;
            break;
        }
    }
    if !found {
        println!("cargo::rustc-env=TYPST_VERSION=unknown");
    }
}
