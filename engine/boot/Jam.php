<?php

use jam\engine\core\Request;
use jam\engine\core\Response;

use jam\engine\core\Database;

class Jam {
    static $db = [];
    static $config = null;
    private $dotenv = null;
    private $rootDir;

    /**
     * Current request
     * @var Request
     */
    private $_request = null;

    /**
     * Current response
     * @var Response
     */
    private $_response = null;

    function __construct($rootDir) {
        $this->rootDir = $rootDir;
    }

    /**
     * @return string
     */
    function getRootDir() {
        return $this->rootDir;
    }

    public function request (?Request $r = null) {
        if ($r)
            $this->_request = $r;
        if (!$this->_request)
            $this->_request = new Request();
        return $this->_request;
    }

    /**
     * @param Response|null $r
     * @return Response
     */
    public function response (?Response $r = null) {
        if ($r)
            $this->_response = $r;
        if (!$this->_response)
            $this->_response = new Response();
        return $this->_response;
    }

    function config($key, $default = []) {
        if (is_null(self::$config)) {
            self::$config = include($this->rootDir . "/config.php");
        }
        $config = self::$config;
        $keyComponents = explode(".", $key);
        $configFound = true;
        while ($keyComponents) {
            $section = array_shift($keyComponents);
            if ($configFound) {
                if (isset($config[$section])) {
                    $config = $config[$section];
                } else {
                    $configFound = false;
                    break;
                }
            }
        }
        if (!$configFound) {
            $config = $default;
        }
        return $config;
    }

    function db($name = "master") {
        if (empty(self::$db[$name])) {
            self::$db[$name] = new Database(self::config('db.'.$name), $name);
        }
        return self::$db[$name];
    }

    public function env ($name, $default = '') {
        if (empty($this->dotenv)) {
            $envFile = $this->rootDir . '/.env';
            if (is_file($envFile)) {
                $this->dotenv = parse_ini_file($envFile);
            }
            if (empty($this->dotenv)) {
                $this->dotenv = ['' => ''];
            }
        }
        return isset($this->dotenv[$name]) ? $this->dotenv[$name] : $default;
    }
}
