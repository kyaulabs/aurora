<?php

# $KYAULabs: version.php Sean Bruen@NOVA 2026/07/04 -0700 Exp $


declare(strict_types=1);

/**
 * Aurora Version Resolver
 *
 * Reads the project version from version.inc.php (updated by /release).
 * Falls back to git describe in development environments.
 *
 * @return string|null The version string, or null if unresolvable.
 */
function aurora_version(): ?string
{
    static $version = null;

    if ($version !== null) {
        return $version;
    }

    $version_file = __DIR__ . '/../version.inc.php';
    if (file_exists($version_file)) {
        $defined = get_defined_constants(true)['user'] ?? [];
        if (!isset($defined['AURORA_VERSION'])) {
            require_once $version_file;
        }
        if (defined('AURORA_VERSION')) {
            $version = AURORA_VERSION;
            return $version;
        }
    }

    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    if (is_callable('shell_exec') && !in_array('shell_exec', $disabled, true)) {
        $git_dir = __DIR__ . '/../.git';
        if (is_dir($git_dir)) {
            $git_version = trim((string) shell_exec(
                'git -C ' . escapeshellarg(dirname((string) realpath($git_dir)))
                . ' describe --tags --always --dirty 2>/dev/null'
            ));
            if ($git_version !== '') {
                $version = $git_version;
                return $version;
            }
        }
    }

    return null;
}

// vim: ft=php sts=4 sw=4 ts=4 et :
