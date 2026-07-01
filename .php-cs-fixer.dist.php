<?php

# $KYAULabs: .php-cs-fixer.dist.php,v 1.0.0 2026/06/24 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
            ->exclude(['node_modules', 'vendor'])
            ->notPath('#^node_modules/#')
            ->notPath('#^vendor/#')
    )
;
// vim: ft=php sts=4 sw=4 ts=4 et :
