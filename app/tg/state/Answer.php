<?php

namespace jam\app\tg\state;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property int|null $question_id
 * @property string|null $value
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Answer extends Model {
    protected string $fields = 'id, user_id, question_id, value, created_at, updated_at';

    protected $types = [
        'int' => 'id, user_id, question_id',
    ];
}
