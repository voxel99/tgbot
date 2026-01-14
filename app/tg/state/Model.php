<?php

namespace jam\app\tg\state;

use jam\engine\utils\CamelCase;
use jam\engine\utils\Code;

class Model {
    protected $data = [];

    function __construct(array $data = []) {
        $this->init($data);
    }

    function __get($prop) {
        return $this->data[$prop] ?? null;
    }

    function __set($prop, $value) {
        $this->data[$prop] = $value;
    }

    function init(array $arr) {
        $this->data = $arr;
    }

    function merge(array $arr) {
        $this->data = array_merge($this->data, $arr);
    }

    function exists() {
        return !empty($this->data['id']);
    }

    function table() {
        return strtolower(CamelCase::from(Code::classBasename($this)));
    }

    function save() {
        if ($this->exists()) {
            $upd = $this->data;
            unset($upd['id']);
            db()->query(sprintf('UPDATE ?_%s SET ?a WHERE id = ?d', $this->table()),
                $upd,
                $this->id
            );
        } else {
            $this->id = db()->query(sprintf('INSERT INTO ?_%s (?#) VALUES (?a)', $this->table()),
                array_keys($this->data),
                array_values($this->data)
            );
        }
        return $this->id;
    }
}

