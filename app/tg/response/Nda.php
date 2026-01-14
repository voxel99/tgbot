<?php

namespace jam\app\tg\response;

use jam\app\tg\state\StateException;
use jam\app\tg\TgResponse;
use jam\app\tg\TgBotState;

class Nda extends TgResponse {
    protected TgBotState $state;

    protected function getEmail(TgBotState $state) {
        $email = $state->getEmail();
        if (!$email) {
            throw new StateException($state->langVariants('Вы не указали email', 'You didn\'t provide an email'));
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new StateException($state->langVariants('Указан некорректный email', 'Invalid email specified').': '.$email);
        }
        return $email;
    }

    protected function getName(TgBotState $state) {
        $name = $state->getName();
        if (!$name) {
            throw new StateException($state->langVariants('Вы не указали своё имя и фамилию', 'You didn\'t provide an name and surename'));
        }
        return $name;
    }

    function __construct(TgBotState $state) {
        $this->state = $state;
        $name = $this->getName($state);
        $email = $this->getEmail($state);

        if ($state->getLang() === 'ru') {
            $this->text = <<<TEXT
            {$name}, нажмите, пожалуйста, кнопку ниже, и Вам на почту <a href="mailto:{$email}">{$email}</a> придут документы для подписи
TEXT;
        } else {
            $this->text = <<<TEXT
            {$name}, please click the button below and you will receive documents to sign by <a href="mailto:{$email}">{$email}</a>
TEXT;
        }

        parent::__construct($state);
    }

    function getReplyMarkup() {
        $markup = [
            ['text' => $this->state->langVariants('Получить', 'Receive'), 'callback_data' => '/nda_send '.$this->state->getEmail()],
        ];
        return json_encode([
            "inline_keyboard" => [$markup],
        ]);
    }
}