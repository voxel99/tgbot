<?php

namespace jam\app\tg;


use jam\app\tg\state\Answer;
use jam\app\tg\state\User;

class TgBotState {
    protected $chatId;
    protected ?User $user = null;
    protected $error = '';

    function __construct ($chatId) {
        $this->chatId = $chatId;
    }

    function getAnswerValue($questionId) {
        $answer = $this->getCurrentAnswer($questionId);
        return $answer ? $answer->value : '';
    }

    function getEmail() {
        return $this->getAnswerValue(Questions::ID_EMAIL);
    }

    function getName() {
        return $this->getAnswerValue(Questions::ID_NAME);
    }

    function setError($error) {
        $this->error = $error;
    }

    function getError() {
        return $this->error;
    }

    function getUser (): User {
        if (!$this->user) {
            $this->user = (new User())->get($this->chatId);
        }
        return $this->user;
    }

    function langVariants($ru, $en) {
        return lang($this->getLang(), $ru, $en);
    }

    function getAnswer($questionId) {
        $answers = $this->getUser()->getAnswers();
        return $answers[$questionId] ?? null;
    }

    function getProfileInfo () {
        $answers = $this->getUser()->getAnswers();
        $questions = (new Questions())->getList();
        $info = [];
        // При выводе профиля некоторые поля объединяются в одну строку
        $usedIds = [];
        /** @var Answer $answer */
        foreach ($questions as $question) {
            $question = (object)$question;
            if (isset($answers[$question->id]) && !in_array($question->id, $usedIds) && ($answers[$question->id]->value !== "")) {
                $answerValue = [$answers[$question->id]->value];
                if (!empty($question->link)) {
                    foreach ($question->link as $linkedQuestionId) {
                        if (isset($answers[$linkedQuestionId])) {
                            $answerValue[] = $answers[$linkedQuestionId]->value;
                            $usedIds[] = $linkedQuestionId;
                        }
                    }
                }
                $info[] = sprintf('<b>%s</b>: %s',
                    $question->alt ?? lang($this->getLang(), $question->name, $question->name_en),
                    implode(' ', $answerValue)
                );
            }
        }
        return implode("\n", $info);
    }

    function getLang () {
        return $this->getUser()->lang;
    }

    function setReferrer($referrer) {
        $this->getUser()->setReferrer($referrer);
        $this->getUser()->save();
    }

    function setEnvelopeId($envelopeId) {
        $this->getUser()->setEnvelopeId($envelopeId);
        $this->getUser()->save();
    }

    function setLang($lang) {
        $this->getUser()->setLang($lang);
        $this->getUser()->save();
    }

    function setPhone($phone) {
        $this->getUser()->setPhone($phone);
        $this->getUser()->save();
    }

    function resetQuestions() {
        $this->getUser()->question_id = Questions::FIRST_QUESTION_ID;
        $this->getUser()->save();
    }

    function setPrevQuestions() {
        $Q = new Questions();
        $prevQuestion = $Q->getPrevQuestion($this->getUser()->question_id);
        $this->getUser()->question_id = $prevQuestion ? $prevQuestion->id : 0;
        $this->getUser()->save();
    }
    function setNextQuestions() {
        $Q = new Questions();
        $nextQuestion = $Q->getNextQuestion($this->getUser()->question_id);
        $this->getUser()->question_id = $nextQuestion ? $nextQuestion->id : Questions::FINISH;
        $this->getUser()->save();
    }

    function getCurrentAnswer ($questionId = null) {
        if (!$questionId) {
            $question = $this->getCurrentQuestion();
            if ($question) {
                $questionId = $question->id;
            }
        }
        $answers = $this->getUser()->getAnswers();
        return $answers[$questionId] ?? null;
    }

    function haveCurrentAnswer ($questionId = null) {
        return !!$this->getCurrentAnswer($questionId);
    }

    function getPrevQuestion () {
        $Q = new Questions();
        return $Q->getPrevQuestion($this->getUser()->question_id);
    }

    function setCurrentQuestion ($value) {
        $this->getUser()->setAnswer($this->getUser()->question_id, $value);
    }

    function getCurrentQuestion () {
        $Q = new Questions();
        return $Q->getQuestion($this->getUser()->question_id ?: Questions::FIRST_QUESTION_ID);
    }

    function getNextQuestion () {
        $Q = new Questions();
        return $Q->getNextQuestion($this->getUser()->question_id);
    }

    function isExists () {
        return $this->getUser()->exists();
    }

    function isFinish() {
        return (int) ($this->getUser()->question_id) === Questions::FINISH;
    }

    function hasPhone () {
        return $this->getUser()->phone;
    }

    function updateCurrentState($text) {
        $user = $this->getUser();
        if (!$user->lang) {
            $user->setLang($text);
        } else if (!$user->phone) {
            $user->setPhone($text);
        } else {
            $Q = new Questions();
            if (!$user->question_id) {
                $firstQuestion = $Q->getFirstQuestion();
                if (empty($firstQuestion['id'])) {
                    throw new \Exception("Empty question list");
                }
                $user->question_id = $firstQuestion['id'];
            }
            if ($user->question_id !== Questions::FINISH) {
                $user->setAnswer($user->question_id, $text);
                $nextQuestion = $Q->getNextQuestion($user->question_id);
                if ($nextQuestion) {
                    $user->question_id = $nextQuestion->id;
                } else {
                    $user->question_id = Questions::FINISH;
                }
            }
        }
        $user->save();
    }
}