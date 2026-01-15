<?php

function lang(?string $lang, string $ru, string $en): string {
    $q = [
        'ru' => $ru,
        'en' => $en
    ];
    return $q[$lang] ?? implode("\n\n", $q);
}