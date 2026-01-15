<?php

namespace jam\app\tg\response;

use jam\app\tg\state\StateException;
use jam\app\tg\TgBotState;

class NdaSend extends Nda {
    public function __construct(TgBotState $state) {
        parent::__construct($state);

        $name = $this->getName($state);
        $email = $this->getEmail($state);

        $res = \jam\app\utils\DocuSign::sentNDAToEmail($email, $name);
        if (!$res->envelope_id) {
            throw new StateException($state->langVariants('Ошибка отправки документа', 'Error sending document'));
        }
        $state->setEnvelopeId($res->envelope_id);
        if ($state->getLang() === 'ru') {
            $this->text = <<<TEXT
                Документ отправлен на почту <a href="mailto:{$email}">{$email}</a>, после подписи Вам придёт уведомление в Телеграм
TEXT;
        } else {
            $this->text = <<<TEXT
                The document has been sent to the mail <a href="mailto:{$email}">{$email}</a>, after signing you will receive a notification in Telegram
 TEXT;
        }
    }

    public function getReplyMarkup(): array|string {
        $markup = [];
        return json_encode([
            "inline_keyboard" => [$markup],
        ]);
    }
}