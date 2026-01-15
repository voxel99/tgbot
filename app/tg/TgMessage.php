<?php

namespace jam\app\tg;

class TgMessage {
    protected string $text = '';
    protected string $chatId = '';

    public function getChatId(): string {
        return $this->chatId;
    }

    public function getText(): string {
        return $this->text;
    }
}