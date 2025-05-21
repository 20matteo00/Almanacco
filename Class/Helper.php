<?php

class Helper
{
    protected $db;
    public $menu = [
        'competitions',
        'seasons',
        'teams'
    ];

    public $menu_competitions = [
        'seasons_list',
        'participating_teams',
        'direct_clashes',
        'champions',
        'all_time_table'
    ];

    public $menu_seasons = [
        'matches',
        'table',
        'scoreboard',
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

    function getParamsbyID($id, $table, $cod = "id")
    {
        $r = $this->db->getOne("{$table}", "{$cod} = ?", [$id]);
        return $r['params'];
    }

    function createTeam(string $sfondo, string $testo, string $bordo): string
    {
        // Nota: htmlspecialchars non serve qui perché usiamo solo colori validi (#xxxxxx)
        return "background-color: {$sfondo} !important; "
            . "color: {$testo} !important; "
            . "border: 3px solid {$bordo} !important;";
    }

    function getClassifica($partite, $ext = '')
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
                        'giocate_c' => 0,
                        'vittorie_c' => 0,
                        'pareggi_c' => 0,
                        'sconfitte_c' => 0,
                        'gol_fatti_c' => 0,
                        'gol_subiti_c' => 0,
                        'diff_reti_c' => 0,
                        'punti_c' => 0,
                        'giocate_t' => 0,
                        'vittorie_t' => 0,
                        'pareggi_t' => 0,
                        'sconfitte_t' => 0,
                        'gol_fatti_t' => 0,
                        'gol_subiti_t' => 0,
                        'diff_reti_t' => 0,
                        'punti_t' => 0,
                    ];
                }
            }

            // Aggiornamento statistiche
            $classifica[$casa]['giocate_c']++;
            $classifica[$trasferta]['giocate_t']++;
            $classifica[$casa]['gol_fatti_c'] += $golCasa;
            $classifica[$casa]['gol_subiti_c'] += $golTrasferta;
            $classifica[$trasferta]['gol_fatti_t'] += $golTrasferta;
            $classifica[$trasferta]['gol_subiti_t'] += $golCasa;

            // Calcolo della differenza reti separatamente
            $diffCasa = $golCasa - $golTrasferta;
            $diffTrasferta = $golTrasferta - $golCasa;

            // Aggiorno la differenza reti
            $classifica[$casa]['diff_reti_c'] += $diffCasa;
            $classifica[$trasferta]['diff_reti_t'] += $diffTrasferta;

            if ($golCasa > $golTrasferta) {
                // Casa vince
                $classifica[$casa]['vittorie_c']++;
                $classifica[$trasferta]['sconfitte_t']++;
                $classifica[$casa]['punti_c'] += 3;
            } elseif ($golCasa < $golTrasferta) {
                // Trasferta vince
                $classifica[$trasferta]['vittorie_t']++;
                $classifica[$casa]['sconfitte_c']++;
                $classifica[$trasferta]['punti_t'] += 3;
            } else {
                // Pareggio
                $classifica[$casa]['pareggi_c']++;
                $classifica[$trasferta]['pareggi_t']++;
                $classifica[$casa]['punti_c'] += 1;
                $classifica[$trasferta]['punti_t'] += 1;
            }

            // Calcolo totale per ciascuna squadra (casa + trasferta)
            $classifica[$casa]['giocate'] = $classifica[$casa]['giocate_c'] + $classifica[$casa]['giocate_t'];
            $classifica[$casa]['punti'] = $classifica[$casa]['punti_c'] + $classifica[$casa]['punti_t'];
            $classifica[$casa]['vittorie'] = $classifica[$casa]['vittorie_c'] + $classifica[$casa]['vittorie_t'];
            $classifica[$casa]['pareggi'] = $classifica[$casa]['pareggi_c'] + $classifica[$casa]['pareggi_t'];
            $classifica[$casa]['sconfitte'] = $classifica[$casa]['sconfitte_c'] + $classifica[$casa]['sconfitte_t'];
            $classifica[$casa]['gol_fatti'] = $classifica[$casa]['gol_fatti_c'] + $classifica[$casa]['gol_fatti_t'];
            $classifica[$casa]['gol_subiti'] = $classifica[$casa]['gol_subiti_c'] + $classifica[$casa]['gol_subiti_t'];
            $classifica[$casa]['diff_reti'] = $classifica[$casa]['diff_reti_c'] + $classifica[$casa]['diff_reti_t'];

            // Totale della squadra in trasferta
            $classifica[$trasferta]['giocate'] = $classifica[$trasferta]['giocate_c'] + $classifica[$trasferta]['giocate_t'];
            $classifica[$trasferta]['punti'] = $classifica[$trasferta]['punti_c'] + $classifica[$trasferta]['punti_t'];
            $classifica[$trasferta]['vittorie'] = $classifica[$trasferta]['vittorie_c'] + $classifica[$trasferta]['vittorie_t'];
            $classifica[$trasferta]['pareggi'] = $classifica[$trasferta]['pareggi_c'] + $classifica[$trasferta]['pareggi_t'];
            $classifica[$trasferta]['sconfitte'] = $classifica[$trasferta]['sconfitte_c'] + $classifica[$trasferta]['sconfitte_t'];
            $classifica[$trasferta]['gol_fatti'] = $classifica[$trasferta]['gol_fatti_c'] + $classifica[$trasferta]['gol_fatti_t'];
            $classifica[$trasferta]['gol_subiti'] = $classifica[$trasferta]['gol_subiti_c'] + $classifica[$trasferta]['gol_subiti_t'];
            $classifica[$trasferta]['diff_reti'] = $classifica[$trasferta]['diff_reti_c'] + $classifica[$trasferta]['diff_reti_t'];


        }

        $sortFields = ['punti', 'diff_reti', 'gol_fatti'];
        usort($classifica, function ($a, $b) use ($ext, $sortFields) {
            foreach ($sortFields as $field) {
                $key = $field . $ext;
                if ($a[$key] !== $b[$key]) {
                    return $b[$key] <=> $a[$key];
                }
            }
            return 0;
        });


        return $classifica;
    }

    /**
     * Per ogni metrica (vittorie, pareggi, …) e ambito ('', '_c', '_t'),
     * calcola valore min e max e quali squadre li hanno ottenuti.
     *
     * @param array $classifica Array di squadre da getClassifica()
     * @return array 
     */
    function getStatistics(array $classifica): array
    {
        $result = ['min' => [], 'max' => []];
        if (empty($classifica)) return $result;
        $metrics = [
            'vittorie',
            'pareggi',
            'sconfitte',
            'gol_fatti',
            'gol_subiti',
            'diff_reti'
        ];
        $scopes = [
            '' => '',    // totale
            '_c' => '_c',  // casa
            '_t' => '_t',  // trasferta
        ];

        

        // Inizializza con valori estremi e squadre vuote
        foreach ($scopes as $suffix) {
            foreach ($metrics as $metric) {
                $key = $metric . $suffix;
                $result['min'][$key] = ['value' => PHP_INT_MAX, 'teams' => []];
                $result['max'][$key] = ['value' => PHP_INT_MIN, 'teams' => []];
            }
        }

        // Scorri tutte le squadre
        foreach ($classifica as $teamData) {
            $teamId = $this->getTeamNameByID($teamData['squadra_id']);
            foreach ($scopes as $suffix) {
                foreach ($metrics as $metric) {
                    $key = $metric . $suffix;
                    $value = $teamData[$key];

                    // Minimo
                    if ($value < $result['min'][$key]['value']) {
                        $result['min'][$key]['value'] = $value;
                        $result['min'][$key]['teams'] = [$teamId];
                    } elseif ($value === $result['min'][$key]['value']) {
                        $result['min'][$key]['teams'][] = $teamId;
                    }

                    // Massimo
                    if ($value > $result['max'][$key]['value']) {
                        $result['max'][$key]['value'] = $value;
                        $result['max'][$key]['teams'] = [$teamId];
                    } elseif ($value === $result['max'][$key]['value']) {
                        $result['max'][$key]['teams'][] = $teamId;
                    }
                }
            }
        }

        return $result;
    }

    function getChampions(array $stagioni)
    {
        $winner = [];

        foreach ($stagioni as $s) {
            $params = json_decode($s['params']);
            $vincitore = $params->Vincitore ?? null;

            if ($vincitore) {
                if (!isset($winner[$vincitore])) {
                    $winner[$vincitore] = [
                        'Vittorie' => 1,
                        'Anni' => [$s['anno']."/".$s['anno']+1],
                    ];
                } else {
                    $winner[$vincitore]['Vittorie']++;
                    $winner[$vincitore]['Anni'][] = $s['anno']."/".$s['anno']+1;
                }
            }
        }

        // Ordina per numero di vittorie (descrescente)
        uasort($winner, function ($a, $b) {
            return $b['Vittorie'] <=> $a['Vittorie'];
        });

        return $winner;
    }


}
