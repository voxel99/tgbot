<?php

namespace jam\app\tg\state;

use jam\engine\utils\CamelCase;
use jam\engine\utils\Code;
use ReflectionClass;
use ReflectionProperty;

class Model {
    public ?int $id = null;

    function __construct (array $data = []) {
        $this->init($data);
    }

    public function init (array $arr): void {
        foreach ($arr as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function merge (array $arr): void {
        foreach ($arr as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function exists (): bool {
        return !empty($this->id);
    }

    public function table (): string {
        return strtolower(CamelCase::from(Code::classBasename($this)));
    }

    protected function getProperties (): array {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->$name;
            if ($value !== null) {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    public function save (): int {
        $data = $this->getProperties();

        if ($this->exists()) {
            $upd = $data;
            unset($upd['id']);
            db()->query(sprintf('UPDATE ?_%s SET ?a WHERE id = ?d', $this->table()),
                $upd,
                $this->id
            );
        } else {
            unset($data['id']);
            $this->id = db()->query(sprintf('INSERT INTO ?_%s (?#) VALUES (?a)', $this->table()),
                array_keys($data),
                array_values($data)
            );
        }
        return $this->id;
    }
}

