<?php

namespace jam\app\tg\state;

class Answer extends Model {
    public ?int $user_id = null;
    public ?int $question_id = null;
    public ?string $value = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}

