<?php

class Helper
{
    protected $db;
    public $menu = [
        'competitions',
        'seasons'
    ];

    public $menu_competitions = [
        'seasons_list',
        'participating_teams',
        'direct_clashes',
    ];

    public $menu_seasons = [
        'matches',
        'ranking',
        'statistics',
    ];

    public $lang = [
        'it',
        'en',
    ];

    public function __construct($db)
    {
        $this->db = $db;
    }

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

    function getTeamsPartecipant($id)
    {
        // Prendo tutte le stagioni di questa competizione
        $teams = $this->db->getAll("stagioni", '*', "competizione_id = ?", [$_GET['comp_id']]);
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
            $r = $this->db->getOne("squadre", "id = ?", [$id]);
            if (!$r)
                continue;
            sort($anni);
            $rows[] = [
                'nome' => $r['nome'],
                'anni' => $anni,
                'id' => $id,
                'params' => $r['params']
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

    function getTeamNameByID($id)
    {
        $row = $this->db->getOne("squadre", "id = ?", [$id]);
        return $row ? $row['nome'] : null;
    }

    function getParamsbyID($id, $table, $cod = "id"){
        $r = $this->db->getOne("{$table}", "{$cod} = ?", [$id]);
        return $r['params'];
    }

    function getClassifica($partite)
    {
        $classifica = [];

        foreach ($partite as $p) {
            $casa = $p['squadra_casa_id'];
            $trasferta = $p['squadra_trasferta_id'];
            $golCasa = $p['gol_casa'];
            $golTrasferta = $p['gol_trasferta'];

            // Inizializzazione squadre se non presenti
            foreach ([$casa, $trasferta] as $squadra) {
                if (!isset($classifica[$squadra])) {
                    $classifica[$squadra] = [
                        'squadra_id' => $squadra,
                        'giocate' => 0,
                        'vittorie' => 0,
                        'pareggi' => 0,
                        'sconfitte' => 0,
                        'gol_fatti' => 0,
                        'gol_subiti' => 0,
                        'diff_reti' => 0,
                        'punti' => 0,
                    ];
                }
            }

            // Aggiornamento statistiche
            $classifica[$casa]['giocate']++;
            $classifica[$trasferta]['giocate']++;
            $classifica[$casa]['gol_fatti'] += $golCasa;
            $classifica[$casa]['gol_subiti'] += $golTrasferta;
            $classifica[$trasferta]['gol_fatti'] += $golTrasferta;
            $classifica[$trasferta]['gol_subiti'] += $golCasa;

            // Calcolo della differenza reti separatamente
            $diffCasa = $golCasa - $golTrasferta;
            $diffTrasferta = $golTrasferta - $golCasa;

            // Aggiorno la differenza reti
            $classifica[$casa]['diff_reti'] += $diffCasa;
            $classifica[$trasferta]['diff_reti'] += $diffTrasferta;

            if ($golCasa > $golTrasferta) {
                // Casa vince
                $classifica[$casa]['vittorie']++;
                $classifica[$trasferta]['sconfitte']++;
                $classifica[$casa]['punti'] += 3;
            } elseif ($golCasa < $golTrasferta) {
                // Trasferta vince
                $classifica[$trasferta]['vittorie']++;
                $classifica[$casa]['sconfitte']++;
                $classifica[$trasferta]['punti'] += 3;
            } else {
                // Pareggio
                $classifica[$casa]['pareggi']++;
                $classifica[$trasferta]['pareggi']++;
                $classifica[$casa]['punti'] += 1;
                $classifica[$trasferta]['punti'] += 1;
            }
        }

        // Ordinamento classifica
        usort($classifica, function ($a, $b) {
            if ($a['punti'] != $b['punti'])
                return $b['punti'] - $a['punti'];
            if ($a['diff_reti'] != $b['diff_reti'])
                return $b['diff_reti'] - $a['diff_reti'];
            return $b['gol_fatti'] - $a['gol_fatti'];
        });

        return $classifica;
    }

}
