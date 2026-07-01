<?php

# $KYAULabs: Pest.php,v 1.0.0 2026/06/28 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

uses(Tests\Unit\UnitCase::class)->in('Unit');
uses(Tests\Feature\FeatureCase::class)->in('Feature');
uses(Tests\Integration\IntegrationCase::class)->in('Integration');

require_once __DIR__ . '/../aurora.inc.php';

// vim: ft=php sts=4 sw=4 ts=4 et :
