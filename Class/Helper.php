<?php

class Helper
{
    public $menu = [
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
}
