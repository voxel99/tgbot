<?php

namespace jam\app\tg\response;

use jam\app\tg\TgBotState;
use jam\app\tg\TgResponse;

class MyError extends TgResponse {
    function __construct(TgBotState $state) {
        $this->text = $state->getError();
        parent::__construct($state);
    }

    function getReplyMarkup() {
        $markup = [
            ['text' => $this->state->langVariants('Продолжить', 'Continue'), 'callback_data' => '/continue'],
            ['text' => 'Начать заново RU', 'callback_data' => '/begin ru'],
            ['text' => 'Start over EN', 'callback_data' => '/begin en']
        ];
        return json_encode([
            "inline_keyboard" => [$markup],
        ]);
    }
}