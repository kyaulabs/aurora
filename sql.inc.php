<?php

/**
 * $KYAULabs: sql.inc.php,v 1.0.4 2024/07/09 04:45:30 -0700 kyau Exp $
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
 * SQL Handler
 *
 * Aurora is built for rapid deployment of Ajax loaded content pages. It
 * is an attempt at using the most up-to-date HTML5 spec / web standards in
 * use today.
 */
class SQLHandler
{
    public const INTERNAL_HANDLING = 1;
    public const THROW_EXCEPTION = 2;
    public const IGNORE_ERRORS = 3;

    private const SQL_HOST = '127.0.0.1';
    private const SQL_PORT = 3306;

    public $pdo = null;
    public $result = null;

    private $db = null;
    private $err = self::INTERNAL_HANDLING;

    /*
        * Create a new database handler object to interact with the database.
        * @param string $user Username to connect with.
        * @param string $passwd Password associated with the username.
        * @param string $db The database to connect to. This is required.
        * @param array $options PDO connection options array. This is optional and will override defaults.
        */
    public function __construct(string $db = null, $options = [])
    {
        $user = "";
        $passwd = "";
        include_once(__DIR__ . '/settings.inc.php');
        if ($db == null) {
            throw new \Exception('Required parameter is null.');
            return 0;
        }
        if (! defined('SQL_USER')) {
            throw new \Exception('No settings.inc.php exists.');
            return 0;
        } else {
            $user = SQL_USER;
            $passwd = SQL_PASSWD;
        }
        $defaults = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($defaults, $options);
        $this->db = $db;
        $dsn = "mysql:host=" . $this::SQL_HOST . ";dbname=" . $this->db . ";port" . $this::SQL_PORT . ";charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $user, $passwd, $options);
        } catch (\PDOException $e) {
            $this->process_exception($e);
        }
    }

    /*
        * Change the current database being accessed
        * @param string $name The name of the database to connect to.
        * @return boolean Whether or not the database change succeeded.
        */
    public function setDatabase($name)
    {
        $this->db = $name;
        return ($this->query("USE ?", array($name))) ? true : false;
    }

    /*
        * Process a query and get the PDOStatement object back
        * @param string $sql The query to process. Use :paramname for named parameters and ? for anonymous parameters.
        * @param array $args The parameters array, the one attached to execute()
        * @return PDOStatement|boolean The PDOStatement object retreived from the database or false if something failed.
        */
    public function query($sql, array $args = array())
    {
        try {
            if ($this->pdo !== null) {
                if (($this->result = $this->pdo->prepare($sql)) !== false) {
                    if ($this->result->execute($args)) {
                        return $this->result;
                    }
                }
            }
            return false;
        } catch (PDOException $e) {
            $this->procException($e);
        }
    }

    /*
        * Process exceptions based on the err setting
        * @param PDOException $e The exception to process
        * @throws PDOException If err is set to sql_handler::THROW_EXCEPTION
        */
    private function procException(PDOException &$e)
    {
        switch ($this->err) {
            case self::INTERNAL_HANDLING:
                self::handleException($e);
                break;
            case self::THROW_EXCEPTION:
                throw $e;
                break;
            default:
                break;
        }
    }

    /*
        * Handle a PDOException by showing the stacktrace properly and dieing.
        * @param PDOException $e The exception to handle
        */
    public static function handleException(PDOException &$e)
    {
        $trace = $e->getTrace();
        $msg = "<span class=\"error\"><strong>An error has occurred: </strong><br/>\n<pre>";
        $msg .= $e->getMessage() . "<br/>\n";
        foreach ($trace as $line) {
            $msg .= "\t<strong>at</strong> ";
            if (!empty($line['class'])) {
                $msg .= $line['class'];
                $msg .= $line['type'];
            }
            $msg .= $line['function'] . "()";
            $path = $line['file'] . " (<strong>line</strong> " . $line['line'] . ")<br/>";
            $msg .= " <strong>in</strong> " . str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);
        }
        $msg .= "</pre></span>";
        die($msg);
    }
}

/*
 * Attribution
 *
 * https://phpdelusions.net/pdo/pdo_wrapper
 * https://gist.github.com/Erackron/5786072
 * https://github.com/infolock/DBHandler/blob/master/Handler.php
 */

/**
 * vim: ft=php sts=4 sw=4 ts=4 et:
 */
