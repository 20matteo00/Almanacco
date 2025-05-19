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
    switch ($tab) {
        case 'ranking':
            $classifica = $help->getClassifica($partite);
            ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle text-center">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Squadra</th>
                            <th>G</th>
                            <th>V</th>
                            <th>N</th>
                            <th>P</th>
                            <th>GF</th>
                            <th>GS</th>
                            <th>DR</th>
                            <th>Punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pos = 1;
                        foreach ($classifica as $s) {
                            $dr = $s['gol_fatti'] - $s['gol_subiti'];
                            echo "<tr>";
                            echo "<td><strong>{$pos}</strong></td>";
                            echo "<td>" . $help->getTeamNameByID($s['squadra_id']) . "</td>";
                            echo "<td>{$s['giocate']}</td>";
                            echo "<td>{$s['vittorie']}</td>";
                            echo "<td>{$s['pareggi']}</td>";
                            echo "<td>{$s['sconfitte']}</td>";
                            echo "<td>{$s['gol_fatti']}</td>";
                            echo "<td>{$s['gol_subiti']}</td>";
                            echo "<td>" . ($dr >= 0 ? "+" : "") . "$dr</td>";
                            echo "<td><span class='badge bg-primary fs-6'>{$s['punti']}</span></td>";
                            echo "</tr>";
                            $pos++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            break;

        case 'statistics':

            break;

        default:
            // raggruppa le partite per giornata
            $grouped = [];
            foreach ($partite as $m) {
                $grouped[$m['giornata']][] = $m;
            }

            // helper per ottenere il nome di una squadra
            function getTeamName($id, $db)
            {
                $r = $db->getOne('squadre', 'id = ?', [$id]);
                return $r ? $r['nome'] : '—';
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

                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <!-- header -->
                                <div class="card-header bg-primary text-white text-center fw-bold h3">
                                    <?= $help->getTranslation('day', $langfile) . " " . htmlspecialchars($giornata) ?>
                                </div>
                                <!-- body -->
                                <div class="card-body">
                                    <?php foreach ($matches as $m):
                                        $date = $m['data_partita']
                                            ? date('d/m/Y', strtotime($m['data_partita']))
                                            : '';
                                        $home = htmlspecialchars(getTeamName($m['squadra_casa_id'], $db));
                                        $away = htmlspecialchars(getTeamName($m['squadra_trasferta_id'], $db));
                                        $score = intval($m['gol_casa']) . '‑' . intval($m['gol_trasferta']);
                                        echo "
                                            <div class=\"d-flex flex-wrap justify-content-between align-items-center py-2 border-bottom\">
                                                <div class=\"d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2\">
                                                    <span class=\"text-muted small\">{$date}</span>
                                                    <span class=\"fw-semibold\">{$home} – {$away}</span>
                                                </div>
                                                <div class=\"fw-bold fs-5 text-end text-md-start\" style=\"min-width: 70px;\">
                                                    {$score}
                                                </div>
                                            </div>";
                                    endforeach; ?>
                                </div>
                                <!-- footer (vuoto per ora) -->
                                <div class="card-footer"></div>
                            </div>
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
            <div class="col-12 col-md-6 col-lg-4">
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