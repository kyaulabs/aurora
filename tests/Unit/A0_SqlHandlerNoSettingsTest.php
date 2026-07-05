<?php

# $KYAULabs: A0_SqlHandlerNoSettingsTest.php Sean Bruen@NOVA 2026/07/04 -0700 Exp $


declare(strict_types=1);

use KYAULabs\SQLHandler;

require_once __DIR__ . '/../../sql.inc.php';

test('throws when SQL_USER is not defined', function () {
    expect(fn () => new SQLHandler('test_db'))
        ->toThrow(\Exception::class, 'No settings.inc.php exists.');
});

// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
