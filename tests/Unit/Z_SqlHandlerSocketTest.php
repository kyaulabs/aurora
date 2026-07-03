<?php

# $KYAULabs: Z_SqlHandlerSocketTest.php,v 1.0.0 2026/07/02 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

use KYAULabs\SQLHandler;

require_once __DIR__ . '/../../sql.inc.php';

test('uses unix socket DSN when SQL_SOCKET is defined', function () {
    define('SQL_USER', 'test_user');
    define('SQL_PASSWD', 'test_pass');
    define('SQL_SOCKET', '/tmp/mysql.sock');

    ob_start();
    $handler = new SQLHandler('test_db');
    ob_end_clean();

    expect($handler->pdo)->toBeNull();
});

// vim: ft=php sts=4 sw=4 ts=4 et :
