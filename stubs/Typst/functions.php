<?php

declare(strict_types=1);

namespace Typst;

use Error;

/**
 * Returns the ext-typst extension version string.
 *
 * This is the version of the PHP extension itself, not the Typst engine.
 *
 * @return non-empty-string Version string in semver format (e.g. "0.1.0").
 *
 * @see typst_version() for the Typst engine version.
 */
function version(): string
{
    throw new Error(
        'Attempted to call stub function ' . __FUNCTION__ . '(), which should be implemented by the Typst extension.',
    );
}

/**
 * Returns the embedded Typst engine version string.
 *
 * This is the version of the Typst typesetting engine compiled into
 * the extension, which determines the available Typst language features
 * and rendering behavior.
 *
 * @return non-empty-string Version string in semver format (e.g. "0.14.2").
 *
 * @link https://typst.app/docs/changelog/ Typst changelog
 *
 * @see version() for the extension version.
 */
function typst_version(): string
{
    throw new Error(
        'Attempted to call stub function ' . __FUNCTION__ . '(), which should be implemented by the Typst extension.',
    );
}
