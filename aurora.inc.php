<?php

declare(strict_types=1);

/**
 * $KYAULabs: aurora.inc.php,v 1.1.4 2026/06/29 13:21:45 -0700 kyau Exp $
 * ▄▄▄▄ ▄▄▄▄ ▄▄▄▄▄▄▄▄▄ ▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄
 * █ ▄▄ ▄ ▄▄ ▄ ▄▄▄▄ ▄▄ ▄    ▄▄   ▄▄▄▄ ▄▄▄▄  ▄▄▄ ▀
 * █ ██ █ ██ █ ██ █ ██ █    ██   ██ █ ██ █ ██▀  █
 * ■ ██▄▀ ██▄█ ██▄█ ██ █ ▀▀ ██   ██▄█ ██▄▀ ▀██▄ ■
 * █ ██ █ ▄▄ █ ██ █ ██ █    ██▄▄ ██ █ ██ █  ▄██ █
 * ▄ ▀▀ ▀ ▀▀▀▀ ▀▀ ▀ ▀▀▀▀    ▀▀▀▀ ▀▀ ▀ ▀▀▀▀ ▀▀▀  █
 * ▀▀▀▀▀▀▀▀▀▀▀▀▀▀ ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀ ▀▀▀▀▀▀▀▀▀▀▀▀▀
 *
 * Aurora HTML5 Template Engine
 * Copyright (C) 2026 KYAU Labs (https://kyaulabs.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KYAULabs;

/**
 * Class Aurora
 *
 * This class provides functionality for managing Aurora templates and CDN directories,
 * and rendering HTML content with CSS and JavaScript inclusion.
 */
class Aurora
{
    /** @var string AURORA_DIR The Aurora template directory */
    private const AURORA_DIR = __DIR__ . "/html";

    /** @var string $aurora_cdn The CDN directory path */
    private $aurora_cdn = "";
    /** @var string|null $templateDir Custom template directory (overlay) */
    private $templateDir = null;
    /** @var string $aurora_template The template file name */
    private $aurora_template = "";

    /** @var bool $status The status of the instance */
    private $status = true;
    /** @var bool $html Flag indicating if HTML output is enabled */
    private $html = false;

    /** @var array $css CSS files to include */
    private $css = [];
    /** @var array $dns DNS prefetch URLs */
    private $dns = [];
    /** @var array $js JavaScript files to include */
    private $js = [];
    /** @var array $js JavaScript modules to include */
    private $mjs = [];
    /** @var array $preload Preload resources */
    private $preload = [];
    /** @var bool $dnsEmitted Whether DNS prefetch tags have been emitted */
    private $dnsEmitted = false;
    /** @var array $vars Variables for template replacement */
    private $vars = [];
    /** @var array $vars_success Successfully replaced variables */
    private $vars_success = [];

    /**
     * Constructor for the Aurora class.
     *
     * @param string|null $template The template file name.
     * @param string|null $cdn The CDN directory path.
     * @param bool $status The status flag.
     * @param bool $html The HTML output flag.
     * @param string|null $templateDir Custom template directory (overlay: checked first, falls back to default).
     * @throws AuroraException If required parameters are null or invalid directories.
     */
    public function __construct(?string $template = null, ?string $cdn = '/cdn', bool $status = false, bool $html = false, ?string $templateDir = null)
    {
        // error handling
        set_exception_handler(['\KYAULabs\Aurora', 'exceptionHandler']);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('error_reporting', '-1');
        ini_set('html_errors', '1');

        // store custom template directory (overlay path)
        $this->templateDir = $templateDir;

        // check if any arguments are null
        if (count(array_filter([$template, $cdn])) == 1) {
            throw new AuroraException('Required parameter is null.', 'param', 1);
        } else {
            if (!file_exists($this->resolveTemplatePath($template))) {
                throw new AuroraException('Aurora HTML5 template not found.', 'html', 1);
            } else {
                $this->aurora_template = $template;
            }
        }
        // Resolve CDN directory relative to the calling script, not Aurora's own __DIR__.
        // debug_backtrace()[0] is safe because the constructor is always called directly.
        $orig_dir = __DIR__;
        $backtrace = debug_backtrace();
        if (isset($backtrace[0]['file'])) {
            $orig_dir = dirname($backtrace[0]['file']);
        }
        if (!is_dir($orig_dir . '/..' . $cdn)) {
            throw new AuroraException("Invalid directory: " . $orig_dir . '/' . $cdn, 'cdn', 1);
        } else {
            $this->aurora_cdn = $cdn;
        }

        // Enable unicode and set default timezone to UTC.
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
        $this->phpSet('default_charset', 'UTF-8');
        date_default_timezone_set('UTC');

        // Set the status and html output variables accordingly.
        $this->status = $status;
        ($html) ? $this->html = $html : '';

        // Set logging settings accordingly.
        if ($this->status) {
            $this->phpSet('display_errors', '1');
            $this->phpSet('display_startup_errors', '1');
            $this->phpSet('error_reporting', '-1');
            $this->phpSet('html_errors', '1');
        } else {
            $this->phpSet('display_errors', '0');
            $this->phpSet('display_startup_errors', '0');
            $this->phpSet('error_reporting', (string)E_ALL);
            $this->phpSet('html_errors', '0');
        }

        // HTML Mode
        if ($this->html) {
            if (function_exists('mb_http_output')) {
                mb_http_output('UTF-8');
            }
            header('Content-Type: text/html; charset=UTF-8');
        }
    }

    /**
     * Magic getter for accessing private properties.
     *
     * @param string $name The name of the property.
     * @return string|null The value of the property or null if not found.
     */
    public function __get(string $name): ?string
    {
        if (in_array($name, array('aurora_cdn', 'dns', 'preload', 'css', 'js', 'mjs', 'status', 'html'))) {
            if (!empty($this->$name)) {
                return $this->$name;
            }
        } else {
            if (array_key_exists($name, $this->vars)) {
                return $this->vars[$name];
            }
        }
        trigger_error("Error: unable to find variable '{$name}'", E_USER_WARNING);
        return null;
    }

    /**
     * Magic setter for setting private properties.
     *
     * @param string $name The name of the property.
     * @param mixed $value The value to set.
     */
    public function __set(string $name, $value): void
    {
        if (in_array($name, array('dns', 'preload', 'css', 'js', 'mjs'))) {
            if (!is_array($this->$name) || !count($this->$name)) {
                $this->$name = $value;
            } else {
                $this->$name = array_merge($this->$name, $value);
            }
            return;
        } else {
            $this->vars[$name] = $value;
            return;
        }
    }

    /**
     * Generate CSS <link/> tags.
     *
     * @return string|null The HTML string of styles.
     * @throws AuroraException If the CSS file or its hash does not exist.
     */
    private function htmlStyles(): ?string
    {
        $str = "";
        if (!empty($this->css)) {
            $str .= "\n";
            foreach ($this->css as $path => $url) {
                $hash = hash_file('sha512', $path);
                if ($hash === false) {
                    throw new AuroraException("{$path} hash computation failed.", 'styles', 1);
                }
                $sha512 = base64_encode(hex2bin($hash));
                $ver = hexdec(substr($hash, 0, 8));

                $str .= sprintf("\t<link rel=\"stylesheet\" type=\"text/css\" href=\"%s?v=%s\"\n", $url, $ver);
                $str .= sprintf("\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\" />\n", $sha512);
            }
        }
        return $str;
    }

    /**
     * Generate preload <link/> tags.
     *
     * @return string|null The HTML string of preload links.
     * @throws AuroraException If the preload file or its hash does not exist.
     */
    private function htmlPreload(): ?string
    {
        $str = "";
        if (!empty($this->preload)) {
            $str .= "\n";
            if (!$this->dnsEmitted && count($this->dns)) {
                foreach ($this->dns as $url) {
                    $str .= sprintf("\t<link rel=\"dns-prefetch\" href=\"//%s\" />\n", $url);
                    $str .= sprintf("\t<link rel=\"preconnect\" href=\"//%s\" crossorigin=\"anonymous\" />\n", $url);
                }
                $str .= "\n";
                $this->dnsEmitted = true;
            }
            foreach ($this->preload as $url => $type) {
                if (in_array($type, array("script", "style"))) {
                    $path = '..' . $this->aurora_cdn . $url;
                    if (!file_exists($path)) {
                        throw new AuroraException("{$path} does not exist.", 'preload', 1);
                    }

                    $hash = hash_file('sha512', $path);
                    if ($hash === false) {
                        throw new AuroraException("{$path} hash computation failed.", 'preload', 1);
                    }
                    $sha512 = base64_encode(hex2bin($hash));
                    $ver = hexdec(substr($hash, 0, 8));

                    if (!isset($this->dns) || empty($this->dns)) {
                        throw new AuroraException("DNS prefetch not found!", 'dns', 1);
                    }
                    $str .= sprintf("\t<link rel=\"preload\" href=\"%s?v=%s\" as=\"%s\"\n\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\" />\n", ("//" . $this->dns[0] . trim($url)), $ver, strtolower(trim($type)), $sha512);
                } else {
                    $str .= sprintf("\t<link rel=\"preload\" href=\"%s\" as=\"%s\" crossorigin=\"anonymous\" />\n", ("//" . $this->dns[0] . trim($url)), strtolower(trim($type)));
                }
            }
        }
        return $str;
    }

    /**
     * Generate JavaScript <script/> tags.
     *
     * @return string|null The HTML string of script tags.
     * @throws AuroraException If the JS file or its hash does not exist.
     */
    private function htmlScripts(): ?string
    {
        $ext = 1;
        $str = "";
        if (!empty($this->mjs)) {
            $str .= "\n";
            foreach ($this->mjs as $path => $url) {
                if ($path == "<external>") {
                    $str .= sprintf("\t<script src=\"%s\" type=\"module\" id=\"ext%d\"></script>\n", $url, $ext);
                    $ext++;
                } else {
                    if (!file_exists($path)) {
                        throw new AuroraException("{$url} does not exist.", 'scripts', 1);
                    }
                    $hash = hash_file('sha512', $path);
                    if ($hash === false) {
                        throw new AuroraException("{$url} hash computation failed.", 'scripts', 1);
                    }
                    $sha512 = base64_encode(hex2bin($hash));
                    $ver = hexdec(substr($hash, 0, 8));
                    $str .= sprintf("\t<script src=\"%s?v=%s\" type=\"module\" defer=\"defer\"\n", $url, $ver);
                    $str .= sprintf("\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\"></script>\n", $sha512);
                }
            }
        }
        if (!empty($this->js)) {
            $str .= "\n";
            foreach ($this->js as $path => $url) {
                if ($path == "<external>") {
                    $str .= sprintf("\t<script src=\"%s\" id=\"ext%d\" async defer></script>\n", $url, $ext);
                    $ext++;
                } else {
                    if (!file_exists($path)) {
                        throw new AuroraException("{$url} does not exist.", 'scripts', 1);
                    }
                    $hash = hash_file('sha512', $path);
                    if ($hash === false) {
                        throw new AuroraException("{$url} hash computation failed.", 'scripts', 1);
                    }
                    $sha512 = base64_encode(hex2bin($hash));
                    $ver = hexdec(substr($hash, 0, 8));
                    $str .= sprintf("\t<script src=\"%s?v=%s\" defer=\"defer\"\n", $url, $ver);
                    $str .= sprintf("\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\"></script>\n", $sha512);
                }
            }
        }
        return $str;
    }

    /**
     * Replace variables in a template line.
     *
     * @param string $line The line from the template.
     * @return string The line with variables replaced.
     */
    private function replace(string $line): string
    {
        foreach ($this->vars as $key => $value) {
            $reg = '/{{[\s\S](' . $key . ')[\s\S]}}/';
            if (preg_match($reg, $line)) {
                //echo "key:{$key} value:{$value}\n";
                $this->vars_success[] = $key;
                return preg_replace($reg, $value, $line);
            }
        }
        $reg = '/{%[\s\S](css\(\))[\s\S]%}\n/';
        if (preg_match($reg, $line)) {
            $this->vars_success[] = "~css";
            return preg_replace($reg, $this->htmlStyles(), $line);
        }
        $reg = '/{%[\s\S](preload\(\))[\s\S]%}\n/';
        if (preg_match($reg, $line)) {
            $this->vars_success[] = "~preload";
            return preg_replace($reg, $this->htmlPreload(), $line);
        }
        return $line;
    }

    /**
     * Resolve the template path with overlay support.
     *
     * Checks the custom $templateDir first; if the file exists there, uses it.
     * Otherwise falls back to the default AURORA_DIR.
     *
     * @param string $template The template file name.
     * @return string The resolved full path to the template file.
     */
    private function resolveTemplatePath(string $template): string
    {
        if ($this->templateDir !== null && file_exists($this->templateDir . '/' . $template)) {
            return $this->templateDir . '/' . $template;
        }
        return self::AURORA_DIR . '/' . $template;
    }

    /**
     * Render the template file.
     *
     * @return bool True on success, false on failure.
     */
    private function render(): bool
    {
        $fd = @fopen($this->resolveTemplatePath($this->aurora_template), 'r');
        if ($fd) {
            while (($buffer = fgets($fd, 4096)) !== false) {
                printf("%s", $this->replace($buffer));
            }
            if (!feof($fd)) {
                echo "Error: unexpected fgets() failure.\n";
                return false;
            }
        } else {
            echo "Error: unexpected fopen() failure.\n";
            return false;
        }
        fclose($fd);
        return true;
    }

    /**
     * Set a PHP configuration option.
     *
     * @param string $setting The configuration option.
     * @param string $value The value to set.
     * @return bool True on success, false on failure.
     */
    private function phpSet(string $setting, string $value): bool
    {
        if (ini_set($setting, $value) === false) {
            echo "Error: could not set {$setting} to {$value}, please ensure it's set on your system!\n";
            return false;
        }
        return true;
    }

    /**
     * Get the project version from the specified file.
     *
     * @param string $project_file The project file.
     * @param bool $hash Whether to include an MD5 hash fragment.
     * @return string|null The project version or null on failure.
     */
    private function projectVersion(string $project_file, bool $hash = false): ?string
    {
        if (file_exists($project_file)) {
            $fd = @fopen($project_file, 'r');
            if ($fd) {
                while (($buffer = fgets($fd, 4096)) !== false) {
                    if (
                        substr($buffer, 0, 13) == ' * $KYAULabs:' ||
                        substr($buffer, 0, 12) == '# $KYAULabs:' ||
                        substr($buffer, 0, 13) == '// $KYAULabs:' ||
                        substr($buffer, 0, 13) == '/* $KYAULabs:'
                    ) {
                        $str = explode(' ', $buffer);
                        fclose($fd);
                        $file = str_replace('.php', '.html', strtolower(basename($project_file)));
                        if ($hash) {
                            $hash = substr(md5($str[5] . $str[6]), 0, 8);
                            return $str[2] . ' ' . $file . ',v ' . $str[4] . '-' . $hash;
                        } else {
                            return 'v' . $str[4];
                        }
                    }
                }
                if (!feof($fd)) {
                    echo "Error: unexpected fgets() fail\n";
                }
            } else {
                echo "Error: unexpected fopen() fail\n";
            }
            fclose($fd);
        } else {
            printf("Error: file '%s' does not exist\n", $project_file);
        }
        return null;
    }

    /**
     * Get the runtime of a specific process.
     *
     * @param array $ru The current resource usage.
     * @param array $rus The previous resource usage.
     * @param string $index The index to calculate.
     * @return int The runtime in milliseconds.
     */
    private function rutime(array $ru, array $rus, string $index): int
    {
        return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
            - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
    }

    /**
     * Render the time taken for rendering.
     *
     * @param array $ru The current resource usage.
     * @param array $rus The previous resource usage.
     * @return string The formatted render time.
     */
    private function renderTime(array $ru, array $rus): string
    {
        return sprintf("compute:%dms  syscall:%dms", self::rutime($ru, $rus, "utime"), self::rutime($ru, $rus, "stime"));
    }

    /**
     * Generate an HTML comment with project version and render time.
     *
     * @param array $rus The previous resource usage.
     * @param string $script The script file.
     * @param bool $vim Flag to include vim settings.
     * @return string The generated HTML comment.
     */
    public function comment(array $rus, string $script, bool $vim = false): string
    {
        $time = sprintf("%s", self::renderTime($rus, getrusage()));
        $version = self::projectVersion($script, true);
        return sprintf("\n<!--\n\t%s  %s\n%s-->", $version, $time, ($vim ? "\tvim: ft=html sts=4 sw=4 ts=4 noet:\n" : ''));
    }

    /**
     * Get the project version for a specific script.
     *
     * @param string $script
     * @return string|null
     */
    public function version(string $script): ?string
    {
        return self::projectVersion($script);
    }

    /**
     * Render the HTML header.
     *
     * @return bool True on success, false on failure.
     */
    public function htmlHeader(): bool
    {
        if (!isset($this->aurora_template) or empty($this->vars)) {
            echo "Error: template file and/or variables not set.\n";
            return false;
        }
        if (!$this->render()) {
            echo "Error: template rendering has failed.\n";
            return false;
        }
        return true;
    }

    /**
     * Render the HTML footer.
     *
     * @return bool True on success, false on failure.
     */
    public function htmlFooter(): bool
    {
        if (!empty($this->js) || !empty($this->mjs)) {
            printf("%s", $this->htmlScripts());
        }
        printf("\n</body>\n</html>");
        return true;
    }

    /**
     * Test the replaced variables and generate a report.
     *
     * @return string The report of replaced variables.
     */
    public function testVariables(): string
    {
        $str = "\nVariables Replaced:<br/>\n";
        foreach ($this->vars_success as $value) {
            if (substr($value, 0, 1) === "~") {
                $str .= sprintf("&#x2714; %s: array(data)<br/>\n", substr($value, 1));
            } else {
                if (!array_key_exists($value, $this->vars)) {
                    $str .= sprintf("&#x2715; %s: success case but no variable?!<br/>\n", $value);
                } else {
                    $str .= sprintf("&#x2714; %s: %s<br/>\n", $value, $this->vars[$value]);
                }
            }
        }
        return $str;
    }

    /**
     * Exception handler for the Aurora class.
     *
     * @param \Throwable $exception The exception to handle.
     */
    public static function exceptionHandler(\Throwable $exception): void
    {
        if (intval(ini_get('display_errors')) === 1) {
            if ($exception->getCode() === 1) {
                echo "</head>\n\n<body>\n\n";
            }
            echo $exception;
            echo "\n</body>\n</html>";
        } else {
            error_log((string)$exception);
        }
    }
}

/**
 * Class AuroraException
 *
 * This class extends the built-in Exception class to provide custom functionality
 * for handling exceptions in the Aurora application.
 */
class AuroraException extends \Exception
{
    /**
     * @var string $type Project base directory.
     */
    private $type = "";

    /**
     * AuroraException constructor.
     *
     * @param string $message The exception message.
     * @param string $type The exception type (e.g. 'param', 'html', 'cdn', 'styles', 'preload', 'scripts', 'dns').
     * @param int $code The exception code (default 0).
     */
    public function __construct(string $message, string $type, int $code = 0)
    {
        // set exception type
        $this->type = $type;
        // call the parent constructor with the provided message and code
        parent::__construct($message, $code);
    }

    /**
     * Convert the exception to a string.
     *
     * @return string A string representation of the exception including the code and message
     */
    public function __toString(): string
    {
        // return a formatted string with the exception code and message
        return "\t<h3>Aurora - Warning!</h3>\n\tCode: <tt>" . $this->getCode() . "</tt><br/>\n\tType: <tt>" . $this->getType() . "</tt><br/>\n\tMessage: <tt>" . htmlentities($this->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</tt>\n";
    }

    /**
     * Get the exception type.
     *
     * @return string The current exception type
     */
    public function getType(): string
    {
        return $this->type;
    }

}


/**
 * vim: ft=php sts=4 sw=4 ts=4 et:
 */
