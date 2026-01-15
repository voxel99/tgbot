<?php

namespace jam\engine\utils;

class CamelCase {
    /**
     * Конвертирует строку с подчёркиваниями в имя в венгерской нотации
     *
     * @param string $input Исходная строка
     * @param string $delimiter
     * @return string
     */
    public static function to (string $input, string $delimiter = "_"): string {
        $arr = explode($delimiter, $input);
        $ret = '';
        foreach ($arr as $value) {
            $ret .= ucfirst($value);
        }
        return $ret;
    }

    /**
     * Конвертирует строку в венгерской нотации в имя с подчёркиваниями
     *
     * @param string $input Исходная строка
     * @param string $delimiter
     * @return string
     */
    public static function from (string $input, string $delimiter = "_"): string {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode($delimiter, $ret);
    }
}