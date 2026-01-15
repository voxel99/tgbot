<?php

namespace jam\app\tg;

use jam\app\tg\response\Answer;
use jam\app\tg\response\Finish;
use jam\app\tg\response\Hint;
use jam\app\tg\response\MyError;
use jam\app\tg\response\Phone;
use jam\app\tg\response\Reset;
use jam\app\tg\response\Start;
use jam\app\tg\response\Nda;
use jam\app\tg\response\NdaSend;
use jam\app\tg\response\NdaComplete;

class TgResponse extends TgMessage {
    const START = 'start';
    const PHONE = 'phone';
    const RESET = 'reset';
    const ANSWER = 'answer';
    const ERROR = 'error';
    const FINISH = 'finish';
    const HINT = 'hint';
    const NDA = 'nda';
    const NDA_SEND = 'nda_send';
    const NDA_COMPLETE = 'nda_complete';

    protected TgBotState $state;

    public function __construct(TgBotState $state) {
        $this->state = $state;
    }

    public static function create(string $type, TgBotState $state): TgResponse {
        $types = [
            self::START => Start::class,
            self::PHONE => Phone::class,
            self::RESET => Reset::class,
            self::ERROR => MyError::class,
            self::ANSWER => Answer::class,
            self::FINISH => Finish::class,
            self::HINT => Hint::class,
            self::NDA => Nda::class,
            self::NDA_SEND => NdaSend::class,
            self::NDA_COMPLETE => NdaComplete::class
        ];
        if (!$type) {
            throw new \Exception("Response type is empty");
        }
        if (empty($types[$type])) {
            throw new \Exception("Response class not found: ".$type);
        }
        $class = $types[$type];
        $responseObject = new $class($state);
        if (!($responseObject instanceof TgResponse)) {
            throw new \Exception("Response class is not instanceof TgResponseMessage");
        }
        return $responseObject;
    }

    public function getReplyMarkup(): array|string {
        return '';
    }

    public function isAnswerCallbackQuery(): bool {
        return false;
    }
}