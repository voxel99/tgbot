<?php

namespace jam\app\tg\state;

class UserLog extends Model {
    /*
    public $id = null;
    public $user_id = null;
    public $change_date = null;
    public $prop_name = null;
    public $old_value = null;
    public $new_value = null;
    public $ip = null;
    */

    function add ($userId, $propName, $oldValue, $newValue, $ip) {
        $this->id = null;
        $this->user_id = $userId;
        $this->change_date = date('Y-m-d H:i:s');
        $this->prop_name = $propName;
        $this->old_value = $oldValue;
        $this->new_value = $newValue;
        $this->ip = $ip;

        $this->save();
    }

    function getList ($userId, $propName = null) {
        return db()->select('SELECT * FROM ?_user_log WHERE user_id = ?d {AND prop_name = ?} ORDER BY id DESC LIMIT 100', $userId, $propName ?: DBSIMPLE_SKIP);
    }



}

