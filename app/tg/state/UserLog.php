<?php

namespace jam\app\tg\state;

class UserLog extends Model {
    public ?int $user_id = null;
    public ?string $change_date = null;
    public ?string $prop_name = null;
    public ?string $old_value = null;
    public ?string $new_value = null;
    public ?string $ip = null;

    public function add (int $userId, string $propName, ?string $oldValue, ?string $newValue, string $ip): void {
        $this->id = null;
        $this->user_id = $userId;
        $this->change_date = date('Y-m-d H:i:s');
        $this->prop_name = $propName;
        $this->old_value = $oldValue;
        $this->new_value = $newValue;
        $this->ip = $ip;

        $this->save();
    }

    public function getList (int $userId, ?string $propName = null): array {
        return db()->select('SELECT * FROM ?_user_log WHERE user_id = ?d {AND prop_name = ?} ORDER BY id DESC LIMIT 100', $userId, $propName ?: DBSIMPLE_SKIP);
    }
}

