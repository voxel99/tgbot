<?php

namespace jam\app\tg;

class TgMessage {
    protected $text;
    protected $chatId;

    function getChatId() {
        return $this->chatId;
    }

    function getText() {
        return $this->text;
    }
}