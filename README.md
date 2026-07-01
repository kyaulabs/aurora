<img src=".github/media/aurora-logo-dark.svg" alt="COSMOS" width="256"><br/>
<!--<img src=".github/media/aurora.ans.png" alt="Repository Logo" />-->

[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-2.1-4baaaa.svg?logo=open-source-initiative&logoColor=4baaaa)](CODE_OF_CONDUCT.md)
[![Conventional Commits](https://img.shields.io/badge/conventional%20commits-1.0.0-fe5196?style=flat&logo=conventionalcommits)](https://www.conventionalcommits.org/en/v1.0.0/)
[![GitHub](https://img.shields.io/github/license/kyaulabs/aurora?logo=creativecommons)](LICENSE)
[![Semantic Versioning](https://img.shields.io/badge/release-0.1.0-red?logo=semver)](https://semver.org)  
[![Gitleaks](https://img.shields.io/badge/protected%20by-gitleaks-blue?logo=git&logoColor=seagreen&color=seagreen)](https://github.com/zricethezav/gitleaks)
[![Discord](https://img.shields.io/discord/88713030895943680?logo=discord&color=blue&logoColor=white)](https://discord.gg/DSvUNYm)

## About

Aurora is a lightweight PHP HTML5 template engine built for rapid deployment
of sites with AJAX content loading. It targets modern HTML specifications and
web standards.

This has been created with personal use in mind and is highly tailored to the
types of sites I create and run. That said, this could be easily adapted to
anyone's needs with a bit of work.

### Requirements

* PHP 8.5 or later
* MariaDB / MySQL (for the SQL handler)
* Composer

### What's Included

* [x] Template-based HTML header with `{{ var }}` / `{% func() %}` syntax
* [x] Resource preloading with DNS prefetch and preconnect
* [x] Subresource Integrity (SRI) — sha512 hashes with cache-busting versions
* [x] CSS stylesheet injection
* [x] JavaScript injection (regular scripts and ES modules)
* [x] External script support for third-party resources
* [x] Custom template overlay with fallback to default
* [x] Open Graph meta tags
* [x] Performance statistics (CPU time, version info)
* [x] PDO-based SQL handler with prepared statements
* [x] Custom exception handling (`AuroraException`)

## Installation

Add Aurora as a git submodule in your project:

```bash
git submodule add https://github.com/kyaulabs/aurora.git aurora
```

Copy and configure the database settings:

```bash
cp aurora/settings.example.php aurora/settings.inc.php
```

Edit `settings.inc.php` with your MariaDB/MySQL credentials.

## Usage

```php
<?php
# $KYAULabs: index.php,v 1.0.0 2026/06/28 00:00:00 -0700 kyau Exp $

$rus = getrusage();
require_once(__DIR__ . "/../aurora/aurora.inc.php");

$site = new KYAULabs\Aurora("index.html", "/cdn", true, true);
$site->title = "Page Title";
$site->description = "Page description for search engines.";
$site->dns = ["cdn.domain.com"];
$site->css = [
    'css/site.min.css' => '//cdn.domain.com/css/site.min.css',
];
$site->preload = [
    '/css/site.min.css' => 'style',
];
$site->js = [
    'javascript/site.min.js' => '//cdn.domain.com/javascript/site.min.js',
];
$site->mjs = [
    'javascript/app.min.js' => '//cdn.domain.com/javascript/app.min.js',
];

$site->htmlHeader();
// page content
echo "\t<header></header>\n\t<main></main>\n\t<footer></footer>\n";
$site->htmlFooter();
echo $site->comment($rus, $_SERVER['SCRIPT_FILENAME'], true);
```

### Template System

Templates are plain HTML files stored in `aurora/html/`. The default template
(`index.html`) uses two placeholder syntaxes:

* `{{ variable_name }}` — replaced with scalar values set on the `$site` object
  (e.g. `$site->title = "Hello"` fills in `{{ title }}`)
* `{% func() %}` — replaced with generated HTML from internal methods:

| Tag | Output |
|-----|--------|
| `{% css() %}` | `<link rel="stylesheet">` tags with SRI |
| `{% preload() %}` | `<link rel="dns-prefetch">`, `<link rel="preconnect">`, and `<link rel="preload">` tags |

> **Note:** JavaScript is injected by `htmlFooter()`, not from the template.
> The closing `</body>` and `</html>` tags are also emitted by `htmlFooter()`.

### Template Overlay

The constructor accepts an optional 5th parameter for a custom template
directory. If a template file exists there, it is used; otherwise the default
`aurora/html/` template is the fallback:

```php
$site = new KYAULabs\Aurora("index.html", "/cdn", true, true, __DIR__ . "/templates");
```

This lets you override the default template per-project without modifying Aurora.

### External Scripts

For third-party scripts that shouldn't be hashed (e.g. analytics, CAPTCHA),
use the `<external>` key:

```php
$site->js = [
    '<external>' => 'https://third-party.com/script.js',
];
$site->mjs = [
    '<external>' => 'https://third-party.com/module.js',
];
```

External scripts are auto-assigned sequential `id="ext1"`, `id="ext2"`, etc.

### Constructor Reference

```php
new KYAULabs\Aurora(
    ?string $template,     // template filename (e.g. "index.html")
    ?string $cdn,          // CDN directory path relative to webroot (default: '/cdn')
    bool $status,          // enable verbose error display in browser
    bool $html,            // set Content-Type header to text/html
    ?string $templateDir   // custom template overlay directory (optional)
)
```

### Properties

Set properties on the `$site` object using assignment syntax:

| Property | Type | Description |
|----------|------|-------------|
| `$site->title` | `string` | Page title (`<title>` and `og:title`) |
| `$site->description` | `string` | Meta description and `og:description` |
| `$site->dns` | `array` | Domains for DNS prefetch/preconnect tags |
| `$site->css` | `array` | Path-to-URL map for stylesheets |
| `$site->js` | `array` | Path-to-URL map for JavaScript files |
| `$site->mjs` | `array` | Path-to-URL map for JavaScript ES modules |
| `$site->preload` | `array` | URL-to-type map for resource preloading |

> Any other property name is stored as a template variable for `{{ var }}`
> substitution. Custom template variables must be set **before** calling
> `htmlHeader()`.

### Methods

| Method | Description |
|--------|-------------|
| `htmlHeader()` | Renders the template header |
| `htmlFooter()` | Emits `<script>` tags and closes `</body></html>` |
| `comment($rus, $script, $vim)` | Returns an HTML comment with version and performance stats |
| `version($script)` | Returns the version string from a file's RCS header |
| `testVariables()` | Debug: reports which template variables were successfully replaced |

## SQL Handler

Aurora includes a PDO-based MariaDB/MySQL handler with real prepared statements
and utf8mb4 charset.

### Configuration

1. Copy `settings.example.php` to `settings.inc.php`
2. Edit the constants with your database credentials:

```php
define("SQL_HOST", "127.0.0.1");       // optional — defaults to 127.0.0.1
define("SQL_PORT", 3306);              // optional — defaults to 3306
define("SQL_SOCKET", "/run/mysqld/mysqld.sock"); // Linux-only; remove on Windows
define("SQL_USER", "username");
define("SQL_PASSWD", "password");
```

> `settings.inc.php` is `.gitignore`d — never commit database credentials.

### Basic Usage

```php
require_once(__DIR__ . "/../aurora/sql.inc.php");

$db = new KYAULabs\SQLHandler("my_database");

// prepared statement with named parameters
$stmt = $db->query("SELECT * FROM users WHERE id = :id", [':id' => 42]);
while ($row = $stmt->fetch()) {
    echo $row['username'];
}

// switch databases
$db->setDatabase("other_database");
```

### Error Modes

Set the error handling mode by modifying the protected `$err` property:

| Constant | Behavior |
|----------|----------|
| `SQLHandler::INTERNAL_HANDLING` | Display/log the error (default) |
| `SQLHandler::THROW_EXCEPTION` | Re-throw `PDOException` for custom handling |
| `SQLHandler::IGNORE_ERRORS` | Silently ignore errors |

## Development

### Testing

Tests use [Pest PHP](https://pestphp.com/):

```bash
php vendor/bin/pest
php vendor/bin/pest --coverage     # with coverage report
```

### Linting

```bash
# PHP syntax check
php -l file.php

# PHP code style (PSR-12)
php-cs-fixer fix . --dry-run --diff
```
