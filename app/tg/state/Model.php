<?php

namespace jam\app\tg\state;

/**
 * Базовая модель проекта поверх jam/dbsimple-models
 */
abstract class Model extends \Jam\Models\Model {
    /**
     * Запись сохранена в БД (в отличие от isExists(), который учитывает любые заполненные поля)
     */
    public function exists (): bool {
        return !empty($this->{$this->pk()});
    }
}
