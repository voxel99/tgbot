<?php

namespace jam\app\tg\response;

use jam\app\tg\TgBotState;
use jam\app\tg\TgResponse;

class Phone extends TgResponse {
    function __construct(TgBotState $state) {
        $this->text = $state->langVariants(
            'Пожалуйста, нажмите кнопку ниже ⬇ ОТПРАВИТЬ НОМЕР',
            'Press the ⬇ SEND NUMBER button'
        );
        parent::__construct($state);
    }

    function getReplyMarkup() {
        $markup = [
            ['text' => $this->state->langVariants('📲 ОТПРАВИТЬ НОМЕР', '📲 SEND NUMBER'), 'request_contact' => true]
        ];
        return json_encode([
            "keyboard" => [$markup],
            "one_time_keyboard" => true,
            "resize_keyboard" => true
        ]);
    }
}