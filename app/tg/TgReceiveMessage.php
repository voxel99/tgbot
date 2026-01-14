<?php

namespace jam\app\tg;

class TgReceiveMessage extends TgMessage {
    public $userName;
    public $firstName;
    public $lastName;
    public $phone;
    public $callbackQueryId = null;
    // private $callbackData;

    function __construct(array $input) {
        $this->parse($input);
    }

    function parse(array $data) {
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

    function getChatId() {
        return $this->chatId;
    }

    function getText() {
        return $this->text;
    }

    function getPhone() {
        return $this->phone;
    }

    function getCallbackQueryid() {
        return $this->callbackQueryId;
    }

    // function getCallbackData() {
    //     return $this->callbackData;
    // }

}