<?php
// --------------------------------------------------
// SEASONS PAGE
// --------------------------------------------------

if (!isset($_GET['season_id'])) {
    header("Location: ?page=seasons");
    exit;
}

// Parametro tab attivo (default matches)
$activeTab = $_GET['tab'] ?? 'matches';

$icone = [
    "calendar",
    "bar-chart",
    "table",
    "graph-up",
];
// Funzione di rendering
function generate($tab, $help, $langfile, $db)
{
    $season_params = json_decode($help->getParamsbyID($_GET['season_id'], "stagioni", "codice_stagione"));
    $location = $_POST['location'] ?? '';
    $round = $_POST['round'] ?? '';
    $giornate = $season_params->giornate;      // 38
    $meta = floor($giornate / 2);         // 19
    if ($round === 'gone' || is_numeric($round)) {
        // Andata: giornate da 1 a $meta (1–19)
        if (is_numeric($round))
            $meta = $round;
        $partite = $db->getAll(
            "partite",
            '*',
            'stagione_id = ? AND giornata BETWEEN ? AND ?',
            [$_GET['season_id'], 1, $meta],
            'giornata ASC, data_partita ASC'
        );
    } elseif ($round === 'return') {
        // Ritorno: giornate da $meta+1 a $giornate (20–38)
        $partite = $db->getAll(
            "partite",
            '*',
            'stagione_id = ? AND giornata BETWEEN ? AND ?',
            [$_GET['season_id'], $meta + 1, $giornate],
            'giornata ASC, data_partita ASC'
        );
    } else {
        // Tutte le giornate
        $partite = $db->getAll(
            "partite",
            '*',
            'stagione_id = ?',
            [$_GET['season_id']],
            'giornata ASC, data_partita ASC'
        );
    }
    if ($location == 'home') {
        $ext = '_c';
    } elseif ($location == 'away') {
        $ext = '_t';
    } else {
        $ext = '';
    }
    $classifica = $help->getClassifica($partite, $ext);
    switch ($tab) {
        case 'table':
            ?>
            <div class="mini-menu my-4">
                <form action="" method="post" class="d-flex flex-column align-items-center gap-3">
                    <!-- prima riga: i 6 bottoni location + round -->
                    <div class="d-flex justify-content-center gap-5 flex-wrap">
                        <button type="submit" class="btn btn-secondary" name="location" value="all">
                            <?= $help->getTranslation('general', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="trend" value="trend">
                            <?= $help->getTranslation('trend', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="location" value="home">
                            <?= $help->getTranslation('home', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="location" value="away">
                            <?= $help->getTranslation('away', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="round" value="gone">
                            <?= $help->getTranslation('andata', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="round" value="return">
                            <?= $help->getTranslation('ritorno', $langfile) ?>
                        </button>
                        <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#giornateCollapse"
                            aria-expanded="false" aria-controls="giornateCollapse">
                            <?= $help->getTranslation('day', $langfile) ?>
                        </button>
                    </div>

                    <!-- seconda riga: il gruppo di giornate, collassabile -->
                    <div class="collapse w-100" id="giornateCollapse">
                        <div class="d-flex justify-content-center flex-wrap gap-2 mt-2">
                            <?php for ($i = 1; $i <= $giornate; $i++): ?>
                                <button type="submit" class="btn btn-secondary btn-sm" name="round" value="<?= $i ?>">
                                    <?= $i ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($_POST['trend']) && $_POST['trend'] == 'trend'): ?>
                <?php $andamento = $help->getAndamento($partite); ?>
                <div class="table-responsive">
                    <?php
                    // 1) Raccogli i leader di ogni giornata (o `null` se pareggio)
                    $leaders = [];
                    foreach ($andamento as $giornata => $classifica) {
                        // Se c’è un ex aequo in testa, mettiamo `null`
                        if ($classifica[0]['punti'] === $classifica[1]['punti']) {
                            $leaders[] = null;
                        } else {
                            $leaders[] = $classifica[0]['squadra_id'];
                        }
                    }

                    // 2) Trasforma i leader in run con colspan
                    $cells = [];
                    $tot = count($leaders);
                    for ($i = 0; $i < $tot; ) {
                        $team = $leaders[$i];
                        // conta la lunghezza del blocco
                        $len = 1;
                        while ($i + $len < $tot && $leaders[$i + $len] === $team) {
                            $len++;
                        }
                        $cells[] = ['team' => $team, 'colspan' => $len];
                        $i += $len;
                    }
                    ?>

                    <table class="table table-hover table-bordered table-striped text-center align-middle mb-5">
                        <thead>
                            <tr>
                                <?php foreach ($cells as $cell): ?>
                                    <?php if ($cell['team'] === null): ?>
                                        <th colspan="<?= $cell['colspan'] ?>" style="background-color: grey;"></th>
                                    <?php else: ?>
                                        <?php
                                        // Parametri e stile della squadra
                                        $params = json_decode($help->getParamsbyID($cell['team'], 'squadre'));
                                        $style = $help->createTeam(
                                            $params->colore_sfondo ?? '#000000',
                                            $params->colore_testo ?? '#ffffff',
                                            $params->colore_bordo ?? '#000000'
                                        );
                                        // Nome abbreviato
                                        $name = $help->getTeamNameByID($cell['team']);
                                        $abbr = strtoupper(substr($name, 0, 3));
                                        ?>
                                        <th colspan="<?= $cell['colspan'] ?>"
                                            style="background-color: <?= $params->colore_sfondo ?? '#000000' ?>; color: <?= $params->colore_testo ?? '#ffffff' ?>;">
                                            <?= $abbr ?>
                                        </th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-secondary">
                                <?php foreach (array_keys($andamento) as $giornata): ?>
                                    <td><?= $giornata ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <?php
                // 1) Estrai la lista di tutte le squadre (ID e nome)
                $squadre = [];
                foreach ($andamento as $giornata => $classifica) {
                    foreach ($classifica as $row) {
                        $id = $row['squadra_id'];
                        $squadre[$id] = $help->getTeamNameByID($id);
                    }
                }
                // Ordino le squadre per nome
                asort($squadre, SORT_STRING);

                // 2) Lista delle giornate
                $giornate = array_keys($andamento);
                ?>

                <div class="table-responsive mytable">
                    <table class="table table-hover table-bordered table-striped text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Squadra</th>
                                <?php foreach ($giornate as $g): ?>
                                    <th scope="col"><?= $g ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($squadre as $teamId => $teamName): ?>
                                <tr>
                                    <!-- Nome squadra abbreviato o intero -->
                                    <td class="fw-bold" scope="row"><?= htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8') ?></td>

                                    <?php foreach ($giornate as $g): ?>
                                        <?php
                                        // Cerco nella giornata $g la riga con questa squadra
                                        $punti = '-';
                                        foreach ($andamento[$g] as $row) {
                                            if ($row['squadra_id'] == $teamId) {
                                                $punti = $row['punti'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <td><?= $punti ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="table-responsive mytable">
                    <table class="table table-hover table-striped align-middle text-center">
                        <thead class="table-dark">
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
                            $promo = $season_params->promozione;
                            $retro = $season_params->retrocessione;
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
                                $style = $help->createTeam($params->colore_sfondo ?? '#000000', $params->colore_testo ?? '#ffffff', $params->colore_bordo ?? '#000000');
                                echo "<tr>";
                                echo "<td><strong>{$pos}</strong></td>";
                                echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='" . $style . "'>" . $help->getTeamNameByID($s['squadra_id']) . "</div></td>";
                                echo '<td><span class="badge bg-' . $badge . ' fs-6">'
                                    . htmlspecialchars($s['punti' . $ext])
                                    . '</span></td>';
                                echo '<td>' . htmlspecialchars($s['giocate' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['vittorie' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['pareggi' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['sconfitte' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['gol_fatti' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['gol_subiti' . $ext]) . '</td>';
                                echo '<td>' . htmlspecialchars($s['diff_reti' . $ext]) . '</td>';
                                echo "</tr>";
                                $pos++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
                                $style = $help->createTeam($params->colore_sfondo ?? '#000000', $params->colore_testo ?? '#ffffff', $params->colore_bordo ?? '#000000');

                                echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='" . $style . "'>" . substr($squadra, 0, 3) . "</div></td>";
                                // Mostra i primi 3 caratteri del nome della squadra
                            }
                            ?>
                        </tr>
                        <?php
                        // Ora stampiamo i risultati per ogni squadra
                        foreach ($squadrename as $keyC => $squadra):
                            $params = json_decode($help->getParamsbyID($keyC, "squadre"));
                            $style = $help->createTeam($params->colore_sfondo ?? '#000000', $params->colore_testo ?? '#ffffff', $params->colore_bordo ?? '#000000');

                            echo "<tr>";
                            echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='" . $style . "'>" . $squadra . "</div></td>"; // Mostra il nome della squadra nella prima colonna
            
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
                    <thead class="table-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"><?= htmlspecialchars($help->getTranslation("max", $langfile)) ?></th>
                            <th scope="col"><?= htmlspecialchars($help->getTranslation("min", $langfile)) ?></th>
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
                                    <span>
                                        <strong>
                                            <?= htmlspecialchars($maxTeams) ?>
                                        </strong> (<?= htmlspecialchars($maxData['value']) ?>)
                                    </span>
                                </td>
                                <td>
                                    <span>
                                        <strong>
                                            <?= htmlspecialchars($minTeams) ?>
                                        </strong> (<?= htmlspecialchars($minData['value']) ?>)
                                    </span>
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
                            <?php if ($giornata >= 100): ?>
                                <h2 class="text-center fw-bold"><?= $help->getTranslation($help->giornateover100[$giornata], $langfile) ?>
                                </h2>
                            <?php else: ?>
                                <h2 class="text-center fw-bold"><?= $help->getTranslation('day', $langfile) . " " . $giornata ?></h2>
                            <?php endif; ?>
                            <table class="table table-striped table-hover matchtable">
                                <thead class="table-dark">
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
                                        $style1 = $help->createTeam(
                                            $params1->colore_sfondo ?? '#000000',
                                            $params1->colore_testo ?? '#ffffff',
                                            $params1->colore_bordo ?? '#000000'
                                        );
                                        $style2 = $help->createTeam(
                                            $params2->colore_sfondo ?? '#000000',
                                            $params2->colore_testo ?? '#ffffff',
                                            $params2->colore_bordo ?? '#000000'
                                        );
                                        // Badge “pill” squadre
                                        $home = "<span class='rounded-pill fw-bold px-3 py-1 text-nowrap' style= '" . $style1 . "'>
                   " . htmlspecialchars($help->getTeamNameByID($m['squadra_casa_id'])) . "
               </span>";
                                        $away = "<span class='rounded-pill fw-bold px-3 py-1 text-nowrap'
                     style= '" . $style2 . "'>
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
        <?php foreach ($help->menu_seasons as $i => $m): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <a href="?page=seasons_details&season_id=<?= urlencode($_GET['season_id']) ?>&tab=<?= $m ?>"
                        class="btn btn-success h-100 align-content-center p-2 fs-5 fw-bold">
                        <span class="bi bi-<?=$icone[$i]?>"></span>
                        <?= $help->getTranslation($m, $langfile) ?>
                    </a>
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