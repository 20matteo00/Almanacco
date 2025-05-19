<?php

class Helper
{
    public $menu = [
        'competitions',
        'seasons'
    ];

    public $menu_competitions = [
        'seasons_list',
        'participating_teams',
        'direct_clashes',
    ];

    public $lang = [
        'it',
        'en',
    ];

    public function loadLanguage($langCode = 'it')
    {
        $path = "Language/$langCode.json";

        if (!file_exists($path)) {
            return []; // oppure lancia un errore
        }

        $json = file_get_contents($path);
        return json_decode($json, true);
    }

    public function getTranslation($key, $langfile)
    {
        return isset($langfile[$key]) ? $langfile[$key] : $key;
    }
}
