<?php

namespace jam\app\tg\response;

use jam\app\tg\Questions;
use jam\app\tg\TgResponse;
use jam\app\tg\TgBotState;

class Answer extends TgResponse {
    protected $question;
    function __construct(TgBotState $state) {
        $profile = $state->getProfileInfo();
        $Q = new Questions();
        $questionId = $state->getUser()->question_id ?: 0;
        if ((int) $questionId === Questions::FINISH) {
            $questionId = 0;
        }
        $this->question = (object) ($questionId ? $Q->getQuestion($questionId) : $Q->getFirstQuestion());
        $ru = sprintf('Вопрос %d из %d', $Q->getQuestionNum($this->question->id), $Q->getCountQuestions()) ;
        $en = sprintf('Question %d from %d', $Q->getQuestionNum($this->question->id), $Q->getCountQuestions()) ;

        $oldAnswer = $state->getCurrentAnswer($this->question->id);

        $this->text = $state->langVariants($ru, $en)."\n\n".$state->langVariants($this->question->description, $this->question->description_en);
        if (!in_array(substr($this->text, -1), ['.', '?', ',', ':'])) {
            $this->text .= ':';
        }
        if ($oldAnswer && $oldAnswer->value !== "") {
            $this->text .= sprintf(" (<pre>%s</pre>)", $oldAnswer->value);
        }

        $ru = "Пожалуйста, отвечайте на вопросы латинскими буквами и не используйте эмодзи";
        $en = "Please answer questions in Latin letters and do not use emoji";
        $this->text = $state->langVariants($ru, $en)."\n\n".$this->text;

        if ($profile) {
            $this->text = $state->langVariants("Ваш профиль:", "Your profile:")."\n\n".$profile."\n\n".$this->text;
        }

        parent::__construct($state);
    }

    function getReplyMarkup() {
        $markup = [];

        if ($this->state->getPrevQuestion()) {
            $markup[] = [
                'text' => $this->state->langVariants('< Назад', '< Back'),
                'callback_data' => '/prev'
            ];
        }

        if (!empty($this->question->hint)) {
            $markup[] = ['text' => $this->state->langVariants('Подсказка', 'Hint'),
                'callback_data' => '/hint' // .$this->question->id
            ];
        }

        $variants = [];
        if (($this->state->getLang() === "ru") && !empty($this->question->variants)) {
            $variants = $this->question->variants;
        } elseif (($this->state->getLang() === "en") && !empty($this->question->variants_en)) {
            $variants = $this->question->variants_en;
        }

        if (!empty($variants)) {
            foreach ($variants as $variant) {
                $markup[] = [
                    'text' => $variant,
                    'callback_data' => $variant
                ];
            }
        }
        if (!empty($this->question->can_skip)) {
            $markup[] = [
                'text' => $this->state->langVariants('Пропустить вопрос', 'Skip question'),
                'callback_data' => '/skip-empty'
            ];
        }
        $answer = $this->state->getCurrentAnswer($this->question->id);
        if ($answer && ($answer->value !== "")) {
            $markup[] = [
                'text' => $this->state->langVariants('Данные верны', 'Data is correct'),
                'callback_data' => '/skip'
            ];
        }

        return json_encode($markup ? [
            "inline_keyboard" => array_chunk($markup, !empty($this->question->keyboard_one_button_in_row) ? 1 : 2),
        ] : [
            "hide_keyboard" => true
        ]);
    }
}