<?php

namespace jam\app\tg\state;

use Jam\Models\ModelList;

/**
 * @property int|null $id
 * @property int|null $chat_id
 * @property string|null $lang
 * @property int|null $question_id
 * @property string|null $phone
 * @property string|null $referrer
 * @property string|null $envelope_id
 * @property int|null $envelope_sign
 * @property int|null $envelope_send
 * @property int|null $pipedrive_id
 * @property ModelList<Answer>|null $answers
 */
class User extends Model {
    protected string $fields = 'id, chat_id, lang, question_id, phone, referrer, envelope_id, envelope_sign, envelope_send, pipedrive_id';

    protected $types = [
        'int' => 'id, chat_id, question_id, envelope_sign, envelope_send, pipedrive_id',
    ];

    public function __construct (mixed $data = null) {
        $this->hasMany(Answer::class, 'answers', 'id', 'user_id');
        parent::__construct($data);
    }

    /**
     * Пользователь по chat_id вместе с ответами. Если записи нет — несохранённая модель с этим chat_id.
     */
    public static function findByChatId (int $chatId): static {
        $user = static::instance()->with('answers')->where(['chat_id' => $chatId])->first();
        if (!$user->exists()) {
            $user = new static(['chat_id' => $chatId]);
        }
        return $user;
    }

    public static function findByEnvelopeId (string $envelopeId): static {
        return static::instance()->where(['envelope_id' => $envelopeId])->first();
    }

    /**
     * Страница списка пользователей (свежие сверху) с ответами
     *
     * @param array<string, mixed> $filter Условия на поля пользователя: [поле => значение]
     * @return object{list: ModelList<User>, count: int, all: int}
     */
    public static function getList (int $limit, int $offset = 0, array $filter = []): object {
        $filter = array_intersect_key($filter, array_flip(static::instance()->getFields()));
        $query = static::instance()->where($filter);
        $count = $query->count();
        $list = $query->with('answers')->orderBy('id DESC')->limit($limit)->offset($offset)->collection();
        return (object)[
            'list' => $list,
            'count' => $count,
            'all' => static::instance()->count(),
        ];
    }

    /**
     * Ответы пользователя по ID вопроса
     * @return array<int, Answer>
     */
    public function getAnswers (): array {
        $answers = [];
        foreach ($this->answers ?? [] as $answer) {
            $answers[$answer->question_id] = $answer;
        }
        return $answers;
    }

    public function addAnswer (Answer $a): void {
        $answers = $this->getAnswers();
        $answers[$a->question_id] = $a;
        $this->setAnswers($answers);
    }

    /**
     * @param array<int, Answer> $answers
     */
    public function setAnswers (array $answers): void {
        $this->answers = new ModelList(Answer::class, array_values($answers));
    }

    public function setAnswer (int $questionId, string $text): void {
        $exists = $this->getAnswers()[$questionId] ?? null;
        $now = static::freshTimestamp();
        $this->addAnswer(new Answer([
            'id' => $exists?->id,
            'user_id' => $this->id,
            'question_id' => $questionId,
            'value' => trim($text),
            'created_at' => $exists?->created_at ?? $now,
            'updated_at' => $now,
        ]));
    }

    public function setLang (string $text): void {
        $text = trim($text);
        if (!in_array($text, ['en', 'ru'])) {
            throw new StateException("Language not set", StateException::WRONG_LANGUAGE);
        }
        $this->lang = $text;
    }

    public function setReferrer (string $referrer): void {
        $this->referrer = trim(strip_tags($referrer));
    }

    public function setEnvelopeId (string $envelopeId): void {
        $this->envelope_id = trim(strip_tags($envelopeId));
        if ($envelopeId) {
            $this->envelope_send = 1;
        }
    }

    public function setPhone (string $text): void {
        $text = trim($text);
        if (!preg_match('#\d+#', $text)) {
            throw new StateException(
                lang($this->lang, 'Номер телефона задан некорректно', 'Incorrect phone number'),
                StateException::WRONG_PHONE);
        }
        $this->phone = $text;
    }

    public function getPhone (): string {
        return $this->phone ?? '';
    }
}
