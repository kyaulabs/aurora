<?php

# $KYAULabs: Z_SqlHandlerSocketTest.php kyau@nova 2026/07/04 -0700 Exp $


declare(strict_types=1);

use KYAULabs\SQLHandler;

require_once __DIR__ . '/../../sql.inc.php';

test('uses unix socket DSN when SQL_SOCKET is defined', function () {
    if (!defined('SQL_USER')) {
        define('SQL_USER', 'test_user');
    }
    if (!defined('SQL_PASSWD')) {
        define('SQL_PASSWD', 'test_pass');
    }
    define('SQL_SOCKET', '/tmp/mysql.sock');

    ob_start();
    $handler = new SQLHandler('test_db');
    ob_end_clean();

    expect($handler->pdo)->toBeNull();
});

// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
