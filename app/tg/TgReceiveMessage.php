<?php

namespace jam\app\tg;

class TgReceiveMessage extends TgMessage {
    public string $userName = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $phone = '';
    public ?string $callbackQueryId = null;
    // private $callbackData;

    public function __construct(array $input) {
        $this->parse($input);
    }

    public function parse(array $data): void {
        $t = 'message';
        if (!empty($data['callback_query'])) {
            $t = 'callback_query';
            $this->text = $data['callback_query']['data'] ?? '';
            $this->callbackQueryId = $data['callback_query']['id'] ?? null;
        } else {
            $this->text = !empty($data[$t]['text']) ? trim($data['message']['text']) : '';
        }
        $this->chatId = $data[$t]['from']['id'] ?? '';
        $this->userName = $data[$t]['from']['username'] ?? '';
        $this->firstName = $data[$t]['from']['first_name'] ?? '';
        $this->lastName = $data[$t]['from']['last_name'] ?? '';
        $this->phone = $data['message']['contact']['phone_number'] ?? '';
    }

    public function getChatId(): string {
        return $this->chatId;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getPhone(): string {
        return $this->phone;
    }

    public function getCallbackQueryid(): ?string {
        return $this->callbackQueryId;
    }

    // function getCallbackData() {
    //     return $this->callbackData;
    // }

}