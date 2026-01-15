<?php

namespace jam\app\tg\response;

use jam\app\tg\TgBotState;
use jam\app\tg\TgResponse;

class Phone extends TgResponse {
    public function __construct(TgBotState $state) {
        $this->text = $state->langVariants(
            'Пожалуйста, нажмите кнопку ниже ⬇ ОТПРАВИТЬ НОМЕР',
            'Press the ⬇ SEND NUMBER button'
        );
        parent::__construct($state);
    }

    public function getReplyMarkup(): array|string {
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