<?php

# $KYAULabs: settings.example.php Sean Bruen@NOVA 2026/07/04 -0700 Exp $


declare(strict_types=1);

/*
 *
 * **WARNING!**
 *
 * 1. This file must be named `settings.inc.php` in order to function
 * 2. Update `username` and `password` with the SQL username/password
 *
 */

define("SQL_HOST", "127.0.0.1");
define("SQL_PORT", 3306);
define("SQL_SOCKET", "/run/mysqld/mysqld.sock"); // Linux-only; comment out or remove on Windows/macOS
define("SQL_USER", "username");
define("SQL_PASSWD", "password");
/**
 * vim: ft=php sts=4 sw=4 ts=4 et:
 */

// vim: ft=php sts=4 sw=4 ts=4 et :
