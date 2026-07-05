# ext-typst-stubs

IDE and static-analysis stubs for [**ext-typst**](https://github.com/carthage-software/ext-typst), the [Typst](https://typst.app/) typesetting engine for PHP.

> [!NOTE]
> This repository is a **mirror**. Its contents are generated from the
> [`stubs/`](https://github.com/carthage-software/ext-typst/tree/main/stubs)
> directory of `carthage-software/ext-typst` and pushed here automatically on
> every change. Do not edit it directly; open PRs against
> [`carthage-software/ext-typst`](https://github.com/carthage-software/ext-typst).

## Installation

```bash
composer require --dev carthage-software/ext-typst-stubs
```

Each tag matches the corresponding `ext-typst` release, so you can pin the
stubs to the extension version you target (e.g. `^0.2`).

## What this gives you

The stubs declare the `Typst\*` classes, interfaces, enums, and functions that
the compiled extension provides, so editors and analyzers understand the API
even on machines where the extension isn't installed.

- **PhpStorm** and **Mago**: discover the stubs automatically (they index `vendor/`).
- **PHPStan**: add the stub directory to your `phpstan.neon`:

  ```neon
  parameters:
      scanDirectories:
          - vendor/carthage-software/ext-typst-stubs/stubs
  ```

- **Psalm**: reference the stubs in your `psalm.xml`:

  ```xml
  <stubs>
      <file name="vendor/carthage-software/ext-typst-stubs/stubs/Typst" />
  </stubs>
  ```

The class stubs are PSR-4 autoloadable. When the extension **is** loaded, the
autoloader is never consulted for `Typst\*` (the real classes already exist).
When it **isn't**, calling any stub method throws an `Error` explaining that the
extension must be installed; so a missing extension fails loudly instead of
silently.

## License

MIT OR Apache-2.0
