<?php

# $KYAULabs: AuroraTest.php,v 1.0.0 2026/06/27 00:00:00 -0700 kyau Exp $

declare(strict_types=1);

use Tests\TestCase;
use KYAULabs\Aurora;

test('constructor throws on null template', function () {
    expect(fn () => new Aurora(null, '/cdn', false, false))
        ->toThrow(\KYAULabs\AuroraException::class);
});

test('constructor throws on missing template file', function () {
    expect(fn () => new Aurora('nonexistent.html', '/cdn', false, false))
        ->toThrow(\KYAULabs\AuroraException::class, 'Aurora HTML5 template not found.');
});

test('__set stores scalar values in vars', function () {
    $site = new Aurora('index.html', '/cdn', false, false);
    $site->title = 'Test Title';
    expect($site->title)->toBe('Test Title');
});

test('__get returns null for missing property', function () {
    $site = new Aurora('index.html', '/cdn', false, false);
    set_error_handler(fn () => true);
    $result = $site->nonexistent;
    restore_error_handler();
    expect($result)->toBeNull();
});

test('comment returns formatted HTML comment', function () {
    $rus = getrusage();
    $site = new Aurora('index.html', '/cdn', false, false);
    $comment = $site->comment($rus, __FILE__);
    expect($comment)->toContain('<!--');
    expect($comment)->toContain('-->');
});

test('version returns version string', function () {
    $site = new Aurora('index.html', '/cdn', false, false);
    $version = $site->version(__FILE__);
    expect($version)->toBeString();
});
// vim: ft=php sts=4 sw=4 ts=4 et :
