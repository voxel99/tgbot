<?php

namespace jam\app\utils;

class PipeFieldKey {
    private static function getFieldKey ($name, $cbAllFields, $cbAddField): string {
        $add = true;
        $key = '';
        $fields = $cbAllFields();
        foreach ($fields->data as $field) {
            if ($field->name === $name) {
                $add = false;
                $key = $field->key;
                break;
            }
        }
        if ($add) {
            $field = $cbAddField($name);
            $key = $field->data->key;
        }
        verb('Field [%s] %s. Key = %s', $name, $add ? 'added' : 'found', $key);
        return $key;
    }

    static function getItemsKeyBy ($key, $cbAllItems): array {
        $items = $cbAllItems();
        $ret = [];
        if (!empty($items->data)) {
            foreach ($items->data as $item) {
                $ret[$item->$key] = $item;
            }
        }
        return $ret;
    }

    static function getPersonChatIdFieldKey(): string {
        return self::getFieldKey(
            'Chat ID',
            function () {
                return PipeApi::personFields()->getAllPersonFields([]);
            },
            function ($name) {
                return PipeApi::personFields()->addANewPersonField([
                    'key' => 'chat_id',
                    'name' => $name,
                    'field_type' => 'varchar'
                ]);
            }
        );
    }

    static function getPersonOtherTextFieldKey($key, $name): string {
        return self::getFieldKey(
            $name,
            function () {
                return PipeApi::personFields()->getAllPersonFields([]);
            },
            function () use ($key, $name) {
                return PipeApi::personFields()->addANewPersonField([
                    'key' => $key,
                    'name' => $name,
                    'field_type' => 'varchar'
                ]);
            }
        );
    }

    static function getDealChatIdFieldKey(): string {
        return self::getFieldKey(
            'Chat ID',
            function () {
                return PipeApi::dealFields()->getAllDealFields([]);
            }, function ($name) {
                return PipeApi::dealFields()->addANewDealField([
                    'key' => 'chat_id',
                    'name' => $name,
                    'field_type' => 'varchar'
                ]);
            }
        );
    }
}