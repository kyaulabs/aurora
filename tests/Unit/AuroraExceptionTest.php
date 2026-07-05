<?php

# $KYAULabs: AuroraExceptionTest.php Sean Bruen@NOVA 2026/07/04 -0700 Exp $


declare(strict_types=1);

use Tests\TestCase;
use KYAULabs\AuroraException;

test('exception stores type and code', function () {
    $e = new AuroraException('Test message', 'param', 42);
    expect($e->getType())->toBe('param');
    expect($e->getCode())->toBe(42);
    expect($e->getMessage())->toBe('Test message');
});

test('exception toString contains alert markup', function () {
    $e = new AuroraException('Database error', 'sql', 1);
    $str = (string)$e;
    expect($str)->toContain('Aurora - Warning!');
    expect($str)->toContain('Database error');
    expect($str)->toContain('sql');
});
// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
