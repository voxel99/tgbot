<?php
/**
 * Helper functions
 */

use jam\engine\core\Database;
use jam\engine\core\DbSimple\Database as DbSimpleDatabase;
use jam\engine\core\Request;

/**
 * Get request
 * @return Request
 */
function request (): Request {
    global $jam;
    return $jam->request();
}

/**
 * Секция конфига
 *
 * @param string $section Секции, разделённые точкой
 * @param mixed $default Значение по умолчанию, если конфигурационное не найдено
 * @return mixed
 */
function config (string $section, mixed $default = []): mixed {
    global $jam;
    return $jam->config($section, $default);
}

/**
 * Экземпляр БД
 */
function db (string $name = "master"): Database|DbSimpleDatabase {
    global $jam;
    return $jam->db($name);
}

/**
 * Получить значение из .env
 *
 * @param string $name
 * @param string $default
 * @return string|array
 */
function env (string $name, string $default = ''): string|array {
    global $jam;
    return $jam->env($name, $default);
}

/**
 * Выводит дамп всех переданных аргументов и завершает работу,
 * используется для отладки при разработке
 * @param ...$args
 * @return void
 */
function dd (...$args): void {
    $c = count($args);
    echo request()->isCli() ? "dd:" : "<h2>dd:</h2>";
    for ($i = 0; $i < $c; $i++) {
        echo request()->isCli() ? "\n" : "<pre>";
        var_dump($args[$i]);
        echo request()->isCli() ? "\n" : "</pre>";
        if ($i < $c - 1) {
            echo request()->isCli() ? "\n\n" . str_pad("", 50, "-") . "\n\n" : "\n\n\n\n<hr>\n\n\n\n";
        }
    }
    echo request()->isCli() ? "Stack trace:\n" : "<pre>";
    echo implode("", backtrace());
    echo request()->isCli() ? "\n" : "</pre>";
    die;
}

function formatTraceLine (array $b): string {
    $obj = !empty($b['object']) ? get_class($b['object']) : '';
    $func = !empty($b['function']) ? $b['function'] : '';
    $class = !empty($b['class']) ? $b['class'] : '';
    $file = !empty($b['file']) ? $b['file'] : '';
    $line = !empty($b['line']) ? ", " . $b['line'] : '';
    $args = null;

    if (!empty($b['args'])) {
        $args = $b['args'];
        array_walk($args, function (&$value) {
            $value = gettype($value) . (is_array($value) ? '(' . count($value) . ')' : '');
        });
    }

    if ($obj != $class) {
        $class = sprintf('[ %s <- %s ]', $class, $obj);
    }
    $invoke = sprintf('%s%s%s',
        !is_null($class) ? $class . '::' : '',
        $func,
        !is_null($args) ? '(' . implode(', ', $args) . ')' : ''
    );
    $pos = !$file ? '' : sprintf('(%s%s)',
        substr($file, strpos($file, "jam")),
        $line
    );
    return sprintf("%-120s%-60s\n", $invoke, $pos);
}

function backtrace (int $slice = 1, int $length = 0): array {
    $bt = debug_backtrace();
    $trace = [];
    foreach ($bt as $k => $b) {
        if ($k < $slice) {
            continue;
        }
        if ($length && $k >= $slice + $length) {
            break;
        }
        $trace[] = formatTraceLine($b);
    }
    return $trace;
}

/**
 * Шаблонизатор
 *
 * @param string $tmpl Путь к шаблону
 * @param array $params Ассоциативный массив параметров
 * @return string
 * @throws Exception
 */
function tpl (string $tmpl, array $params = []): string {
    global $jam;
    $tmplPath = $jam->getRootDir() . '/app/templates/' . $tmpl;
    if (!$tmplPath) {
        throw new \Exception(sprintf('Template file not found: %s', $tmplPath));
    }
    if ($params) {
        extract($params);
    }
    ob_start();
    require $tmplPath;
    return ob_get_clean();
}

/**
 * Базовая авторизация
 * @param string $section Секция в конфиге
 * @return void
 */
function show401 (string $section = 'main'): void {
    $login = config('401.'.$section.'.login');
    $password = config('401.'.$section.'.password');
    if (empty($login)
        || !isset($_SERVER['PHP_AUTH_USER'])
        || !isset($_SERVER['PHP_AUTH_PW'])
        || ($_SERVER['PHP_AUTH_USER'] != $login)
        || ($_SERVER['PHP_AUTH_PW'] != $password)
    ) {
        header('WWW-Authenticate: Basic realm=""');
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
}

/**
 * Вывод на консоль
 * @param ...$args
 * @return void
 */
function verb (...$args): void {
    if (\request()->isCli() && \request()->isVerboseFlag()) {
        $format = array_shift($args);
        echo vsprintf($format, $args) . "\n";
    }
}