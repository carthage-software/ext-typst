# Extension file name varies by OS
ext_name := if os() == "macos" { "libext_typst.dylib" } else { "libext_typst.so" }
ext_debug := "target/debug/" + ext_name
ext_release := "target/release/" + ext_name

# Build the extension (debug mode)
build:
    cargo build

# Build the extension (release mode, with LTO)
release:
    cargo build --release

# Build the extension in an Alpine Linux musl container
release-musl php_version="8.4":
    #!/usr/bin/env sh
    docker run --rm -v "$PWD:/app" -w /app php:{{php_version}}-cli-alpine sh -lc '
      apk add --no-cache bash build-base clang16-libclang curl git linux-headers openssl &&
      curl https://sh.rustup.rs -sSf | sh -s -- -y --profile minimal &&
      . "$HOME/.cargo/env" &&
      export LIBCLANG_PATH=/usr/lib/llvm16/lib &&
      export BINDGEN_EXTRA_CLANG_ARGS=-I/usr/lib/llvm16/lib/clang/16/include &&
      cargo build --release
    '

# Run all tests
test: build
    php -d extension={{ext_debug}} vendor/bin/phpunit

# Run a specific test file
test-file file: build
    php -d extension={{ext_debug}} vendor/bin/phpunit {{file}}

# Run clippy lints
lint:
    cargo clippy -- -D warnings
    vendor/bin/mago lint

# Type-check without building
check:
    cargo check
    vendor/bin/mago analyze

# Format Rust code
fmt:
    cargo fmt
    vendor/bin/mago fmt

# Check Rust formatting
fmt-check:
    cargo fmt -- --check
    vendor/bin/mago fmt --check

# Fix issues, and run formatter
fix:
    vendor/bin/mago lint --fix --unsafe
    vendor/bin/mago analyze --fix --unsafe
    vendor/bin/mago fmt
    cargo fmt

# Run an example by name (e.g. just run-example hello)
run-example name: release
    php -d extension={{ext_release}} examples/{{name}}.php

# Run all checks
verify: fmt-check lint check

# Remove build artifacts
clean:
    cargo clean
