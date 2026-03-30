# ext-typst

A PHP extension that embeds the [Typst](https://typst.app/) typesetting engine, built with Rust via [ext-php-rs](https://github.com/extphprs/ext-php-rs).

Compile Typst markup to PDF, PNG, JPEG, and SVG directly from PHP. No CLI needed. No subprocess spawning.

**[Documentation](https://ext-typst.carthage.software)** | **[GitHub](https://github.com/carthage-software/ext-typst)** | **[Typst](https://typst.app/)**

## Installation

### Via PIE

```bash
pie install carthage-software/ext-typst
```

### Pre-built binaries

Download the ZIP for your platform from [GitHub Releases](https://github.com/carthage-software/ext-typst/releases). Each ZIP contains a `typst.so` file. Extract it and add to your `php.ini`:

```ini
extension=/path/to/typst.so
```

### IDE & static analysis stubs

```bash
composer require --dev carthage-software/ext-typst
```

Enables autocompletion in PhpStorm and support for Mago, PHPStan, and Psalm.

## Quick Start

```php
$world = new Typst\World();
$compiler = new Typst\Compiler($world);

const TEMPLATE = <<<'TYPST'
#set page(height: auto)
= Hello from Typst

This is a *bold* statement with _italic_ flair.
TYPST;

$document = $compiler->compileString(TEMPLATE);

$document->toPdf()->save('output.pdf');
$document->toImage()->save('output.png');
$document->toSvg()->save('output.svg');
```

See the full documentation at [ext-typst.carthage.software](https://ext-typst.carthage.software).

## Supported Platforms

| Platform | Architecture |
| -------- | ------------ |
| Linux    | x86_64       |
| Linux    | aarch64      |
| macOS    | arm64        |

PHP 8.3, 8.4, and 8.5 are supported.

## Development

Requires [Rust](https://rustup.rs/) (stable, 2024 edition) and [just](https://github.com/casey/just).

```bash
just build          # debug build
just release        # release build
just test           # run tests
just run-example X  # run examples/X.php
just lint           # clippy + mago lint
just check          # cargo check + mago analyze
just fmt            # format rust + php
just verify         # fmt-check + lint + check
```

For more examples, see [`examples/`](examples/).

## Acknowledgments

This project was developed by [Carthage Software](https://carthage.software) and is fully funded by our partner [Buhta](https://buhta.com).

## License

MIT OR Apache-2.0
