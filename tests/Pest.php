<?php

# $KYAULabs: Pest.php kyau@nova 2026/07/04 -0700 Exp $


declare(strict_types=1);

uses(Tests\Unit\UnitCase::class)->in('Unit');
uses(Tests\Feature\FeatureCase::class)->in('Feature');
uses(Tests\Integration\IntegrationCase::class)->in('Integration');

require_once __DIR__ . '/../aurora.inc.php';

/*
|--------------------------------------------------------------------------
| Arch Tests
|--------------------------------------------------------------------------
|
| Architecture tests enforce invariants across the entire codebase without
| requiring per-class test files.
|
*/

arch('no debug functions in production code')
    ->expect(['dd', 'dump', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('backend classes use strict types')
    ->expect('KYAULabs')
    ->toUseStrictTypes();

// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
