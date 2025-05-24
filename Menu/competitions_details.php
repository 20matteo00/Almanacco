<?php
// --------------------------------------------------
// COMPETITIONS PAGE
// --------------------------------------------------

if (!isset($_GET['comp_id'])) {
    header("Location: ?page=competitions");
    exit;
}

// Parametro tab attivo (default seasons)
$activeTab = $_GET['tab'] ?? 'seasons';

$icone = [
    "list-task",
    "shield",
    "people",
    "trophy",
    "bar-chart",
    "graph-up",
];

// Funzione di rendering
function generate($tab, $help, $langfile, $db)
{
    $stagioni = $db->getAll("stagioni", '*', 'competizione_id = ?', [$_GET['comp_id']], "anno DESC");
    $tutte_le_partite = [];

    foreach ($stagioni as $s) {
        $partite = $db->getAll("partite", "*", "stagione_id = ?", [$s['codice_stagione']]);
        $tutte_le_partite = array_merge($tutte_le_partite, $partite);
    }
    $location = $_POST['location'] ?? '';

    if ($location == 'home') {
        $ext = '_c';
    } elseif ($location == 'away') {
        $ext = '_t';
    } else {
        $ext = '';
    }
    $classifica_all = $help->getClassifica($tutte_le_partite, $ext);
    switch ($tab) {
        case 'teams':
            $rows = $help->getTeamsPartecipant($_GET['comp_id']);
            if (empty($rows)) {
                echo '<h2>' . $help->getTranslation('no_teams', $langfile) . '</h2>';
                return;
            }
            ?>
            <h2><?= $help->getTranslation('teams', $langfile) ?></h2>
            <div class="table-responsive text-center">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th><?= $help->getTranslation('team', $langfile) ?></th>
                            <th><?= $help->getTranslation('years', $langfile) ?></th>
                            <th><?= $help->getTranslation('actions', $langfile) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $params = json_decode($row['params']);
                            $style = $help->createTeam(
                                $params->colore_sfondo ?? '#000000',
                                $params->colore_testo ?? '#ffffff',
                                $params->colore_bordo ?? '#000000'
                            );
                            ?>
                            <tr>
                                <td>
                                    <div style="<?= $style ?>" class="fw-bold border rounded-pill p-3 fs-5">
                                        <?= htmlspecialchars($row['nome']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= implode(
                                        ', ',
                                        array_map(
                                            fn($y) => htmlspecialchars($y . '/' . ($y + 1)),
                                            $row['anni']
                                        )
                                    )
                                        ?>
                                </td>
                                <td>
                                    <a href="?page=teams_details&team_id=<?= urlencode($row['id']) ?>" class="btn btn-primary btn-sm">
                                        <?= $help->getTranslation('view', $langfile) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;

        case 'direct_clashes':
            // Prepara elenco squadre per select
            $rows = $help->getTeamsPartecipant($_GET['comp_id']);
            $teams = $help->getTeamsNamebyCompetition($rows);
            $compId = (int) $_GET['comp_id'];
            $pattern = $compId . '_%';
            $team1 = $_POST['team1'] ?? null;
            $team2 = $_POST['team2'] ?? null;
            $loc = $_POST['location'] ?? 'all';
            switch ($loc) {
                case 'home':
                    $que = 'stagione_id LIKE ? AND (squadra_casa_id = ? AND squadra_trasferta_id = ?)';
                    $par = [
                        $pattern,
                        $team1,
                        $team2
                    ];
                    break;
                case 'away':
                    $que = 'stagione_id LIKE ? AND (squadra_casa_id = ? AND squadra_trasferta_id = ?)';
                    $par = [
                        $pattern,
                        $team2,
                        $team1
                    ];
                    break;
                default:
                    $que = 'stagione_id LIKE ? AND ((squadra_casa_id = ? AND squadra_trasferta_id = ?) OR (squadra_casa_id = ? AND squadra_trasferta_id = ?))';
                    $par = [
                        $pattern,
                        $team1,
                        $team2,   // casa=team1, trasferta=team2
                        $team2,
                        $team1    // casa=team2, trasferta=team1
                    ];
                    break;
            }

            $partite = $db->getAll(
                'partite',
                '*',
                $que,
                $par,
                "data_partita DESC"
            );
            ?>
            <h2><?= $help->getTranslation('direct_clashes', $langfile) ?></h2>

            <form method="post" action="?page=competitions_details&comp_id=<?= urlencode($_GET['comp_id']) ?>&tab=direct_clashes"
                id="teamForm">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label"><?= $help->getTranslation('team_home', $langfile) ?></label>
                        <select name="team1" class="form-select" onchange="this.form.submit()">
                            <option value=""><?= $help->getTranslation('select_team', $langfile) ?></option>
                            <?php foreach ($teams as list($name, $id)): ?>
                                <option value="<?= htmlspecialchars($id) ?>" <?= ($_POST['team1'] ?? '') == $id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label"><?= $help->getTranslation('team_away', $langfile) ?></label>
                        <select name="team2" class="form-select" onchange="this.form.submit()">
                            <option value=""><?= $help->getTranslation('select_team', $langfile) ?></option>
                            <?php foreach ($teams as list($name, $id)): ?>
                                <option value="<?= htmlspecialchars($id) ?>" <?= ($_POST['team2'] ?? '') == $id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <?= $help->getTranslation('location', $langfile) ?>
                        </label>
                        <select name="location" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $loc === 'all' ? 'selected' : '' ?>>
                                <?= $help->getTranslation('all', $langfile) ?>
                            </option>
                            <option value="home" <?= $loc === 'home' ? 'selected' : '' ?>>
                                <?= $help->getTranslation('home', $langfile) ?>
                            </option>
                            <option value="away" <?= $loc === 'away' ? 'selected' : '' ?>>
                                <?= $help->getTranslation('away', $langfile) ?>
                            </option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (isset($_POST['team1']) && isset($_POST['team2']) && $_POST['team1'] != "" && $_POST['team2'] != "" && !empty($partite)):
                // Qui generi la tabella degli scontri diretti…
                ?>
                <?php $classifica = $help->getClassifica($partite); ?>
                <div class="info-clashes mt-4">
                    <div class="row">
                        <!-- Squadra 1 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header h1 fw-bold text-center"><?= $help->getTeamNameByID($_POST['team1']) ?></div>
                                <div class="card-body">
                                    <?php
                                    // Trova la squadra di team1 nella classifica
                                    $team1_id = $_POST['team1'];
                                    $team1 = null;
                                    foreach ($classifica as $team) {
                                        if ($team['squadra_id'] == $team1_id) {
                                            $team1 = $team;
                                            break;
                                        }
                                    }

                                    // Mostra le informazioni solo se la squadra esiste nella classifica
                                    if ($team1):
                                        ?>
                                        <ul class="list-group fw-bold">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('played', $langfile) ?>
                                                <span class="badge bg-info rounded-pill"><?= $team1['giocate'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('wins', $langfile) ?>
                                                <span class="badge bg-success rounded-pill"><?= $team1['vittorie'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('draws', $langfile) ?>
                                                <span class="badge bg-warning rounded-pill"><?= $team1['pareggi'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('loses', $langfile) ?>
                                                <span class="badge bg-danger rounded-pill"><?= $team1['sconfitte'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_scored', $langfile) ?>
                                                <span class="badge bg-success rounded-pill"><?= $team1['gol_fatti'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_conceded', $langfile) ?>
                                                <span class="badge bg-danger rounded-pill"><?= $team1['gol_subiti'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_difference', $langfile) ?>
                                                <span class="badge bg-dark rounded-pill"><?= $team1['diff_reti'] ?></span>
                                            </li>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Squadra 2 -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header h1 fw-bold text-center"><?= $help->getTeamNameByID($_POST['team2']) ?></div>
                                <div class="card-body">
                                    <?php
                                    // Trova la squadra di team2 nella classifica
                                    $team2_id = $_POST['team2'];
                                    $team2 = null;
                                    foreach ($classifica as $team) {
                                        if ($team['squadra_id'] == $team2_id) {
                                            $team2 = $team;
                                            break;
                                        }
                                    }

                                    // Mostra le informazioni solo se la squadra esiste nella classifica
                                    if ($team2):
                                        ?>
                                        <ul class="list-group fw-bold">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('played', $langfile) ?>
                                                <span class="badge bg-info rounded-pill"><?= $team2['giocate'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('wins', $langfile) ?>
                                                <span class="badge bg-success rounded-pill"><?= $team2['vittorie'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('draws', $langfile) ?>
                                                <span class="badge bg-warning rounded-pill"><?= $team2['pareggi'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('loses', $langfile) ?>
                                                <span class="badge bg-danger rounded-pill"><?= $team2['sconfitte'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_scored', $langfile) ?>
                                                <span class="badge bg-success rounded-pill"><?= $team2['gol_fatti'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_conceded', $langfile) ?>
                                                <span class="badge bg-danger rounded-pill"><?= $team2['gol_subiti'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $help->getTranslation('goals_difference', $langfile) ?>
                                                <span class="badge bg-dark rounded-pill"><?= $team2['diff_reti'] ?></span>
                                            </li>
                                        </ul>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive text-center mt-4">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th><?= $help->getTranslation('season', $langfile) ?></th>
                                <th><?= $help->getTranslation('day', $langfile) ?></th>
                                <th><?= $help->getTranslation('team_home', $langfile) ?></th>
                                <th><?= $help->getTranslation('gol_home', $langfile) ?></th>
                                <th><?= $help->getTranslation('gol_away', $langfile) ?></th>
                                <th><?= $help->getTranslation('team_away', $langfile) ?></th>
                                <th><?= $help->getTranslation('data', $langfile) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partite as $p): ?>
                                <tr>
                                    <td><?= explode("_", $p['stagione_id'])[1] ?></td>
                                    <td><?= $p['giornata'] ?></td>
                                    <td><?= $help->getTeamNameByID($p['squadra_casa_id']) ?></td>
                                    <td><?= $p['gol_casa'] ?></td>
                                    <td><?= $p['gol_trasferta'] ?></td>
                                    <td><?= $help->getTeamNameByID($p['squadra_trasferta_id']) ?></td>
                                    <td><?= $p['data_partita'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>


            <?php else: ?>
                <div class="alert alert-warning mt-3 text-center">
                    <?= $help->getTranslation('no_match', $langfile) ?>
                </div>
            <?php endif;
            break;

        case 'champions':
            $winners = $help->getChampions($stagioni);
            ?>
            <h2><?= $help->getTranslation('champions', $langfile) ?></h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th><?= $help->getTranslation('team', $langfile) ?></th>
                            <th><?= $help->getTranslation('vittorie', $langfile) ?></th>
                            <th><?= $help->getTranslation('year', $langfile) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($winners as $teamId => $info): ?>
                            <?php
                            $params = $help->getParamsbyID($teamId, "squadre");
                            $params = json_decode($params);
                            $style = $help->createTeam(
                                $params->colore_sfondo ?? '#000000',
                                $params->colore_testo ?? '#ffffff',
                                $params->colore_bordo ?? '#000000'
                            );
                            ?>
                            <tr>
                                <td>
                                    <div style="<?= $style ?>" class="fw-bold border rounded-pill p-3 fs-5">
                                        <?= $help->getTeamNameByID($teamId) ?>
                                    </div>
                                </td>
                                <td><?= $info['Vittorie'] ?></td>
                                <td><?= implode(', ', $info['Anni']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;

        case 'all_time_table':

            ?>
            <div class="mini-menu my-4">
                <form action="" method="post" class="d-flex flex-column align-items-center gap-3">
                    <!-- prima riga: i 6 bottoni location + round -->
                    <div class="d-flex justify-content-center gap-5 flex-wrap">
                        <button type="submit" class="btn btn-secondary" name="location" value="all">
                            <?= $help->getTranslation('general', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="location" value="home">
                            <?= $help->getTranslation('home', $langfile) ?>
                        </button>
                        <button type="submit" class="btn btn-secondary" name="location" value="away">
                            <?= $help->getTranslation('away', $langfile) ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive mytable">
                <table class="table table-hover table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th><?= $help->getTranslation('team', $langfile) ?></th>
                            <th><?= $help->getTranslation('edition', $langfile) ?></th>
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

                        foreach ($classifica_all as $s) {
                            $params = json_decode($help->getParamsbyID($s['squadra_id'], "squadre"));
                            $edition = $help->getCountEdition($s['squadra_id'], $_GET['comp_id']);
                            $badge = "dark";  // Badge per le squadre normali
            
                            $style = $help->createTeam($params->colore_sfondo ?? '#000000', $params->colore_testo ?? '#ffffff', $params->colore_bordo ?? '#000000');
                            echo "<tr>";
                            echo "<td><strong>{$pos}</strong></td>";
                            echo "<td><div class='rounded-pill fw-bold px-4 py-2' style='" . $style . "'>" . $help->getTeamNameByID($s['squadra_id']) . "</div></td>";
                            echo "<td><strong>{$edition}</strong></td>";
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
            <?php
            break;

        case "statistics":
            $statistiche = $help->getStatistics($classifica_all);
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
            ?>
            <h2><?= $help->getTranslation('seasons', $langfile) ?></h2>
            <div class="table-responsive text-center">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th><?= $help->getTranslation("year", $langfile) ?></th>
                            <th><?= $help->getTranslation("teams", $langfile) ?></th>
                            <th><?= $help->getTranslation("actions", $langfile) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stagioni as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['anno'] . '/' . ($s['anno'] + 1)) ?></td>
                                <td>
                                    <?php
                                    $squadre = json_decode($s['squadre'], true);
                                    foreach ($squadre as $k => $id) {
                                        $squadre[$k] = $db->getOne('squadre', 'id = ?', [$id])['nome'];
                                    }
                                    sort($squadre);
                                    echo implode(', ', array_map('htmlspecialchars', $squadre));
                                    ?>
                                </td>
                                <td>
                                    <a href="?page=seasons_details&season_id=<?= urlencode($s['codice_stagione']) ?>"
                                        class="btn btn-primary btn-sm">
                                        <?= $help->getTranslation("view", $langfile) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;
    }
}
?>



<div class="container py-5">

    <div class="row mb-4">
        <?php foreach ($help->menu_competitions as $i => $m): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <a href="?page=competitions_details&comp_id=<?= urlencode($_GET['comp_id']) ?>&tab=<?= $m ?>"
                        class="btn btn-success h-100 align-content-center p-2 fs-5 fw-bold">
                        <span class="bi bi-<?= $icone[$i] ?>"></span>
                        <?= $help->getTranslation($m, $langfile) ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4">
        <?php foreach ($help->menu_competitions as $m): ?>
            <div class="<?= $activeTab === $m ? 'd-block' : 'd-none' ?>">
                <?php generate($m, $help, $langfile, $db); ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>