<?php

namespace jam\app\tg\response;

use jam\app\tg\state\StateException;
use jam\app\tg\TgResponse;
use jam\app\tg\TgBotState;

class Hint extends TgResponse {
    function __construct(TgBotState $state) {
        $question = $state->getCurrentQuestion();
        if ($question && !empty($question->hint)) {
            $this->text = $state->langVariants($question->hint, $question->hint_en);
        } else {
            throw new StateException($state->langVariants('У вопроса нет подсказки', 'This question don\'t have a hint'));
        }
        parent::__construct($state);
    }
    function isAnswerCallbackQuery() {
        return true;
    }
}