<?php
// --------------------------------------------------
// COMPETITIONS PAGE
// --------------------------------------------------

if (!isset($_GET['comp_id'])) {
    header("Location: ?page=competitions");
    exit;
}

// Parametro tab attivo (default seasons_list)
$activeTab = $_GET['tab'] ?? 'seasons_list';

// Funzione di rendering
function generate($tab, $help, $langfile, $db)
{
    switch ($tab) {
        case 'seasons_list':
            $stagioni = $db
                ->query("SELECT * FROM stagioni WHERE competizione_id = ? ORDER BY anno DESC", [$_GET['comp_id']])
                ->fetchAll();
?>
            <h2><?= $help->getTranslation('seasons_list', $langfile) ?></h2>
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
                                    <a href="?page=seasons_details&id=<?= urlencode($s['codice_stagione']) ?>"
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

        case 'participating_teams':
            $rows = $help->getTeamsPartecipant($db, $_GET['comp_id']);
            if (empty($rows)) {
                echo '<h2>' . $help->getTranslation('no_teams', $langfile) . '</h2>';
                return;
            }
        ?>
            <h2><?= $help->getTranslation('participating_teams', $langfile) ?></h2>
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
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
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
                                    <a href="?page=teams_details&id=<?= urlencode($row['id']) ?>"
                                        class="btn btn-primary btn-sm">
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
            $rows    = $help->getTeamsPartecipant($db, $_GET['comp_id']);
            $teams   = $help->getTeamsNamebyCompetition($rows);
            $compId = (int)$_GET['comp_id'];
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
                $par
            );
        ?>
            <h2><?= $help->getTranslation('direct_clashes', $langfile) ?></h2>

            <form method="post"
                action="?page=competitions_details&comp_id=<?= urlencode($_GET['comp_id']) ?>&tab=direct_clashes"
                id="teamForm">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label"><?= $help->getTranslation('team_home', $langfile) ?></label>
                        <select name="team1" class="form-select"
                            onchange="this.form.submit()">
                            <option value=""><?= $help->getTranslation('select_team', $langfile) ?></option>
                            <?php foreach ($teams as list($name, $id)): ?>
                                <option value="<?= htmlspecialchars($id) ?>"
                                    <?= ($_POST['team1'] ?? '') == $id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label"><?= $help->getTranslation('team_away', $langfile) ?></label>
                        <select name="team2" class="form-select"
                            onchange="this.form.submit()">
                            <option value=""><?= $help->getTranslation('select_team', $langfile) ?></option>
                            <?php foreach ($teams as list($name, $id)): ?>
                                <option value="<?= htmlspecialchars($id) ?>"
                                    <?= ($_POST['team2'] ?? '') == $id ? 'selected' : '' ?>>
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
                            <option value="all" <?= $loc === 'all'  ? 'selected' : '' ?>>
                                <?= $help->getTranslation('all',   $langfile) ?>
                            </option>
                            <option value="home" <?= $loc === 'home' ? 'selected' : '' ?>>
                                <?= $help->getTranslation('home',  $langfile) ?>
                            </option>
                            <option value="away" <?= $loc === 'away' ? 'selected' : '' ?>>
                                <?= $help->getTranslation('away',  $langfile) ?>
                            </option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (isset($_POST['team1']) && isset($_POST['team2']) && $_POST['team1'] != "" && $_POST['team2'] != "" && !empty($partite)):
                // Qui generi la tabella degli scontri diretti…
            ?>
                <div class="table-responsive text-center mt-4">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th><?= $help->getTranslation('day', $langfile) ?></th>
                                <th><?= $help->getTranslation('team_home', $langfile) ?></th>
                                <th><?= $help->getTranslation('gol_home', $langfile) ?></th>
                                <th><?= $help->getTranslation('gol_away', $langfile) ?></th>
                                <th><?= $help->getTranslation('team_away', $langfile) ?></th>
                                <th><?= $help->getTranslation('data', $langfile) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partite as $p) : ?>
                                <tr>
                                    <td><?= $p['giornata'] ?></td>
                                    <td><?= $help->getTeamNameByID($p['squadra_casa_id'], $db) ?></td>
                                    <td><?= $p['gol_casa'] ?></td>
                                    <td><?= $p['gol_trasferta'] ?></td>
                                    <td><?= $help->getTeamNameByID($p['squadra_trasferta_id'], $db) ?></td>
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
    }
}
?>



<div class="container py-5">

    <div class="row mb-4">
        <?php foreach ($help->menu_competitions as $m): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-body text-center">
                        <a href="?page=competitions_details&comp_id=<?= urlencode($_GET['comp_id']) ?>
                      &tab=<?= $m ?>"
                            class="card-title h5 text-decoration-none">
                            <?= $help->getTranslation($m, $langfile) ?>
                        </a>
                    </div>
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