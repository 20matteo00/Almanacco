<?php
// --------------------------------------------------
// COMPETITIONS PAGE
// --------------------------------------------------

if (!isset($_GET['season_id'])) {
    header("Location: ?page=competitions");
    exit;
}

// Parametro tab attivo (default seasons_list)
$activeTab = $_GET['tab'] ?? 'matches';


// Funzione di rendering
function generate($tab, $help, $langfile, $db)
{
    $partite = $db->getAll("partite", '*', 'stagione_id = ?', [$_GET['season_id']], 'giornata ASC, data_partita ASC');
    $classifica = $help->getClassifica($partite);
    switch ($tab) {
        case 'table':
            ?>
            <div class="table-responsive mytable">
                <table class="table table-hover table-striped align-middle text-center">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>#</th>
                            <th><?= $help->getTranslation('team', $langfile) ?></th>
                            <th>Pt</th>
                            <th>G</th>
                            <th>V</th>
                            <th>N</th>
                            <th>P</th>
                            <th>GF</th>
                            <th>GS</th>
                            <th>DR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pos = 1;
                        $season_params = json_decode($help->getParamsbyID($_GET['season_id'], "stagioni", "codice_stagione"));
                        $promo = $season_params->Promozione;
                        $retro = $season_params->Retrocessione;
                        $totsquadre = count($classifica);
                        $postoretro = $totsquadre - $retro;
                        foreach ($classifica as $s) {
                            $params = json_decode($help->getParamsbyID($s['squadra_id'], "squadre"));
                            // Assegna il badge in base alla posizione
                            if ($pos <= $promo) {
                                $badge = "success";  // Badge per le squadre in promozione
                            } elseif ($pos > $postoretro) {
                                $badge = "danger";  // Badge per le squadre in retrocessione
                            } else {
                                $badge = "dark";  // Badge per le squadre normali
                            }
                            echo "<tr>";
                            echo "<td><strong>{$pos}</strong></td>";
                            echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='background-color: " . $params->colore_sfondo . "; color: " . $params->colore_testo . "; border: 1px solid " . $params->colore_bordo . ";'>" . $help->getTeamNameByID($s['squadra_id']) . "</div></td>";
                            echo "<td><span class='badge bg-{$badge} fs-6'>{$s['punti']}</span></td>";
                            echo "<td>{$s['giocate']}</td>";
                            echo "<td>{$s['vittorie']}</td>";
                            echo "<td>{$s['pareggi']}</td>";
                            echo "<td>{$s['sconfitte']}</td>";
                            echo "<td>{$s['gol_fatti']}</td>";
                            echo "<td>{$s['gol_subiti']}</td>";
                            echo "<td>{$s['diff_reti']}</td>";
                            echo "</tr>";
                            $pos++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;

        case 'scoreboard':
            ?>
            <div class="table-responsive" style="max-width:100vw; margin-inline: calc((95vw - 100%) / -2);">
                <table class="table table-hover table-striped align-middle text-center">
                    <tbody>
                        <tr>
                            <td></td>
                            <?php
                            // Ottieni le squadre dalla stagione
                            $squadre = json_decode($db->getOne("stagioni", "codice_stagione = ?", [$_GET['season_id']])['squadre']);

                            // Crea un array associativo (ID => Nome)
                            $squadrename = [];
                            foreach ($squadre as $squadra) {
                                $squadrename[$squadra] = $help->getTeamNameByID($squadra);
                            }

                            // Ordina le squadre per nome
                            asort($squadrename); // Usa asort per ordinare per valore (nome squadra) mantenendo la relazione con gli ID
                
                            // Stampa i nomi delle squadre come intestazione
                            foreach ($squadrename as $key => $squadra) {
                                $params = json_decode($help->getParamsbyID($key, "squadre"));

                                echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='background-color: " . $params->colore_sfondo . "; color: " . $params->colore_testo . "; border: 1px solid " . $params->colore_bordo . ";'>" . substr($squadra, 0, 3) . "</div></td>";
                                // Mostra i primi 3 caratteri del nome della squadra
                            }
                            ?>
                        </tr>
                        <?php
                        // Ora stampiamo i risultati per ogni squadra
                        foreach ($squadrename as $keyC => $squadra):
                            $params = json_decode($help->getParamsbyID($keyC, "squadre"));
                            echo "<tr>";
                            echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='background-color: " . $params->colore_sfondo . "; color: " . $params->colore_testo . "; border: 1px solid " . $params->colore_bordo . ";'>" . $squadra . "</div></td>"; // Mostra il nome della squadra nella prima colonna
            
                            // Per ogni avversaria nella colonna, cerchiamo il risultato della partita
                            foreach ($squadrename as $keyT => $avversaria):
                                $risultato = "-"; // Default se non esiste partita tra le due squadre
            
                                // Ottieni gli ID reali delle squadre (già associati)
                                $squadraCasaID = $keyC; // ID della squadra di casa
                                $squadraTrasfertaID = $keyT; // ID della squadra trasferta
            
                                // Cerca se c'è una partita tra le due squadre
                                foreach ($partite as $partita) {
                                    // Controlla se la partita coinvolge entrambe le squadre
                                    if ($partita['squadra_casa_id'] == $squadraCasaID && $partita['squadra_trasferta_id'] == $squadraTrasfertaID) {
                                        // Trova il risultato della partita
                                        if ($partita['squadra_casa_id'] == $squadraCasaID) {
                                            $risultato = $partita['gol_casa'] . "-" . $partita['gol_trasferta']; // Squadra di casa vince/perde
                                        } else {
                                            $risultato = $partita['gol_trasferta'] . "-" . $partita['gol_casa']; // Squadra ospite vince/perde
                                        }
                                        break; // Esci dal ciclo quando trovi la partita
                                    }
                                }

                                // Mostra il risultato o il trattino se non esiste partita
                                echo "<td>" . $risultato . "</td>";
                            endforeach;
                            echo "</tr>";
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>

            <?php
            break;

        case 'statistics':
            $statistiche = $help->getStatistics($classifica);
            ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle text-center">
                    <thead>
                        <tr>
                            <th scope="col">Statistica</th>
                            <th scope="col">Minimo (Valore & Squadre)</th>
                            <th scope="col">Massimo (Valore & Squadre)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistiche['min'] as $key => $minData):
                            $maxData = $statistiche['max'][$key];
                            $minTeams = implode(', ', $minData['teams']);
                            $maxTeams = implode(', ', $maxData['teams']);
                            ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($help->getTranslation($key, $langfile)) ?></th>
                                <td>
                                    <strong><?= htmlspecialchars($minData['value']) ?></strong><br>
                                    <small><?= htmlspecialchars($minTeams) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($maxData['value']) ?></strong><br>
                                    <small><?= htmlspecialchars($maxTeams) ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;



        default:
            // raggruppa le partite per giornata
            $grouped = [];
            foreach ($partite as $m) {
                $grouped[$m['giornata']][] = $m;
            }
            ?>
            <div class="container">
                <div class="row">
                    <?php
                    $idx = 0;
                    foreach ($grouped as $giornata => $matches):
                        // chiudi/apri riga ogni 2 card
                        if ($idx > 0 && $idx % 2 === 0): ?>
                        </div>
                        <div class="row">
                        <?php endif; ?>

                        <div class="col mb-4">
                            <h2 class="text-center fw-bold"><?= $help->getTranslation('day', $langfile) . " " . $giornata ?></h2>
                            <table class="table table-striped table-hover matchtable">
                                <thead>
                                    <tr class="text-center">
                                        <th scope="col">Data</th>
                                        <th scope="col">Squadra Casa</th>
                                        <th scope="col">Squadra Trasferta</th>
                                        <th scope="col" class="text-center">Risultato</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matches as $m):
                                        // Formattazione data
                                        $date = $m['data_partita']
                                            ? date('d/m/Y', strtotime($m['data_partita']))
                                            : '';

                                        // Parametri colori squadre
                                        $params1 = json_decode($help->getParamsbyID($m['squadra_casa_id'], "squadre"));
                                        $params2 = json_decode($help->getParamsbyID($m['squadra_trasferta_id'], "squadre"));

                                        // Badge “pill” squadre
                                        $home = "<span class='rounded-pill fw-bold px-3 py-1 text-nowrap'
                     style='background-color: {$params1->colore_sfondo};
                            color: {$params1->colore_testo};
                            border: 1px solid {$params1->colore_bordo};'>
                   " . htmlspecialchars($help->getTeamNameByID($m['squadra_casa_id'])) . "
               </span>";
                                        $away = "<span class='rounded-pill fw-bold px-3 py-1 text-nowrap'
                     style='background-color: {$params2->colore_sfondo};
                            color: {$params2->colore_testo};
                            border: 1px solid {$params2->colore_bordo};'>
                   " . htmlspecialchars($help->getTeamNameByID($m['squadra_trasferta_id'])) . "
               </span>";

                                        // Punteggio
                                        $score = intval($m['gol_casa']) . '‑' . intval($m['gol_trasferta']);
                                        ?>
                                        <tr>
                                            <td class="align-middle"><?= $date ?></td>
                                            <td class="align-middle text-center"><?= $home ?></td>
                                            <td class="align-middle text-center"><?= $away ?></td>
                                            <td class="align-middle text-center fw-bold"><?= $score ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        </div>
                        <?php
                        $idx++;
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
            break;
    }
}
?>

<div class="container py-5">

    <div class="row mb-4">
        <?php foreach ($help->menu_seasons as $m): ?>
            <div class="col">
                <div class="card mb-3 shadow-sm">
                    <div class="card-body text-center">
                        <a href="?page=seasons_details&season_id=<?= urlencode($_GET['season_id']) ?>&tab=<?= $m ?>"
                            class="card-title h5 text-decoration-none">
                            <?= $help->getTranslation($m, $langfile) ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4">
        <?php foreach ($help->menu_seasons as $m): ?>
            <div class="<?= $activeTab === $m ? 'd-block' : 'd-none' ?>">
                <?php generate($m, $help, $langfile, $db); ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>