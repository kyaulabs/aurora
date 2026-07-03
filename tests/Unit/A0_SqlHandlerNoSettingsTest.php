<?php

# $KYAULabs: A0_SqlHandlerNoSettingsTest.php,v 1.0.0 2026/07/02 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

use KYAULabs\SQLHandler;

require_once __DIR__ . '/../../sql.inc.php';

test('throws when SQL_USER is not defined', function () {
    expect(fn () => new SQLHandler('test_db'))
        ->toThrow(\Exception::class, 'No settings.inc.php exists.');
});

// vim: ft=php sts=4 sw=4 ts=4 et :
