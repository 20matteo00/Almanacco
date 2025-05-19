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

    function getTeamsPartecipant($db, $id)
    {
        // Prendo tutte le stagioni di questa competizione
        $teams = $db->getAll("stagioni", '*', "competizione_id = ?", [$_GET['comp_id']]);
        $squadreConAnni = [];

        // Costruisco l’array id_squadra => [anni...]
        foreach ($teams as $team) {
            $anno = $team['anno'];
            $ids = json_decode($team['squadre'], true);

            foreach ($ids as $id) {
                if (!isset($squadreConAnni[$id])) {
                    $squadreConAnni[$id] = [];
                }
                // evito duplicati
                if (!in_array($anno, $squadreConAnni[$id], true)) {
                    $squadreConAnni[$id][] = $anno;
                }
            }
        }

        // preparo i dati ordinati per nome
        $rows = [];
        foreach ($squadreConAnni as $id => $anni) {
            $r = $db->getOne("squadre", "id = ?", [$id]);
            if (!$r) continue;
            sort($anni);
            $rows[] = [
                'nome' => $r['nome'],
                'anni' => $anni,
                'id'   => $id
            ];
        }
        usort($rows, fn($a, $b) => strcasecmp($a['nome'], $b['nome']));
        return $rows;
    }

    function getTeamsNamebyCompetition(array $teamsData): array
    {
        $names = [];
        foreach ($teamsData as $team) {
            if (isset($team['nome'])) {
                $names[] = [
                    $team['nome'],
                    $team['id'],
                ];
            }
        }
        return $names;
    }

    function getTeamNameByID($id, $db)
    {
        $row = $db->getOne("squadre", "id = ?", [$id]);
        return $row ? $row['nome'] : null;
    }
}
