![ANSI Logo](https://raw.githubusercontent.com/kyaulabs/aurora/master/aurora.ans.png "ANSI Logo")

[![](https://img.shields.io/badge/coded_in-vim-green.svg?logo=vim&logoColor=brightgreen&colorB=brightgreen&longCache=true&style=flat)](https://vim.org) &nbsp; [![](https://img.shields.io/badge/license-AGPL_v3-blue.svg?style=flat)](https://raw.githubusercontent.com/kyaulabs/aurora/master/LICENSE) &nbsp; [![](https://img.shields.io/badge/php-8.0+-C85000.svg?style=flat)](https://www.php.net/)

### About

Aurora is built for rapid deployment of Ajax loaded content pages. It is an
attempt at using the most up-to-date HTML5 spec / web standards in use today.

This has been created with personal use in mind and is highly tailored to the
types of sites I create and run. That said, this could be easily adapted to
anyones needs with a bit of work.

### Usage

To use Aurora, one needs to either use the default HTML template or create
your own. Once finished, move Aurora into a folder above the web root then
create an `index.php` similar to the following:

```php
$rus = getrusage();
require_once("../../aurora/aurora.inc.php");

$site = new KYAULabs\Aurora("index.html", "/nginx/https/domain_com/www", "www.domain.com", true, true);
$site->title = "Website Title";
$site->description = "Full website description for search engines.";
$site->api = ["api.domain.com"];
$site->preload = [
    '/css/site.min.css' => 'style',
    '/javascript/jquery-3.6.0.min.js' => 'script',
    '/javascript/site.min.js' => 'script',
];
$site->css = [
    '../api/css/site.min.css' => '//api.domain.com/css/site.min.css',
];
$site->js = [
    '../api/javascript/jquery-3.6.0.min.js' => '//api.domain.com/javascript/jquery-3.6.0.min.js',
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
