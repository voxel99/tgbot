<?php

namespace jam\engine\core;

use jam\engine\utils\Strings;

/**
 * Объект запроса
 */
class Request {
    /**
     * Типы запросов
     */
    const TYPE_ACTION = "action"; // HTTP
    const TYPE_BLOCK = "block";  // HTTP
    const TYPE_CLI = "cli"; // Command line

    /**
     * Методы запросов
     */
    const METHOD_GET = "get";
    const METHOD_POST = "post";
    const METHOD_OPTIONS = "options";

    /**
     * URI запроса
     * @var string
     */
    private $_uri = '';

    /**
     * Полный URI запроса
     * @var string
     */
    private $_source_uri = '';

    /**
     * Тип запроса
     * @var string
     */
    private $_type;

    /**
     * Метод запроса
     * @var string
     */
    private $_method;

    /**
     * Окружение для запроса
     * @var RequestEnv
     */
    private $_env = null;

    /**
     * Аякс-запрос?
     * @var bool
     */
    private $_ajax = false;

    /**
     * Флаги запроса CLI режима?
     * @var String[]
     */
    private $flags = [];

    /**
     * Конструктор
     * @param string $uri Параметры запроса
     * @param string $type Тип запроса
     * @param string $method Тип запроса (get|post)
     */
    public function __construct (string $uri = '', string $type = '', string $method = '') {
        $this->_env = new RequestEnv();

        if (!$type) {
            $type = $this->determineType();
        }
        if (!$uri) {
            $uri = $this->determineUri($type);
        }
        if (!$method) {
            $method = $this->determineMethod();
        }
        if ($uri) {
            $this->_source_uri = $uri;
            if (false !== ($pos = strpos($uri, "?"))) {
                $uri = substr($uri, 0, $pos);
            }
            $data = array();
            $data_tmp = explode('/', $uri);
            foreach ($data_tmp as $v) {
                if ($v !== '') {
                    $data[] = $v;
                }
            }

            $uri = implode('/', $data);
        }
        $this->_type = $type;
        $this->_uri = $uri;
        $this->_method = $method;
        $this->setAjax("auto");
    }

    /**
     * Определить тип запроса
     * @return string Тип запроса
     */
    private function determineType (): string {
        $type = self::TYPE_ACTION;
        if (!isset($_SERVER['SERVER_SOFTWARE']) && (
            php_sapi_name() == 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0)
        )) {
            $type = self::TYPE_CLI;
        }
        return $type;
    }

    /**
     * Определить URI по типу запроса
     * @param string $type
     * @return string URI запроса
     */
    private function determineUri (string $type): string {
        $uri = '';
        switch ($type) {
            case self::TYPE_ACTION:
                $uri = empty($_SERVER['REQUEST_URI']) ? "/" : $_SERVER['REQUEST_URI'];
            break;
            case self::TYPE_CLI:
                if (!empty($_SERVER['argv'])) {
                    $uri = $_SERVER['argv'];
                    array_shift($uri); // Отбрасываем первый элемент - имя скрипта запуска
                    $newUri = [];
                    foreach ($uri as $uriPart) {
                        if (($uriPart[0] === '-') && !empty($uriPart[1])) {
                            if ($uriPart[1] === '-') // --param
                            {
                                $f = substr($uriPart, 2);
                                $pair = Strings::pairValue($f, true);
                                foreach ($pair as $k => $v) {
                                    $this->flags[$k] = $v;
                                }
                            } else // -params
                            {
                                $this->flags[substr($uriPart, 1)] = 1;
                            }
                            continue;
                        }
                        $newUri[] = $uriPart;
                    }
                    $uri = implode('/', $newUri);
                }
            break;
        }
        return $uri;
    }

    private function determineMethod (): string {
        $method = self::METHOD_GET;
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }
        return $method;
    }

    /**
     * Получить URI запроса
     * @return string
     */
    public function getUri (): string {
        return $this->_uri;
    }

    /**
     * Задать URI запроса
     * @param string $uri
     * @return $this
     */
    public function setUri (string $uri): static {
        $this->_uri = $uri;
        return $this;
    }

    /**
     * Текущий тип запроса - командная строка?
     * @return bool
     */
    public function isCli (): bool {
        return $this->_type === self::TYPE_CLI;
    }

    /**
     * Текущий тип запроса - HTTP?
     * @return bool
     */
    public function isAction (): bool {
        return $this->_type === self::TYPE_ACTION;
    }

    /**
     * Получить окружение для запроса
     * @return RequestEnv
     */
    public function env (): RequestEnv {
        return $this->_env;
    }

    /**
     * Установить флаг
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setFlag(string $name, mixed $value): static {
        $this->flags[$name] = $value;
        return $this;
    }

    public function setVerboseFlag(mixed $value, bool $vv = false): void {
        $this->setFlag($vv ? 'vv' : 'v', $value);
    }

    public function isVerboseFlag (bool $vv = false): bool {
        $vFlag = !$vv && !empty($this->flags['v']);
        $vvFlag = !empty($this->flags['vv']);
        return $vFlag || $vvFlag;
    }

    public function isHelpFlag (): bool {
        return !empty($this->flags['h']);
    }

    public function isForceFlag (): bool {
        return !empty($this->flags['f']) || !empty($this->flags['force']);
    }

    public function isProfileFlag (): bool {
        return !empty($this->flags['profile']);
    }

    public function isLogFlag (): bool {
        return !empty($this->flags['ll']);
    }

    public function flag (string $f, mixed $default = false): mixed {
        return $this->flags[$f] ?? $default;
    }

    public function isAjaxRequestedWith (): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public function setAjax (string|bool $flag): void {
        if ($flag === "auto") {
            $this->_ajax = $this->isAjaxRequestedWith();
        } else {
            $this->_ajax = $flag;
        }
    }

    /**
     * Uri запроса
     * @return string
     */
    public function uri (): string {
        return $this->_uri;
    }

    /**
     * Тип запроса
     * @return string
     */
    public function type (): string {
        return $this->_type;
    }

    /**
     * Метод запроса
     * @return string
     */
    public function method (): string {
        return $this->_method;
    }

    public function get (string $name, mixed $default = null): mixed {
        if ($this->method() === self::METHOD_GET) {
            return isset($_GET[$name]) ? $_GET[$name] : $default;
        }
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
}
