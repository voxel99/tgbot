<?php

namespace jam\app\tg;


use jam\app\tg\state\Answer;
use jam\app\tg\state\User;

class TgBotState {
    protected int $chatId;
    protected ?User $user = null;
    protected string $error = '';

    public function __construct (int $chatId) {
        $this->chatId = $chatId;
    }

    public function getAnswerValue(int $questionId): string {
        $answer = $this->getCurrentAnswer($questionId);
        return $answer ? $answer->value : '';
    }

    public function getEmail(): string {
        return $this->getAnswerValue(Questions::ID_EMAIL);
    }

    public function getName(): string {
        return $this->getAnswerValue(Questions::ID_NAME);
    }

    public function setError(string $error): void {
        $this->error = $error;
    }

    public function getError(): string {
        return $this->error;
    }

    public function getUser (): User {
        if (!$this->user) {
            $this->user = (new User())->get($this->chatId);
        }
        return $this->user;
    }

    public function langVariants(string $ru, string $en): string {
        return lang($this->getLang(), $ru, $en);
    }

    public function getAnswer(int $questionId): ?Answer {
        $answers = $this->getUser()->getAnswers();
        return $answers[$questionId] ?? null;
    }

    public function getProfileInfo (): string {
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

    public function getLang (): ?string {
        return $this->getUser()->lang;
    }

    public function setReferrer(string $referrer): void {
        $this->getUser()->setReferrer($referrer);
        $this->getUser()->save();
    }

    public function setEnvelopeId(string $envelopeId): void {
        $this->getUser()->setEnvelopeId($envelopeId);
        $this->getUser()->save();
    }

    public function setLang(string $lang): void {
        $this->getUser()->setLang($lang);
        $this->getUser()->save();
    }

    public function setPhone(string $phone): void {
        $this->getUser()->setPhone($phone);
        $this->getUser()->save();
    }

    public function resetQuestions(): void {
        $this->getUser()->question_id = Questions::FIRST_QUESTION_ID;
        $this->getUser()->save();
    }

    public function setPrevQuestions(): void {
        $Q = new Questions();
        $prevQuestion = $Q->getPrevQuestion($this->getUser()->question_id);
        $this->getUser()->question_id = $prevQuestion ? $prevQuestion->id : 0;
        $this->getUser()->save();
    }

    public function setNextQuestions(): void {
        $Q = new Questions();
        $nextQuestion = $Q->getNextQuestion($this->getUser()->question_id);
        $this->getUser()->question_id = $nextQuestion ? $nextQuestion->id : Questions::FINISH;
        $this->getUser()->save();
    }

    public function getCurrentAnswer (?int $questionId = null): ?Answer {
        if (!$questionId) {
            $question = $this->getCurrentQuestion();
            if ($question) {
                $questionId = $question->id;
            }
        }
        $answers = $this->getUser()->getAnswers();
        return $answers[$questionId] ?? null;
    }

    public function haveCurrentAnswer (?int $questionId = null): bool {
        return !!$this->getCurrentAnswer($questionId);
    }

    public function getPrevQuestion (): ?object {
        $Q = new Questions();
        return $Q->getPrevQuestion($this->getUser()->question_id);
    }

    public function setCurrentQuestion (string $value): void {
        $this->getUser()->setAnswer($this->getUser()->question_id, $value);
    }

    public function getCurrentQuestion (): ?object {
        $Q = new Questions();
        return $Q->getQuestion($this->getUser()->question_id ?: Questions::FIRST_QUESTION_ID);
    }

    public function getNextQuestion (): ?object {
        $Q = new Questions();
        return $Q->getNextQuestion($this->getUser()->question_id);
    }

    public function isExists (): bool {
        return $this->getUser()->exists();
    }

    public function isFinish(): bool {
        return (int) ($this->getUser()->question_id) === Questions::FINISH;
    }

    public function hasPhone (): ?string {
        return $this->getUser()->phone;
    }

    public function updateCurrentState(string $text): void {
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