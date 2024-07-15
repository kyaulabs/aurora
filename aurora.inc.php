<?php

/**
 * $KYAULabs: aurora.inc.php,v 1.0.3 2024/07/09 04:35:51 -0700 kyau Exp $
 * ▄▄▄▄ ▄▄▄▄ ▄▄▄▄▄▄▄▄▄ ▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄
 * █ ▄▄ ▄ ▄▄ ▄ ▄▄▄▄ ▄▄ ▄    ▄▄   ▄▄▄▄ ▄▄▄▄  ▄▄▄ ▀
 * █ ██ █ ██ █ ██ █ ██ █    ██   ██ █ ██ █ ██▀  █
 * ■ ██▄▀ ██▄█ ██▄█ ██ █ ▀▀ ██   ██▄█ ██▄▀ ▀██▄ ■
 * █ ██ █ ▄▄ █ ██ █ ██ █    ██▄▄ ██ █ ██ █  ▄██ █
 * ▄ ▀▀ ▀ ▀▀▀▀ ▀▀ ▀ ▀▀▀▀    ▀▀▀▀ ▀▀ ▀ ▀▀▀▀ ▀▀▀  █
 * ▀▀▀▀▀▀▀▀▀▀▀▀▀▀ ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀ ▀▀▀▀▀▀▀▀▀▀▀▀▀
 *
 * Aurora HTML5 Template Engine
 * Copyright (C) 2024 KYAU Labs (https://kyaulabs.com)
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
 * Aurora HTML5 Template Engine
 *
 * Aurora is built for rapid deployment of Ajax loaded content pages. It
 * is an attempt at using the most up-to-date HTML5 spec / web standards in
 * use today.
 */
class Aurora
{
    /**
     * @var string AURORA_ROOT Aurora's base directory on the filesystem.
     * @var string AURORA_DIR The location of the Aurora template directory.
     */
    private const AURORA_ROOT = __DIR__;
    private const AURORA_DIR = __DIR__ . "/html";

    /**
     * @var string $aurora_base Project base directory.
     * @var string $aurora_template Aurora HTML5 Template to load.
     * @var string $aurora_url Project base url.
     * @var string $session_name Name of the session which is used as cookie name.
     */
    private $aurora_base = "";
    private $aurora_template = "";
    private $aurora_url = "";
    private $session_name = "KYAULabs";

    /**
     * @var bool $status When true development mode is enabled, production mode
     *                   when set to false.
     * @var bool $sessions When true sessions are enabled before headers are sent.
     * @var bool $html When true send an html header before content.
     */
    private $status = true;
    private $sessions = false;
    private $html = false;

    /**
     * @var string[] $api API URLs used by the project.
     * @var string[] $css Stylesheet files for the current page, eg. path => url.
     * @var string[] $js JavaScript files for the current page, eg. path => url.
     * @var string[] $preload Files to preload with the page source, eg. url => type.
     * @var string[] $vars Variables to replace inside the template.
     * @var string[] $vars_success Replacements that went successfully.
     */
    private $api = [];
    private $css = [];
    private $js = [];
    private $preload = [];
    private $vars = [];
    private $vars_success = [];

    /**
     * @param string $template Filename for the web template to execute.
     * @param string $base Full path of the current website api.
     * @param string $url Full url for the current website api.
     * @param bool $status Run aurora in development/production (true/false) mode.
     * @param bool $html Whether or not to output in HTML mode.
     *
     * @return bool Return true if success.
     */
    public function __construct(string $template = null, string $base = null, string $url = null, bool $status = false, bool $html = false)
    {
        // throw an Exception if $template, $base or $url is null
        if (count(array_filter(array($template, $base, $url))) == 1) {
            throw new \Exception('Required parameter is null.');
            return 0;
        } else {
            if (!file_exists(self::AURORA_DIR . "/{$template}")) {
                throw new \Exception('Aurora HTML5 template not found.');
                return 0;
            } else {
                $this->aurora_template = $template;
            }
        }
        // throw an Exception if base directory does not exist
        if (!is_dir($base)) {
            throw new \Exception("Invalid directory: {$base}");
            return 0;
        } else {
            $this->aurora_base = $base;
            $this->aurora_url = $url;
        }

        // Enable secure sessions
        if ($this->sessions) {
            $this->phpSet('session.use_strict_mode', '1');
            session_start([
                'cookie_domain' => $_SERVER['HTTP_HOST'],
                'cookie_httponly' => 1,
                'cookie_lifetime' => 86400 * 30,
                'cookie_samesite' => 'Strict',
                'cookie_secure' => 1,
                'session_name' => $this->session_name,
            ]);
            // Do not allow expired sessions
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
                // last request was more than 30 minutes ago
                session_unset();
                session_destroy();
                session_start();
            }
            $this->sessionRegenerateID();
        }

        // Enable unicode and set default timezone to UTC.
        mb_internal_encoding('UTF-8');
        $this->phpSet('default_charset', 'UTF-8');
        date_default_timezone_set('UTC');

        // Set the status and html output variables accordingly.
        ($status) ? '' : $this->status = $status;
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
            $this->phpSet('error_reporting', 'E_ALL');
            $this->phpSet('html_errors', '0');
        }

        // HTML Mode
        if ($this->html) {
            mb_http_output('UTF-8');
            header('Content-Type: text/html; charset=UTF-8');
        }
    }

    /**
     * @param string $name Variable to look for.
     *
     * @return bool|string Return false or the string value of the variable.
     */
    public function __get(string $name)
    {
        if (in_array($name, array('aurora_base', 'aurora_url', 'api', 'preload', 'css', 'js', 'sessions', 'status', 'html'))) {
            if (!empty($this->$name)) {
                return $this->$name;
            }
        } else {
            if (array_key_exists($name, $this->vars)) {
                return $this->vars[$name];
            }
        }
        echo "Error: unable to find variable '{$name}'\n";
        return 0;
    }

    /**
     * @param string $name Variable to set.
     */
    public function __set(string $name, $value)
    {
        if (in_array($name, array('api', 'preload', 'css', 'js', 'sessions'))) {
            if (!is_array($name) || !count($this->$name)) {
                $this->$name = $value;
            } else {
                $this->$name = array_merge($this->$name, $value);
            }
            return 1;
        } else {
            $this->vars[$name] = $value;
            return 1;
        }
        echo "Error: unable to set '{$name}' variable to '{$value}'.\n";
        return 0;
    }

    /**
     * Process stylesheets and return a string to insert into <head>.
     *
     * @return string|bool Return stylesheet string for insertion into <head>
     *                     or throw exception and return false.
     */
    private function htmlStyles()
    {
        $str = "";
        if (!empty($this->css)) {
            $str .= "\n";
            foreach ($this->css as $path => $url) {
                if (!file_exists($path) or !file_exists($path . '.sha512')) {
                    throw new \Exception("{$path}.sha384 does not exist.");
                }
                $sha512 = trim(file_get_contents($path . '.sha512'));
                if (!empty($sha512)) {
                    $str .= sprintf("\t<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\"\n", $url);
                    $str .= sprintf("\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\" />\n", $sha512);
                }
            }
        }
        return $str;
    }

    /**
     * Process all preloaded files and create string to insert into <head>.
     *
     * @return string Return preload string for insertion into <head>.
     */
    private function htmlPreload()
    {
        $str = "";
        if (!empty($this->preload)) {
            $str .= "\n";
            if (count($this->api)) {
                foreach ($this->api as $url) {
                    // add dns prefetch
                    $str .= sprintf("\t<link rel=\"dns-prefetch\" href=\"//%s\" />\n", $url);
                    $str .= sprintf("\t<link rel=\"preconnect\" href=\"//%s\" crossorigin />\n", $url);
                }
                $str .= "\n";
            }
            foreach ($this->preload as $url => $type) {
                if (in_array($type, array("script", "style"))) {
                    $path = "";
                    foreach ($this->css as $cpath => $curl) {
                        if (stristr($curl, $url) !== false) {
                            $path = $cpath;
                        }
                    }
                    foreach ($this->js as $cpath => $curl) {
                        if (stristr($curl, $url) !== false) {
                            $path = $cpath;
                        }
                    }
                    if (!file_exists($path) or !file_exists($path . '.sha512')) {
                        throw new \Exception("{$path}.sha384 does not exist.");
                    }
                    $sha512 = trim(file_get_contents($path . '.sha512'));
                    if (!empty($sha512)) {
                        $str .= sprintf("\t<link rel=\"preload\" href=\"%s\" as=\"%s\"\n\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\" />\n", ("//" . $this->api[0] . trim($url)), strtolower(trim($type)), $sha512);
                    }
                } else {
                    $str .= sprintf("\t<link rel=\"preload\" href=\"%s\" as=\"%s\" crossorigin />\n", ("//" . $this->api[0] . trim($url)), strtolower(trim($type)));
                }
            }
        }
        return $str;
    }

    /**
     * Process all javascript files and create string to place at the end of
     * <body>.
     *
     * @return string Return script string for insertion into <body> or
     *                throw exception and return false.
     */
    private function htmlScripts()
    {
        $str = "";
        if (!empty($this->js)) {
            $str .= "\n";
            foreach ($this->js as $path => $url) {
                if (!file_exists($path) or !file_exists($path . '.sha512')) {
                    throw new \Exception("{$path}.sha512 does not exist.");
                    return 0;
                }
                $sha512 = trim(file_get_contents($path . '.sha512'));
                if (!empty($sha512)) {
                    $str .= sprintf("\t<script src=\"%s\" defer=\"defer\"\n", $url);
                    $str .= sprintf("\t\tintegrity=\"sha512-%s\"\n\t\tcrossorigin=\"anonymous\"></script>\n", $sha512);
                }
            }
        }
        return $str;
    }

    /**
     * Replace variables and/or functions with cooresponding data.
     *
     * @param string $line Line of text to be analyzed and regexp strings applied.
     *
     * @return string The template line complete with replacements.
     */
    private function replace(string $line)
    {
        foreach ($this->vars as $key => $value) {
            $reg = '/{{[\s|\S](' . $key . ')[\s|\S]}}/';
            if (preg_match($reg, $line)) {
                //echo "key:{$key} value:{$value}\n";
                $this->vars_success[] = $key;
                return preg_replace($reg, $value, $line);
            }
        }
        $reg = '/{%[\s|\S](css\(\))[\s|\S]}\n/';
        if (preg_match($reg, $line)) {
            $this->vars_success[] = "~css";
            return preg_replace($reg, $this->htmlStyles(), $line);
        }
        $reg = '/{%[\s|\S](preload\(\))[\s|\S]}\n/';
        if (preg_match($reg, $line)) {
            $this->vars_success[] = "~preload";
            return preg_replace($reg, $this->htmlPreload(), $line);
        }
        return $line;
    }

    /**
     * Render the Aurora HTML5 template specified, replacing all variables
     * accordingly, and then outputing the code.
     *
     * @return bool True on success and false upon any failure.
     */
    private function render()
    {
        $fd = @fopen(self::AURORA_DIR . "/" . $this->aurora_template, 'r');
        if ($fd) {
            while (($buffer = fgets($fd, 4096)) !== false) {
                printf("%s", $this->replace($buffer));
            }
            if (!feof($fd)) {
                echo "Error: unexpected fgets() failure.\n";
                return 0;
            }
        } else {
            echo "Error: unexpected fopen() failure.\n";
            return 0;
        }
        fclose($fd);
        return 1;
    }

    /**
     * Sets the value of a php configuration option.
     *
     * @param string $setting Configuration option to change.
     * @param string $value New value for the option.
     *
     * @return bool True on success and false upon any failure.
     */
    private function phpSet($setting, $value)
    {
        if (ini_set($setting, $value) === false) {
            echo "Error: could not set {$setting} to {$value}, please ensure it's set on your system!\n";
            return 0;
        }
        return 1;
    }

    /**
     * Pull the version string from the top of the main project file.
     *
     * @param string $project_file The main project file.
     *
     * @return string The version string or error message.
     */
    private function projectVersion(string $project_file)
    {
        if (file_exists($project_file)) {
            $line = 1;
            $fd = @fopen($project_file, 'r');
            if ($fd) {
                while (($buffer = fgets($fd, 4096)) !== false) {
                    if (substr($buffer, 0, 13) == ' * $KYAULabs:') {
                        $str = explode(' ', $buffer);
                        $hash = substr(md5($str[5] . $str[6]), 0, 8);
                        fclose($fd);
                        $file = str_replace('.php', '.html', strtolower(basename($project_file)));
                        return $str[2] . ' ' . $file . ',v ' . $str[4] . '-' . $hash;
                    }
                    $line++;
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
        return 0;
    }

    /**
     * Calculate runtime formula (compute/syscall).
     *
     * @param array $ru Resource usages at the end of script run.
     * @param array $rus Resource usages at start of script.
     * @param string $index Time used in microseconds (utime/stime), user/system.
     *
     * @return int Runtime in milliseconds.
     */
    private function rutime(array $ru, array $rus, string $index)
    {
        return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
            - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
    }

    /**
     * Put the render time together in a formatted string.
     *
     * @param array $ru Resource usages at the end of script run.
     * @param array $rus Resource usages at the start of script.
     *
     * @return string Page render time.
     */
    private function renderTime(array $ru, array $rus)
    {
        $ret = sprintf("compute:%dms  syscall:%dms", self::rutime($ru, $rus, "utime"), self::rutime($ru, $rus, "stime"));
        return $ret;
    }

    /**
     * Return the page render time.
     *
     * @param array $rus Resource usages at the start of script.
     * @param string $script Filename for currently running script.
     * @param boolean $vim Whether or not to add a Vim modeline.
     *
     * @return string Page footer comment.
     */
    public function comment(array $rus, string $script, bool $vim = false)
    {
        $time = sprintf("%s", self::renderTime($rus, getrusage()));
        $version = self::projectVersion($script);
        $ret = sprintf("\n<!--\n\t%s  %s\n%s-->", $version, $time, ($vim ? "\tvim: ft=html sts=4 sw=4 ts=4 noet:\n" : ''));
        return $ret;
    }

    /**
     * Using the template and all set variables output the page header from
     * html tag to body tag.
     *
     * @return bool True on success and false upon any failure.
     */
    public function htmlHeader()
    {
        if (!isset($this->aurora_template) or empty($this->vars)) {
            echo "Error: template file and/or variables not set.\n";
            return 0;
        }
        if (!$this->render()) {
            echo "Error: template rendering has failed.\n";
            return 0;
        }
        return 1;
    }

    /**
     * Utilizing the javascript variable output all script tags and then close
     * out the body and html tags.
     *
     * @return bool True on success and false upon any failure.
     */
    public function htmlFooter()
    {
        if (!empty($this->js)) {
            printf("%s", $this->htmlScripts());
        }
        printf("\n</body>\n</html>");
        return 1;
    }

    /**
     * Enable sessions, optionally setting a session name for cookies.
     *
     * @return bool True on success and false upon any failure.
     */
    public function enableSessions(string $session_name = "")
    {
        if ($session_name != "") {
            $this->session_name = $session_name;
        }
        $this->sessions = true;
        return 1;
    }

    /**
     * Create a new collision free session id.
     *
     * @return bool True on success and false upon any failure.
     */
    public function sessionRegenerateID()
    {
        // ensure sessions are active
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        $newid = session_create_id(strtolower($this->session_name) . '-');
        $_SESSION['last_activity'] = time();
        session_commit();

        // Make sure to accept user defined session id.
        // NOTE: You must enable use_strict_mode for normal operations.
        $this->phpSet('session.use_strict_mode', '0');
        session_id($newid);
        session_start();

        return 1;
    }

    /**
     *
     */
    public function testVariables()
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
}


/**
 * vim: ft=php sts=4 sw=4 ts=4 et:
 */
