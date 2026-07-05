<?php

# $KYAULabs: AuroraTest.php kyau@nova 2026/07/04 -0700 Exp $


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

test('__get returns empty array for array properties on fresh instance', function () {
    $site = new Aurora('index.html', '/cdn', false, false);
    expect($site->css)->toBe([]);
    expect($site->dns)->toBe([]);
    expect($site->js)->toBe([]);
    expect($site->mjs)->toBe([]);
    expect($site->preload)->toBe([]);
});

test('__get returns array properties via __get after assignment', function () {
    $site = new Aurora('index.html', '/cdn', false, false);
    $site->css = ['/a.css' => 'a.css'];
    $site->js = ['/app.js' => 'app.js'];
    expect($site->css)->toBe(['/a.css' => 'a.css']);
    expect($site->js)->toBe(['/app.js' => 'app.js']);
});

describe('comment()', function () {
    $rus = getrusage();
    $dummyScript = __FILE__;

    test('contains SemVer version and timing', function () use ($rus, $dummyScript) {
        $site = new Aurora('index.html', '/cdn', false, false);
        $comment = $site->comment($rus, $dummyScript);

        expect($comment)->toContain('<!--');
        expect($comment)->toContain('-->');
        expect($comment)->toMatch('/Aurora v\d+\.\d+\.\d+/');
        expect($comment)->toMatch('/compute:-?\d+ms/');
        expect($comment)->toMatch('/syscall:-?\d+ms/');
    });

    test('includes vim modeline when vim flag is true', function () use ($rus, $dummyScript) {
        $site = new Aurora('index.html', '/cdn', false, false);
        $comment = $site->comment($rus, $dummyScript, true);

        expect($comment)->toContain('vim: ft=html sts=4 sw=4 ts=4 noet:');
    });

    test('excludes vim modeline when vim flag is false', function () use ($rus, $dummyScript) {
        $site = new Aurora('index.html', '/cdn', false, false);
        $comment = $site->comment($rus, $dummyScript, false);

        expect($comment)->not->toContain('vim:');
    });
});

describe('version()', function () {
    test('returns SemVer version from version.inc.php', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $version = $site->version();

        expect($version)->toMatch('/^v\d+\.\d+\.\d+/');
    });
});
describe('__set/__get array properties', function () {
    test('merges dns arrays on repeated set', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->dns = ['cdn.example.com'];
        $site->dns = ['cdn2.example.com'];

        $dns = $site->dns;
        expect($dns)->toBeArray();
        expect($dns)->toHaveCount(2);
        expect($dns)->toContain('cdn.example.com');
        expect($dns)->toContain('cdn2.example.com');
    });

    test('merges css arrays on repeated set', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->css = ['/a.css' => 'a.css'];
        $site->css = ['/b.css' => 'b.css'];

        $css = $site->css;
        expect($css)->toBeArray();
        expect($css)->toHaveCount(2);
        expect($css['/a.css'])->toBe('a.css');
        expect($css['/b.css'])->toBe('b.css');
    });

    test('first set on empty array property stores directly', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->preload = ['/main.js' => 'script'];

        $preload = $site->preload;
        expect($preload)->toBeArray();
        expect($preload)->toHaveCount(1);
        expect($preload['/main.js'])->toBe('script');
    });

    test('returns dns array via __get after setting', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->dns = ['dns.example.com', 'fonts.example.com'];

        $dns = $site->dns;
        expect($dns)->toBeArray();
        expect($dns)->toHaveCount(2);
        expect($dns[0])->toBe('dns.example.com');
        expect($dns[1])->toBe('fonts.example.com');
    });
});

describe('constructor', function () {
    test('status=true enables display_errors', function () {
        $site = new Aurora('index.html', '/cdn', true, false);

        expect(ini_get('display_errors'))->toBe('1');
        expect(ini_get('display_startup_errors'))->toBe('1');
        expect(ini_get('html_errors'))->toBe('1');
    });

    test('status=false disables display_errors', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        expect(ini_get('display_errors'))->toBe('0');
        expect(ini_get('display_startup_errors'))->toBe('0');
        expect(ini_get('html_errors'))->toBe('0');
        expect(ini_get('error_reporting'))->toBe((string)E_ALL);
    });

    test('html=true stores html flag', function () {
        $site = new Aurora('index.html', '/cdn', false, true);

        expect($site->html)->toBeTrue();
    });

    test('html=false stores html flag', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        expect($site->html)->toBeFalse();
    });

    test('uses templateDir overlay when template exists there', function () {
        $overlayDir = __DIR__ . '/fixtures/overlay';
        $site = new Aurora('index.html', '/cdn', false, false, $overlayDir);

        expect($site->html)->toBeFalse();
    });

    test('throws on invalid CDN directory', function () {
        expect(fn () => new Aurora('index.html', '/nonexistent_cdn', false, false))
            ->toThrow(\KYAULabs\AuroraException::class, 'Invalid directory');
    });
});

describe('htmlHeader()', function () {
    test('returns false when no variables are set', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        ob_start();
        $result = $site->htmlHeader();
        ob_get_clean();

        expect($result)->toBeFalse();
    });

    test('replaces template variable placeholders in output', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->title = 'Test Title';
        $site->description = 'A description for testing';

        ob_start();
        $result = $site->htmlHeader();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('Test Title')
            ->and($output)->toContain('A description for testing')
            ->and($output)->not->toContain('{{ title }}')
            ->and($output)->not->toContain('{{ description }}');
    });

    test('injects stylesheet link tags with SRI hashes', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->title = 'Test Title';
        $site->css = ['tests/cdn/style.css' => '/style.css'];

        ob_start();
        $result = $site->htmlHeader();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<link rel="stylesheet" type="text/css"')
            ->and($output)->toContain('integrity="sha512-')
            ->and($output)->toContain('crossorigin="anonymous"')
            ->and($output)->toMatch('/\?v=[a-f0-9]+/');
    });

    test('injects DNS prefetch and preload tags', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->title = 'Test Title';
        $site->dns = ['cdn.example.com'];
        $site->preload = ['/font.woff2' => 'font'];

        ob_start();
        $result = $site->htmlHeader();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<link rel="dns-prefetch" href="//cdn.example.com"')
            ->and($output)->toContain('<link rel="preconnect" href="//cdn.example.com"')
            ->and($output)->toContain('<link rel="preload" href="//cdn.example.com/font.woff2"');
    });
});

describe('htmlFooter()', function () {
    test('injects external JS script tags', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->js = ['<external>' => 'https://example.com/app.js'];

        ob_start();
        $result = $site->htmlFooter();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<script src="https://example.com/app.js"')
            ->and($output)->toContain('async defer')
            ->and($output)->toContain('</body>')
            ->and($output)->toContain('</html>');
    });

    test('injects file-based JS script tags with SRI hashes', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->js = ['tests/cdn/app.js' => '/app.js'];

        ob_start();
        $result = $site->htmlFooter();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<script src="/app.js?v=')
            ->and($output)->toContain('integrity="sha512-')
            ->and($output)->toContain('crossorigin="anonymous"')
            ->and($output)->toContain('defer="defer"');
    });

    test('injects ES module script tags with SRI hashes', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->mjs = ['tests/cdn/module.js' => '/module.js'];

        ob_start();
        $result = $site->htmlFooter();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<script src="/module.js?v=')
            ->and($output)->toContain('type="module"')
            ->and($output)->toContain('integrity="sha512-');
    });

    test('outputs closing body and html tags when no scripts are set', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        ob_start();
        $result = $site->htmlFooter();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toBe("\n</body>\n</html>");
    });
});

describe('testVariables()', function () {
    test('lists replaced scalar variables after rendering', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->title = 'Report Title';
        $site->description = 'Report Description';

        ob_start();
        $site->htmlHeader();
        ob_end_clean();

        $report = $site->testVariables();

        expect($report)->toContain('&#x2714; title: Report Title');
        expect($report)->toContain('&#x2714; description: Report Description');
        expect($report)->toContain('&#x2714; css: array(data)');
        expect($report)->toContain('&#x2714; preload: array(data)');
    });
});

describe('exceptionHandler()', function () {
    test('outputs head-body separator when code is 1 and display_errors is on', function () {
        ini_set('display_errors', '1');
        $e = new \KYAULabs\AuroraException('Handler test', 'test', 1);

        ob_start();
        \KYAULabs\Aurora::exceptionHandler($e);
        $output = ob_get_clean();

        expect($output)->toContain('</head>')
            ->and($output)->toContain('<body>')
            ->and($output)->toContain('Aurora - Warning!')
            ->and($output)->toContain('Handler test');
    });

    test('skips head-body separator when code is not 1', function () {
        ini_set('display_errors', '1');
        $e = new \KYAULabs\AuroraException('Other error', 'test', 5);

        ob_start();
        \KYAULabs\Aurora::exceptionHandler($e);
        $output = ob_get_clean();

        expect($output)->not->toContain('</head>')
            ->and($output)->toContain('Aurora - Warning!')
            ->and($output)->toContain('Other error');
    });

    test('logs to error_log when display_errors is off', function () {
        ini_set('display_errors', '0');
        $e = new \KYAULabs\AuroraException('Silent error', 'test', 0);

        $logged = false;
        set_error_handler(function () use (&$logged) {
            $logged = true;
            return true;
        });

        ob_start();
        \KYAULabs\Aurora::exceptionHandler($e);
        $output = ob_get_clean();

        restore_error_handler();
        expect($output)->toBe('');
    });
});

describe('htmlPreload()', function () {
    test('injects SRI-hashed preload tags for script and style types', function () {
        $cwd = getcwd();
        chdir(__DIR__);

        try {
            $site = new Aurora('index.html', '/cdn', false, false);
            $site->title = 'Test Title';
            $site->dns = ['cdn.example.com'];
            $site->preload = ['/style.css' => 'style'];

            ob_start();
            $result = $site->htmlHeader();
            $output = ob_get_clean();

            chdir($cwd);

            expect($result)->toBeTrue()
                ->and($output)->toContain('integrity="sha512-')
                ->and($output)->toContain('as="style"')
                ->and($output)->toContain('crossorigin="anonymous"')
                ->and($output)->toContain('dns-prefetch');
        } finally {
            chdir($cwd);
        }
    });

    test('throws when DNS prefetch is not configured for script or style preload', function () {
        $cwd = getcwd();
        chdir(__DIR__);

        try {
            $site = new Aurora('index.html', '/cdn', false, false);
            $site->title = 'Test Title';
            $site->preload = ['/style.css' => 'style'];

            expect(fn () => $site->htmlHeader())
                ->toThrow(\KYAULabs\AuroraException::class, 'DNS prefetch not found!');
        } finally {
            chdir($cwd);
        }
    });
});

describe('htmlHeader() render failure', function () {
    test('returns false when template rendering fails', function () {
        $cwd = getcwd();
        chdir(__DIR__);

        try {
            $tempFile = sys_get_temp_dir() . '/aurora_test_render_' . uniqid() . '.html';
            file_put_contents($tempFile, '{{ title }}');

            $site = new Aurora(basename($tempFile), '/cdn', false, false, dirname($tempFile));
            $site->title = 'Test';

            unlink($tempFile);

            ob_start();
            set_error_handler(fn () => true);
            $result = $site->htmlHeader();
            restore_error_handler();
            $output = ob_get_clean();

            chdir($cwd);

            expect($result)->toBeFalse()
                ->and($output)->toContain('template rendering has failed');
        } finally {
            chdir($cwd);
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    });
});

describe('htmlFooter()', function () {
    test('injects external ES module script tags', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->mjs = ['<external>' => 'https://example.com/module.js'];

        ob_start();
        $result = $site->htmlFooter();
        $output = ob_get_clean();

        expect($result)->toBeTrue()
            ->and($output)->toContain('<script src="https://example.com/module.js"')
            ->and($output)->toContain('type="module"')
            ->and($output)->toContain('id="ext1"');
    });
});

describe('htmlStyles()', function () {
    test('throws when CSS file hash computation fails', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->title = 'Test';
        $site->css = ['/nonexistent_css_file.css' => '/style.css'];

        expect(function () use ($site) {
            set_error_handler(fn () => true);
            try {
                $site->htmlHeader();
            } finally {
                restore_error_handler();
            }
        })->toThrow(\KYAULabs\AuroraException::class, 'hash computation failed');
    });
});

describe('htmlPreload() file not found', function () {
    test('throws when preload file does not exist', function () {
        $cwd = getcwd();
        chdir(__DIR__);

        try {
            $site = new Aurora('index.html', '/cdn', false, false);
            $site->title = 'Test';
            $site->dns = ['cdn.example.com'];
            $site->preload = ['/nonexistent_preload.js' => 'script'];

            expect(fn () => $site->htmlHeader())
                ->toThrow(\KYAULabs\AuroraException::class, 'does not exist');
        } finally {
            chdir($cwd);
        }
    });

    test('throws when preload hash computation fails', function () {
        $cwd = getcwd();
        chdir(__DIR__);

        try {
            $site = new Aurora('index.html', '/cdn', false, false);
            $site->title = 'Test';
            $site->dns = ['cdn.example.com'];
            $site->preload = ['/../' => 'style'];

            expect(function () use ($site) {
                set_error_handler(fn () => true);
                try {
                    $site->htmlHeader();
                } finally {
                    restore_error_handler();
                }
            })->toThrow(\KYAULabs\AuroraException::class, 'hash computation failed');
        } finally {
            chdir($cwd);
        }
    });
});

describe('htmlScripts()', function () {
    test('throws when ES module file does not exist', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->mjs = ['/nonexistent_module.js' => '/module.js'];

        expect(fn () => $site->htmlFooter())
            ->toThrow(\KYAULabs\AuroraException::class, 'does not exist');
    });

    test('throws when regular JS file does not exist', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->js = ['/nonexistent_script.js' => '/app.js'];

        expect(fn () => $site->htmlFooter())
            ->toThrow(\KYAULabs\AuroraException::class, 'does not exist');
    });

    test('throws when ES module hash computation fails', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->mjs = [__DIR__ => '/module.js'];

        expect(function () use ($site) {
            set_error_handler(fn () => true);
            try {
                $site->htmlFooter();
            } finally {
                restore_error_handler();
            }
        })->toThrow(\KYAULabs\AuroraException::class, 'hash computation failed');
    });

    test('throws when regular JS hash computation fails', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $site->js = [__DIR__ => '/app.js'];

        expect(function () use ($site) {
            set_error_handler(fn () => true);
            try {
                $site->htmlFooter();
            } finally {
                restore_error_handler();
            }
        })->toThrow(\KYAULabs\AuroraException::class, 'hash computation failed');
    });
});

describe('projectVersion()', function () {
    test('version returns SemVer string when version file exists', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $version = $site->version();

        expect($version)->not->toBeNull();
        expect($version)->toMatch('/^v\d+\.\d+\.\d+/');
    });

    test('version is stable across repeated calls', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $first = $site->version();
        $second = $site->version();

        expect($first)->toBe($second);
    });
});

describe('phpSet()', function () {
    test('returns false and echoes error on ini_set failure', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        $reflection = new \ReflectionMethod(\KYAULabs\Aurora::class, 'phpSet');

        ob_start();
        $result = $reflection->invoke($site, 'nonexistent_php_setting', 'value');
        $output = ob_get_clean();

        expect($result)->toBeFalse()
            ->and($output)->toContain('Error: could not set');
    });
});

describe('testVariables()', function () {
    test('reports orphaned success entry when variable is missing', function () {
        $site = new Aurora('index.html', '/cdn', false, false);

        $reflection = new \ReflectionProperty(\KYAULabs\Aurora::class, 'vars_success');
        $reflection->setValue($site, ['orphaned_key']);

        $report = $site->testVariables();

        expect($report)->toContain('&#x2715; orphaned_key: success case but no variable?!');
    });
});

describe('render() feof failure', function () {
    test('echoes failure when fgets stops but feof reports false', function () {
        if (!in_array('errorfeof', stream_get_wrappers(), true)) {
            require_once __DIR__ . '/fixtures/ErrorFeofStream.php';
            stream_wrapper_register('errorfeof', \Tests\Unit\Fixtures\ErrorFeofStream::class);
        }

        try {
            \Tests\Unit\Fixtures\ErrorFeofStream::setData("line\n");

            $site = new Aurora('test.html', '/cdn', false, false, 'errorfeof://tpl');
            $site->title = 'Test';

            ob_start();
            $result = $site->htmlHeader();
            $output = ob_get_clean();

            expect($result)->toBeFalse()
                ->and($output)->toContain('unexpected fgets() failure');
        } finally {
            if (in_array('errorfeof', stream_get_wrappers(), true)) {
                stream_wrapper_unregister('errorfeof');
            }
        }
    });
});

describe('version() edge cases', function () {
    test('version ignores script parameter for backward compatibility', function () {
        $site = new Aurora('index.html', '/cdn', false, false);
        $version = $site->version('/nonexistent/file.php');

        expect($version)->not->toBeNull();
        expect($version)->toMatch('/^v\d+\.\d+\.\d+/');
    });
});

// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
