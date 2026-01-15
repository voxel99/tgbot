<?php
namespace jam\engine\utils;
class Strings {
    public static function pairValue (string $line, string $notPairValue = '', string $delim = '='): array {
        $ret = [];
        if (!str_contains($line, $delim)) {
            $ret[trim($line)] = $notPairValue;
        } else {
            list($k, $v) = explode($delim, $line);
            $ret[trim($k)] = trim($v);
        }
        return $ret;
    }
}