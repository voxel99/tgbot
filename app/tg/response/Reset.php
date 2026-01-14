<?php

namespace jam\app\tg\response;

use jam\app\tg\TgBotState;
use jam\app\tg\TgResponse;

class Reset extends TgResponse {
    function __construct(TgBotState $state) {
        if ($state->isFinish()) {
            $this->text = $state->langVariants("Ваш профиль:", "Your profile:")."\n\n".$state->getProfileInfo()."\n\n";
            $this->text .= $state->langVariants('Хотите начать заново?', 'Do you want to start over?');
        } else {
            $this->text = $state->langVariants('Вы хотите продолжить или начать заново?', 'Do you want to continue or start over?');
        }
        parent::__construct($state);
    }

    function getReplyMarkup() {
        $first = $this->state->isFinish() ?
            ['text' => $this->state->langVariants('Отмена', 'Continue'), 'callback_data' => '/cancel']:
            ['text' => $this->state->langVariants('Продолжить', 'Continue'), 'callback_data' => '/continue'];

        $markup = [
            ['text' => 'Начать заново RU', 'callback_data' => '/begin ru'],
            ['text' => 'Start over EN', 'callback_data' => '/begin en']
        ];
        return json_encode([
            "inline_keyboard" => [[$first], $markup],
        ]);
    }
}