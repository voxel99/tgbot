<?php

namespace jam\engine\core;

class Response {
    /**
     * @var string[]
     */
    private $_headers = [];

    /**
     * @var string
     */
    private $_content = "";

    public function __construct () {
    }

    public function sendHeaders () {
        foreach ($this->_headers as $k => $v)
            header($k . ":" . $v);
        return $this;
    }

    public function header ($key, $value) {
        $this->_headers[$key] = $value;
        return $this;
    }

    public function headers (array $headers) {
        foreach ($headers as $key => $value)
            $this->_headers[$key] = $value;
        return $this;
    }

    public function setContent (string $content) {
        $this->_content = $content;
        return $this;
    }

    public function content () {
        return $this->_content;
    }

    public function send () {
        $this->sendHeaders();
        echo $this->_content;
    }

    public function __toString () {
        return $this->content();
    }

    public function json ($data) {
        $this->header("Content-Type", "application/json");
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function error ($message = 'Error', $code = 500) {
        header($message, true, $code);
        return $this;
    }
}
