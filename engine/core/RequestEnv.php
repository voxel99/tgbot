<?php

namespace jam\engine\core;

use function config;

/**
 * Окружение запроса
 */
class RequestEnv
{
    public function __construct() {}

    /**
     * ПолучитьIP адрес запроса
     */
    public function getIp () {
        $ip = false;
        // Заголовки в порядке приоритета получения IP адреса клиента
        $headers = ["X-REAL-IP", "HTTP_X_REAL_IP", "REMOTE_ADDR"];
        foreach ($headers as $env) {
            $ip = getenv($env);
            if (!$ip && isset($_SERVER[$env])) {
                $ip = $_SERVER[$env];
            }
            if ($ip) {
                // Некоторые прокси-сервера записывают через запятую цепочки IP
                // Нам нужен последний IP в цепочке
                $ip_a = explode(',', $ip);
                $ip = trim(end($ip_a));
                break;
            }
        }
        return $ip;
    }

    function getHost()
    {
        $host = "";
        if (isset($_SERVER['HTTP_HOST']))
            $host = explode(":", $_SERVER['HTTP_HOST'])[0];
        return $host;
    }

    function isSecure()
    {
        return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https");
    }

    /**
     * @return string
     */
    function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}
