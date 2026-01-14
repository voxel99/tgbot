<?php

namespace jam\app\tg\response;

use jam\app\tg\TgResponse;
use jam\app\tg\TgBotState;

class Start extends TgResponse {
    function __construct(TgBotState $state) {
        $this->text = tpl("response/start.html");
        parent::__construct($state);
    }

    function getReplyMarkup() {
        $markup = [
            ['text' => 'Начать RU', 'callback_data' => '/begin ru'],
            ['text' => 'Start EN', 'callback_data' => '/begin en']
        ];
        return json_encode([
            "inline_keyboard" => [$markup],
            "is_persistent" => false,
            "one_time_keyboard" => true
        ]);
    }
}