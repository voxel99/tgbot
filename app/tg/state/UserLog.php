<?php

namespace jam\app\tg\state;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $change_date
 * @property string|null $prop_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $ip
 */
class UserLog extends Model {
    protected string $fields = 'id, user_id, change_date, prop_name, old_value, new_value, ip';

    protected $types = [
        'int' => 'id, user_id',
    ];

    public static function add (int $userId, string $propName, ?string $oldValue, ?string $newValue, string $ip): static {
        $log = new static([
            'user_id' => $userId,
            'change_date' => static::freshTimestamp(),
            'prop_name' => $propName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip' => $ip,
        ]);
        $log->save();
        return $log;
    }

    public static function getList (int $userId, ?string $propName = null): array {
        $query = static::instance()->where(['user_id' => $userId]);
        if ($propName) {
            $query->where(['prop_name' => $propName]);
        }
        return $query->orderBy('id DESC')->limit(100)->collection()->toArray();
    }
}
