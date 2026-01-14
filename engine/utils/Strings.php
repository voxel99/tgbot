<?php
namespace jam\engine\utils;
class Strings {
    static function pairValue ($line, $notPairValue = '', $delim = '=') {
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