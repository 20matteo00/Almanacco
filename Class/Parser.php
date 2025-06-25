<?php

class Parser
{
    private string $page;

    private string $comp;
    private array $anni;

    public function __construct(string $page, string $comp)
    {
        $this->page = $page;
        $this->comp = $comp;
        $this->anni = $this->estraiAnni($page);
    }

    public function esegui(): void
    {
        $html = $this->scaricaHtmlWikipedia();
        if (!$html)
            return;

        $giornate = $this->estraiGiornatePartite($html);
        $filename = $this->salvaJson($giornate);
        $this->stampaRiepilogo($giornate, $filename);
    }

    private function estraiAnni(string $nomePagina): ?array
    {
        if (preg_match('/_(\d{4})-(\d{4})$/', $nomePagina, $m)) {
            return ['inizio' => (int) $m[1], 'fine' => (int) $m[2]];
        }
        return null;
    }

    private function scaricaHtmlWikipedia(): string|false
    {
        $url = "https://it.wikipedia.org/w/api.php?action=parse&prop=text&format=json&page={$this->page}";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'SerieA Parser 1.0',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            echo "Errore HTTP: $code<br>";
            return false;
        }
        $data = json_decode($response, true);
        return $data['parse']['text']['*'] ?? false;
    }

    private function pulisciTesto(string $s): string
    {
        return trim(preg_replace('/\[[^\]]*\]/u', '', $s));
    }

    private function normalizzaData(string $d): ?string
    {
        $mesi = [
            'gen' => '01',
            'feb' => '02',
            'mar' => '03',
            'apr' => '04',
            'mag' => '05',
            'giu' => '06',
            'lug' => '07',
            'ago' => '08',
            'set' => '09',
            'ott' => '10',
            'nov' => '11',
            'dic' => '12'
        ];
        $d = strtolower(trim(preg_replace('/[^a-z0-9\s]/iu', '', $d)));
        if (!preg_match('/^(\d{1,2})\s+([a-z]+)/u', $d, $m))
            return null;
        $g = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $ma = substr($m[2], 0, 3);
        if (!isset($mesi[$ma]))
            return null;
        $mm = $mesi[$ma];
        $yy = ($mm >= '08') ? $this->anni['inizio'] : $this->anni['fine'];
        return "$g/$mm/$yy";
    }

    private function estraiGiornatePartite(string $html): array
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        $xp = new DOMXPath($doc);
        $tables = $xp->query('//table[@width="99%"]');
        $out = [];

        foreach ($tables as $table) {
            $A = $R = null;
            $found = false;
            $trs = $xp->query('.//tr', $table);

            foreach ($trs as $tr) {
                $text = $this->pulisciTesto($tr->textContent);
                if (preg_match('/andata\s*\((\d{1,2})ª\).*?ritorno\s*\((\d{1,2})ª\)/i', $text, $m)) {
                    $A = (int) $m[1];
                    $R = (int) $m[2];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $tn = $xp->query('.//tr[1]/td[2]/b', $table);
                if (!$tn->length) {
                    continue;
                }
                $raw = trim($tn->item(0)->textContent);
                $giornateOrd = [
                    'prima' => 1,
                    'seconda' => 2,
                    'terza' => 3,
                    'quarta' => 4,
                    'quinta' => 5,
                    'sesta' => 6,
                    'settima' => 7,
                    'ottava' => 8,
                    'nona' => 9,
                    'decima' => 10,
                    'undicesima' => 11,
                    'dodicesima' => 12,
                    'tredicesima' => 13,
                    'quattordicesima' => 14,
                    'quindicesima' => 15,
                    'sedicesima' => 16,
                    'diciassettesima' => 17,
                    'diciottesima' => 18,
                    'diciannovesima' => 19,
                    'ventesima' => 20,
                    'ventunesima' => 21,
                ];

                $rawLower = mb_strtolower($raw);

                if (preg_match('/(\d{1,2})ª/', $rawLower, $m2)) {
                    $A = (int) $m2[1];
                    $R = $A + count($tables);
                } elseif (preg_match('/([a-zà]+)/u', $rawLower, $m3)) {
                    $nome = $m3[1];
                    if (isset($giornateOrd[$nome])) {
                        $A = $giornateOrd[$nome];
                        $R = $A + count($tables);
                    } else {
                        continue; // non riconosciuto
                    }
                } else {
                    continue;
                }

            }

            // ciclo righe partite
            $ultimaData = '';
            foreach ($xp->query('.//tr[position()>2]', $table) as $riga) {
                $cells = [];
                foreach ($xp->query('td', $riga) as $td) {
                    $t = $this->pulisciTesto($td->textContent);
                    if ($t !== '')
                        $cells[] = $t;
                }
                if (count($cells) < 2)
                    continue;

                $tokens = [];
                foreach ($cells as $c) {
                    if ($this->normalizzaData($c)) {
                        $tokens[] = ['type' => 'date', 'raw' => $c, 'norm' => $this->normalizzaData($c)];
                    } elseif (preg_match('/^\d+-\d+$/', $c)) {
                        $tokens[] = ['type' => 'score', 'raw' => $c];
                    } elseif (strpos($c, '-') !== false) {
                        $tokens[] = ['type' => 'match', 'raw' => $c];
                    }
                }
                if (empty($tokens))
                    continue;

                if ($tokens[0]['type'] === 'date') {
                    $ultimaData = $tokens[0]['norm'];
                    array_shift($tokens);
                }

                if (count($tokens) >= 2 && $tokens[0]['type'] === 'score' && $tokens[1]['type'] === 'match') {
                    list($g1A, $g2A) = array_map('intval', explode('-', $tokens[0]['raw'], 2));
                    list($q1A, $q2A) = array_map('trim', explode('-', $tokens[1]['raw'], 2));
                    $out[$A][] = [
                        'data' => $ultimaData,
                        'squadra1' => $q1A,
                        'squadra2' => $q2A,
                        'gol1' => $g1A,
                        'gol2' => $g2A,
                    ];
                    $tokens = array_slice($tokens, 2);
                    if (!empty($tokens) && $tokens[0]['type'] === 'score') {
                        list($g1R, $g2R) = array_map('intval', explode('-', $tokens[0]['raw'], 2));
                        $dtR = $ultimaData;
                        if (isset($tokens[1]) && $tokens[1]['type'] === 'date') {
                            $dtR = $tokens[1]['norm'];
                        }
                        $out[$R][] = [
                            'data' => $dtR,
                            'squadra1' => $q2A,
                            'squadra2' => $q1A,
                            'gol1' => $g2R,
                            'gol2' => $g1R,
                        ];
                    }
                } elseif (count($tokens) >= 2 && $tokens[0]['type'] === 'match' && $tokens[1]['type'] === 'score') {
                    list($q1, $q2) = array_map('trim', explode('-', $tokens[0]['raw'], 2));
                    list($g1, $g2) = array_map('intval', explode('-', $tokens[1]['raw'], 2));
                    $out[$A][] = [
                        'data' => $ultimaData,
                        'squadra1' => $q1,
                        'squadra2' => $q2,
                        'gol1' => $g1,
                        'gol2' => $g2,
                    ];
                }
            }
        }

        ksort($out, SORT_NUMERIC);
        return $out;
    }

    private function salvaJson(array $giornate): string
    {
        $fn = 'Json/' . $this->comp . '/' . $this->page . '.json';
        file_put_contents($fn, json_encode($giornate, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $fn;
    }

    private function stampaRiepilogo(array $g, string $f): void
    {
        $tot = array_sum(array_map('count', $g));
        echo "✅ File JSON salvato: $f<br>";
        echo "📅 Giornate trovate: " . count($g) . "<br>";
        echo "⚽ Partite totali: $tot<br>";
    }
}
