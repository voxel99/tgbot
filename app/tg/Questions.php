<?php

namespace jam\app\tg;

class Questions {
    protected $list;

    const FIRST_QUESTION_ID = 10;
    const ID_NAME = 10;
    const ID_COUNTRY = 20;
    const ID_BIRTHDATE = 30;
    const ID_EMAIL = 40;
    const ID_COMPANY = 50;
    const ID_COMPANY_WEBSITE = 60;
    const ID_NDA = 100;
    const FINISH = 9999;

    function __construct() {
        $this->list = config('questions');
    }

    function getList() {
        return $this->list;
    }

    function getCountQuestions () {
        return count($this->list);
    }

    function getQuestionNum($questionId) {
        foreach ($this->list as $k => $item) {
            $item = (object) $item;
            if ((int) $item->id === (int) $questionId) {
                return $k + 1;
            }
        }
        return 0;
    }

    function getQuestion($questionId, $next = false, $prev = false) {
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

    function getNextQuestion($questionId) {
        return $this->getQuestion($questionId, true);
    }

    function getPrevQuestion($questionId) {
        return $this->getQuestion($questionId, false, true);
    }

    function getFirstQuestion() {
        return $this->list[0] ?? null;
    }
}
