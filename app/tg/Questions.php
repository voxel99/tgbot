<?php

namespace jam\app\tg;

class Questions {
    protected array $list;

    const FIRST_QUESTION_ID = 10;
    const ID_NAME = 10;
    const ID_COUNTRY = 20;
    const ID_BIRTHDATE = 30;
    const ID_EMAIL = 40;
    const ID_COMPANY = 50;
    const ID_COMPANY_WEBSITE = 60;
    const ID_NDA = 100;
    const FINISH = 9999;

    public function __construct() {
        $this->list = config('questions');
    }

    public function getList(): array {
        return $this->list;
    }

    public function getCountQuestions (): int {
        return count($this->list);
    }

    public function getQuestionNum(int $questionId): int {
        foreach ($this->list as $k => $item) {
            $item = (object) $item;
            if ((int) $item->id === (int) $questionId) {
                return $k + 1;
            }
        }
        return 0;
    }

    public function getQuestion(int $questionId, bool $next = false, bool $prev = false): ?object {
        $find = false;
        $question = null;
        $prevQuestion = null;
        foreach ($this->list as $item) {
            $item = (object) $item;
            if ($next && $find) {
                $question = $item;
                break;
            }
            if ((int) $item->id === (int) $questionId) {
                if ($prev) {
                    $question = $prevQuestion;
                    break;
                } else {
                    $find = true;
                    if (!$next) {
                        $question = $item;
                        break;
                    }
                }
            }
            $prevQuestion = $item;
        }
        return $question;
    }

    public function getNextQuestion(int $questionId): ?object {
        return $this->getQuestion($questionId, true);
    }

    public function getPrevQuestion(int $questionId): ?object {
        return $this->getQuestion($questionId, false, true);
    }

    public function getFirstQuestion(): ?array {
        return $this->list[0] ?? null;
    }
}
