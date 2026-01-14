<?php

namespace jam\app\tg\state;

use jam\app\tg\Questions;

class User extends Model {
    /*
    public $id = null;
    public $chat_id = null;
    public $lang = null;
    public $question_id = null;
    public $phone = null;
    public $referrer = null;
    public $envelope_id = null; 
    public $envelope_sign = 0;
    public $envelope_send = 0;
    */

    protected array $answers = [];
    function get($chatId) {
        $this->init(db()->selectRow('SELECT * FROM ?_user WHERE chat_id = ?d', $chatId));
        if ($this->exists()) {
            $answers = db()->select('SELECT * FROM ?_answer WHERE user_id = ?d', $this->id);
            $this->answers = [];
            foreach ($answers as $answer) {
                $this->answers[$answer['question_id']] = new Answer($answer);
            }
        } else {
            $this->chat_id = $chatId;
        }
        return $this;
    }

    function getByEnvelopeId($envelopeId) {
        $this->init(db()->selectRow('SELECT * FROM ?_user WHERE envelope_id = ?d', $envelopeId));
        return $this;
    }

    function getList($limit, $offset = 0, $filter = []) {
        $s = \DBSIMPLE_SKIP;
        if (!empty($filter)) {
            $s = db()->subquery('WHERE (?&)', $filter);
        }
        $list = db()->select('SELECT id AS ARRAY_KEY1, u.* FROM ?_user u ?s ORDER BY id DESC LIMIT ?d OFFSET ?d', $s, $limit, $offset);
        $userIds = array_keys($list);
        if (!empty($userIds)) {
            $listAnswers = db()->select('SELECT user_id AS ARRAY_KEY1, question_id AS ARRAY_KEy2, a.* FROM ?_answer a WHERE user_id IN (?a)', $userIds);
        }
        foreach ($list as $userId => $user) {
            $list[$userId] = new User($user);
            if (!empty($listAnswers[$userId])) {
                foreach ($listAnswers[$userId] as $answer) {
                    $list[$userId]->addAnswer(new Answer($answer));
                }
            }
        }
        $count = db()->selectCell('SELECT COUNT(*) FROM ?_user ?s', $s);
        $all = db()->selectCell('SELECT COUNT(*) FROM ?_user');
        return (object)[
            'list' => $list,
            'count' => $count,
            'all' => $all
        ];

    }

    function addAnswer(Answer $a) {
        $this->answers[$a->question_id] = $a;
    }

    function getAnswers() {
        return $this->answers;
    }

    function save() {
        parent::save();
        foreach ($this->answers as &$answer) {
            $answer->user_id = $this->id;
            $answer->save();
        }
    }

    function setLang($text) {
        $text = trim($text);
        if (!in_array($text, ['en', 'ru'])) {
            throw new StateException("Language not set", StateException::WRONG_LANGUAGE);
        }
        $this->lang = $text;
    }

    function setReferrer($referrer) {
        $this->referrer = trim(strip_tags($referrer));
    }

    function setEnvelopeId($envelopeId) {
        $this->envelope_id = trim(strip_tags($envelopeId));
        if ($envelopeId) {
            $this->envelope_send = true;
        }
    }

    function setPhone($text) {
        $text = trim($text);
        if (!preg_match('#\d+#', $text)) {
            throw new StateException(
                lang($this->lang, 'Номер телефона задан некорректно', 'Incorrect phone number'),
                StateException::WRONG_PHONE);
        }
        $this->phone = $text;
    }

    function getPhone() {
        return $this->phone ?? '';
    }

    function setAnswers(array $answers) {
        $this->answers = $answers;
    }

    function setAnswer($questionId, $text) {
        $exists = $this->answers[$questionId] ?? null;
        $A = new Answer([
            'id' => $exists->id ?? null,
            'user_id' => $this->id,
            'question_id' => $questionId,
            'value' => trim($text),
            'created_at' => $exists->created_at ?? date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->addAnswer($A);
    }
}

