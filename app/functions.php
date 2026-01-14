<?php

function lang($lang, $ru, $en) {
    $q = [
        'ru' => $ru,
        'en' => $en
    ];
    return $q[$lang] ?? implode("\n\n", $q);
}