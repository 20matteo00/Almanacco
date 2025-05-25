<?php
$stagione = '1_2018';
$input = 'file.yaml';

$annobase = explode("_", $stagione)[1];
$annofine = $annobase + 1;
$map = [
    'Inter' => 1,
    'Juventus' => 2,
    'Roma' => 3,
    'Milan' => 4,
    'Fiorentina' => 5,
    'Lazio' => 6,
    'Torino' => 7,
    'Napoli' => 8,
    'Bologna' => 9,
    'Sampdoria' => 10,
    'Atalanta' => 11,
    'Genoa' => 12,
    'Udinese' => 13,
    'Cagliari' => 14,
    'Verona' => 15,
    'Bari' => 16,
    'Vicenza' => 17,
    'Palermo' => 18,
    'Parma' => 19,
    'Triestina' => 20,
    'Brescia' => 21,
    'Lecce' => 22,
    'Spal' => 23,
    'Livorno' => 24,
    'Catania' => 25,
    'Chievo' => 26,
    'Empoli' => 27,
    'Ascoli' => 28,
    'Padova' => 29,
    'Como' => 30,
    'Venezia' => 31,
    'Alessandria' => 32,
    'Cesena' => 33,
    'Modena' => 34,
    'Novara' => 35,
    'Perugia' => 36,
    'Pro Patria' => 37,
    'Sassuolo' => 38,
    'Foggia' => 39,
    'Avellino' => 40,
    'Reggina' => 41,
    'Siena' => 42,
    'Cremonese' => 43,
    'Lucchese' => 44,
    'Piacenza' => 45,
    'Sampierdarenese' => 46,
    'Catanzaro' => 47,
    'Mantova' => 48,
    'Pescara' => 49,
    'Pisa' => 50,
    'Varese' => 51,
    'Pro Vercelli' => 52,
    'Messina' => 53,
    'Salernitana' => 54,
    'Casale' => 55,
    'Crotone' => 56,
    'Frosinone' => 57,
    'Lecco' => 58,
    'Legnano' => 59,
    'Monza' => 60,
    'Reggiana' => 61,
    'Spezia' => 62,
    'Ancona' => 63,
    'Benevento' => 64,
    'Ternana' => 65,
    'Carpi' => 66,
    'Pistoiese' => 67,
    'Treviso' => 68,
    'Taranto' => 69,
    'Cosenza' => 70,
    'Sambenedettese' => 71,
    'Cittadella' => 72,
    'Arezzo' => 73,
    'Fanfulla' => 74,
    'Vigevano' => 75,
    'Marzotto Valdagno' => 76,
    'Prato' => 77,
    'AlbinoLeffe' => 78,
    'Rimini' => 79,
    'Ravenna' => 80,
    'Siracusa' => 81,
    'Brindisi' => 82,
    'Fidelis Andria' => 83,
    'Grosseto' => 84,
    'Juve Stabia' => 85,
    'Seregno' => 86,
    'Viareggio' => 87,
    'Virtus Entella' => 88,
    'Campobasso' => 89,
    'Potenza' => 90,
    'Savona' => 91,
    'Trapani' => 92,
    'Barletta' => 93,
    'Latina' => 94,
    'Monfalcone' => 95,
    'Pavia' => 96,
    'Pro Sesto' => 97,
    'Virtus Lanciano' => 98,
    'Carrarese' => 99,
    'Cavese' => 100,
    'Derthona' => 101,
    'Grion Pola' => 102,
    'L’Aquila' => 103,
    'Nocerina' => 104,
    'Piombino' => 105,
    'Pordenone' => 106,
    'Sanremese' => 107,
    'Savoia' => 108,
    'Südtirol' => 109,
    'Acireale' => 110,
    'Biellese' => 111,
    'Casertana' => 112,
    'Castel di Sangro' => 113,
    'Crema' => 114,
    'La Dominante' => 115,
    'Fiumana' => 116,
    'Gallaratese' => 117,
    'Gubbio' => 118,
    'Licata' => 119,
    'Pro Gorizia' => 120,
    'Rieti' => 121,
    'Scafatese' => 122,
    'Suzzara' => 123,
    'Trani' => 124,
    'Vogherese' => 125,
    'Albatrastevere' => 126,
    'Alzano Virescit' => 127,
    'Arsenale Taranto' => 128,
    'Bolzano' => 129,
    'Centese' => 130,
    'Feralpisalò' => 131,
    'Fermana' => 132,
    'Forlì' => 133,
    'Gallipoli' => 134,
    'Maceratese' => 135,
    'Magenta' => 136,
    'Massese' => 137,
    'MATER' => 138,
    'Matera' => 139,
    'Mestre' => 140,
    'Molinella' => 141,
    'Portogruaro' => 142,
    'Sestrese' => 143,
    'Sorrento' => 144,
    'Vita Nova' => 145,
];

$lines = file($input, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false)
    die("Errore lettura file");

$giornata = 0;
$lastDate = null;

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

$giornata = 0;

foreach ($lines as $line) {
    $trim = trim($line);

    // Sezione nuova giornata (andata/ritorno)
    if (preg_match('/^\d+\s+(andata|ritorno)/i', $trim)) {
        $giornata++;
        continue;
    }
    // Intestazione colonna
    if (stripos($trim, 'Data') === 0) continue;

    // Riga risultato: dd.mm.yyyy<tab>Home-Away<tab>x-y
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})\s+(.+?)-(.+?)\s+(\d+)-(\d+)/', $trim, $m)) {
        list(, $dd, $mm, $yyyy, $home, $away, $gH, $gA) = $m;
        $date = sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd);
        $homeId = $map[$home] ?? null;
        $awayId = $map[$away] ?? null;
        if (!$homeId || !$awayId) {
            trigger_error("Squadra non trovata: $home o $away", E_USER_WARNING);
            continue;
        }
        // Stampa INSERT
        printf(
            "INSERT IGNORE INTO `partite` (`stagione_id`,`giornata`,`squadra_casa_id`,`squadra_trasferta_id`,`gol_casa`,`gol_trasferta`,`data_partita`) VALUES ('%s', %d, %d, %d, %d, %d, '%s');<br>",
            $stagione, $giornata, $homeId, $awayId, (int)$gH, (int)$gA, $date
        );
    }
}
?>
