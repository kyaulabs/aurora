<?php

# $KYAULabs: UnitCase.php,v 1.0.0 2026/06/28 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

abstract class UnitCase extends TestCase
{
    private $previousExceptionHandler;

    protected function setUp(): void
    {
        $this->previousExceptionHandler = set_exception_handler(null);
    }

    protected function tearDown(): void
    {
        if ($this->previousExceptionHandler !== null) {
            set_exception_handler($this->previousExceptionHandler);
        } else {
            restore_exception_handler();
        }
    }
}
// vim: ft=php sts=4 sw=4 ts=4 et :
