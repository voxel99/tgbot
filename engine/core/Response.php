<?php

namespace jam\engine\core;

class Response {
    /**
     * @var string[]
     */
    private array $_headers = [];

    /**
     * @var string
     */
    private string $_content = "";

    public function __construct () {
    }

    public function sendHeaders (): static {
        foreach ($this->_headers as $k => $v)
            header($k . ":" . $v);
        return $this;
    }

    public function header (string $key, string $value): static {
        $this->_headers[$key] = $value;
        return $this;
    }

    public function headers (array $headers): static {
        foreach ($headers as $key => $value)
            $this->_headers[$key] = $value;
        return $this;
    }

    public function setContent (string $content): static {
        $this->_content = $content;
        return $this;
    }

    public function content (): string {
        return $this->_content;
    }

    public function send (): void {
        $this->sendHeaders();
        echo $this->_content;
    }

    public function __toString (): string {
        return $this->content();
    }

    public function json (mixed $data): string {
        $this->header("Content-Type", "application/json");
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function error (string $message = 'Error', int $code = 500): static {
        header($message, true, $code);
        return $this;
    }
}
