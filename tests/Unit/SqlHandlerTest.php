<?php

# $KYAULabs: SqlHandlerTest.php,v 1.0.0 2026/07/01 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

use Tests\TestCase;
use KYAULabs\SQLHandler;

require_once __DIR__ . '/../../sql.inc.php';

define('SQL_USER', 'test_user');
define('SQL_PASSWD', 'test_pass');
define('SQL_HOST', '127.0.0.1');
define('SQL_PORT', 3306);

describe('SQLHandler constructor', function () {
    test('throws when db parameter is null', function () {
        expect(fn () => new SQLHandler(null))
            ->toThrow(\Exception::class, 'Required parameter is null.');
    });

    test('attempts PDO connection and handles failure gracefully', function () {
        ob_start();
        $handler = new SQLHandler('test_db');
        $output = ob_get_clean();

        expect($handler->pdo)->toBeNull();
    });
});

describe('SQLHandler query and setDatabase with null PDO', function () {
    test('setDatabase returns false when PDO is null', function () {
        ob_start();
        $handler = new SQLHandler('test_db');
        ob_end_clean();

        expect($handler->setDatabase('other_db'))->toBeFalse();
    });

    test('query returns false when PDO is null', function () {
        $handler = new SQLHandler('test_db');

        expect($handler->query('SELECT 1'))->toBeFalse();
    });
});

describe('SQLHandler handleException', function () {
    test('outputs formatted HTML error with display_errors on', function () {
        $e = new \PDOException('Test DB Error', 1045);

        ini_set('display_errors', '1');

        ob_start();
        SQLHandler::handleException($e);
        $output = ob_get_clean();

        expect($output)->toContain('<span class="error">')
            ->and($output)->toContain('<strong>An error has occurred: </strong>')
            ->and($output)->toContain('Test DB Error');
    });

    test('logs to error_log when display_errors is off', function () {
        $e = new \PDOException('Silent DB Error', 1045);

        ini_set('display_errors', '0');

        ob_start();
        SQLHandler::handleException($e);
        $output = ob_get_clean();

        expect($output)->toBe('');
    });
});

describe('SQLHandler procException with THROW_EXCEPTION', function () {
    test('re-throws PDOException when err is set to THROW_EXCEPTION', function () {
        expect(fn () => new class ('test_db') extends SQLHandler {
            protected $err = SQLHandler::THROW_EXCEPTION;
        })->toThrow(\PDOException::class);
    });
});

describe('SQLHandler setDatabase with PDO error', function () {
    test('returns false when PDO exec throws an exception', function () {
        $handler = new SQLHandler('test_db');

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('exec')
            ->willThrowException(new \PDOException('Table not found', 1146));

        $handler->pdo = $mockPdo;

        ini_set('display_errors', '1');

        ob_start();
        $result = $handler->setDatabase('bad_db');
        $output = ob_get_clean();

        expect($result)->toBeFalse()
            ->and($output)->toContain('Table not found');
    });
});

// vim: ft=php sts=4 sw=4 ts=4 et :
