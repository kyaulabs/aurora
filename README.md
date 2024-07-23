# Aurora

<img src=".github/media/aurora.ans.png" alt="Repository Logo" />

[https://kyaulabs.com/](https://kyaulabs.com/)

[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-2.1-4baaaa.svg?logo=open-source-initiative&logoColor=4baaaa)](CODE_OF_CONDUCT.md) &nbsp; [![Conventional Commits](https://img.shields.io/badge/conventional%20commits-1.0.0-fe5196?style=flat&logo=conventionalcommits)](https://www.conventionalcommits.org/en/v1.0.0/) &nbsp; [![GitHub](https://img.shields.io/github/license/kyaulabs/aurora?logo=creativecommons)](LICENSE) &nbsp; [![Gitleaks](https://img.shields.io/badge/protected%20by-gitleaks-blue?logo=git&logoColor=seagreen&color=seagreen)](https://github.com/zricethezav/gitleaks)  
[![Semantic Versioning](https://img.shields.io/github/v/release/kyaulabs/aurora?include_prereleases&logo=semver&sort=semver)](https://semver.org) &nbsp; [![Discord](https://img.shields.io/discord/88713030895943680?logo=discord&color=blue&logoColor=white)](https://discord.gg/DSvUNYm)

* [About](#about)
  * [What's Included](#whats-included)
* [Usage](#usage)

## About

Aurora is built for rapid deployment of sites built with AJAX content loading.
It also attempts to use the most up-to-date HTML specifications / web standards
in use today.

This has been created with personal use in mind and is highly tailored to the
types of sites I create and run. That said, this could be easily adapted to
anyones needs with a bit of work.

### What's Included

* [x] Template-based HTML header
* [x] Resource preloading
* [x] Subresource Integrity (SRI) enabled
* [x] Performance Statistics
* [x] SQL Handler

## Usage

To use Aurora, one needs to either use the default HTML header template or
create your own. Once finished, move Aurora into a folder above the web root
then create an `index.php` similar to the following:

```php
# $KYAULabs: index.php,v 1.0.0 2024/07/22 22:51:26 -0700 kyau Exp $

$rus = getrusage();
require_once(__DIR__ . "/../../aurora/aurora.inc.php");

$site = new KYAULabs\Aurora("index.html", "/cdn", true, true);
$site->title = "Website Title";
$site->description = "Full website description for search engines.";
$site->dns = ["cdn.domain.com"];
$site->preload = [
    '/css/site.min.css' => 'style',
    '/javascript/jquery.min.js' => 'script',
    '/javascript/site.min.js' => 'script',
];
$site->css = [
    '../api/css/site.min.css' => '//api.domain.com/css/site.min.css',
];
$site->js = [
    '../api/javascript/jquery.min.js' => '//api.domain.com/javascript/jquery.min.js',
    '../api/javascript/site.min.js' => '//api.domain.com/javascript/site.min.js',
];
$site->htmlHeader();
// <content>
echo "\t<header></header>\n\t<main></main>\n\t<footer></footer>\n";
// </content>
$site->htmlFooter();
echo $site->comment($rus, $_SERVER['SCRIPT_FILENAME'], true);
```

Looking over the source code can give you more insights on how to utilize Aurora.
