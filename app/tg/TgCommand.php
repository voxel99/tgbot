<?php

namespace jam\app\tg;

use jam\app\tg\state\StateException;

class TgCommand {
    public static function getDefaultResponseType (TgBotState $state, bool $isContinue = false): string {
        $type = TgResponse::ANSWER;
        if (!$state->getLang()) {
            $type = TgResponse::START;
        } else if (!$state->hasPhone()) {
            $type = TgResponse::PHONE;
        } else if ($state->isFinish()) {
            $type = $isContinue ? TgResponse::RESET : TgResponse::FINISH;
        }
        return $type;
    }

    public static function processCommand(TgReceiveMessage $message, TgBotState $state): ?string {
        $args = explode(' ', $message->getText());
        $command = $args[0];
        $arg = $args[1] ?? '';
        $type = null;
        switch ($command) {
            case '/start':
                if (!$state->getUser()->exists()) {
                    $type = TgResponse::START;
                } else {
                    $type = TgResponse::RESET;
                }
                if ($arg) {
                    $state->setReferrer($arg);
                }
            break;
            case '/begin':
                $state->setLang($arg);
                $state->resetQuestions();
            break;
            case '/prev':
                $state->setPrevQuestions();
            break;
            case '/skip':
                if ($state->haveCurrentAnswer()) {
                    $state->setNextQuestions();
                }
            break;
            case '/skip-empty':
                $state->setCurrentQuestion('');
                $state->setNextQuestions();
            break;
            case '/hint':
                $type = TgResponse::HINT;
            break;
            case '/nda':
                $type = TgResponse::NDA;
            break;
            case '/nda_send':
                $type = TgResponse::NDA_SEND;
            break;
        }
        if (is_null($type)) {
            $state = self::getDefaultResponseType($state, $command === '/continue');
        }
        return $type;
    }

    public static function processAnswer(TgReceiveMessage $message, TgBotState $state): string {
        $nextResponseType = TgResponse::ANSWER;
        $text = $message->getText();
        if ($message->getPhone()) {
            if ($state->getUser()->getPhone() !== $message->getPhone()) {
                $state->setPhone($message->getPhone());
            }
        } else if ($text) {
            if (!$state->isExists()) {
                $nextResponseType = TgResponse::START;
            } else if (!$state->getUser()->phone) {
                $nextResponseType = TgResponse::PHONE;
            } else {
                $state->updateCurrentState($text);
                if ($state->isFinish()) {
                    $nextResponseType = TgResponse::FINISH;
                }
            }
        } else {
            throw new StateException($state->langVariants("Ошибка", "Error"));
        }
        return $nextResponseType;
    }

    public static function process (TgReceiveMessage $message, TgBotState $state): TgResponse {
        if (self::isCommand($message)) {
            $responseType = self::processCommand($message, $state);
            if (!$responseType) {
                $responseType = $state->getUser()->exists() ? self::getDefaultResponseType($state) : TgResponse::START;
            }
        } else {
            $responseType = self::processAnswer($message, $state);
        }
        return TgResponse::create($responseType, $state);
    }

    public static function isCommand(TgReceiveMessage $message, string $commandName = ''): bool {
        $text = $message->getText();
        $isLeadingSlash = $text && !empty($text[0]) && ($text[0] === '/');
        $isTargetCommand = trim(substr($text, 1)) === $commandName;
        return $isLeadingSlash && (!$commandName || $isTargetCommand);
    }
}