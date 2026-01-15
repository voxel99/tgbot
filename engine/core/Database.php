<?php

namespace jam\engine\core;

use Exception;
use jam\engine\core\DbSimple\Adapter\Mypdo;
use PDOException;

/**
 * Подключение к БД
 */
class Database {
    /** @var Mypdo */
    private $db = null;
    private $name = '';
    private $_info = '';

    public function __construct (string $dsn, string $name) {
        $this->name = $name;
        if (!$dsn)
            throw new \Exception("DSN не задан для подключения " . $name);
        if (!is_string($dsn))
            throw new \Exception("DSN должна быть строкой, задан " . gettype($dsn));
        try {
            $this->db = new DbSimple\Connect($dsn);
            $this->db->setErrorHandler([&$this, "errorHandler"]);
            $this->db->setLogger([&$this, "info"]);
        } catch (PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Установить режим отладки
     *
     * @param int $debug
     *      0 - отключён
     *      1 - включён вывод только первого sql-запроса (die после)
     *      2 - включён вывод в stdout всех sql запросов
     *      3 - возврат строки в виде исключения
     *      4 - включён вывод в stdout всех sql запросов + trace точки вызова
     *      5 - включён вывод в stdout всех sql запросов без их выполнения
     * @return int old debug mode
     */
    public function debug (int $debug = 1): int {
        return $this->db->debugSql($debug);
    }

    public function isDebug (int $debug = 1): bool {
        return $this->db->isDebugSql($debug);
    }

    public function getName (): string {
        return $this->name;
    }

    public function info (mixed $pdo, mixed $query, mixed $trace): void {
        $this->_info .= $query;
    }

    public function __call (string $name, array $args): mixed {
        $this->_info = "";
        if ($name == "errorHandler")
            $stat = call_user_func_array(array($this, $name), $args);
        else
            $stat = call_user_func_array(array($this->db, $name), $args);
        return $stat;
    }

    protected function errorHandler (string $msg, array $info): void {
        $msg = iconv('cp1251', 'utf-8//IGNORE', $msg);
        if (isset($info['message'])) {
            $info['message'] = iconv('cp1251', 'utf-8//IGNORE', $info['message']);
            throw new \Exception($msg . "\n" . print_r($info, true));
        }
    }
}