<?php

namespace jam\app\tg\response;

use jam\app\tg\state\StateException;
use jam\app\tg\TgResponse;
use jam\app\tg\TgBotState;

class NdaComplete extends Nda {
    function __construct(TgBotState $state) {
        parent::__construct($state);
        if ($state->getLang() === 'ru') {
            $this->text = <<<TEXT
                Спасибо, мы получили Вашу подпись!
TEXT;
        } else {
            $this->text = <<<TEXT
                Thank you, we have received your signature!
 TEXT;
        }
    }

    function getReplyMarkup() {
        $markup = [];
        return json_encode([
            "inline_keyboard" => [$markup],
        ]);
    }
}