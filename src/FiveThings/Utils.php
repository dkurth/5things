<?php

namespace FiveThings;

class Utils {

    public static function isAre($str) {
        $str = trim($str);
        return substr($str, -1) == "s" ? "are" : "is";
    }

    public static function aAn($str) {
        $aAn = preg_match("/^[aeiou]/", $str) ? ' an ' : ' a ';
        if (preg_match("/^[A-Z]/", $str)) {
            $aAn = ' '; // proper nouns
        }
        return $aAn;
    }
}