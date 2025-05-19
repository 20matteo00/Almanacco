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
    switch ($tab) {
        case 'ranking':

            break;

        case 'statistics':

            break;

        default:
            $partite = $db->getAll("partite", '*', 'stagione_id = ?', [$_GET['season_id']], 'giornata ASC');
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

            echo '<div class="container"><div class="row">';
            $idx = 0;
            foreach ($grouped as $giornata => $matches) {
                // chiudi/apri riga ogni 2 card
                if ($idx > 0 && $idx % 2 === 0) {
                    echo '</div><div class="row">';
                }

                echo '<div class="col-md-6 mb-4">';
                echo '<div class="card h-100">';
                // header
                echo '<div class="card-header bg-primary text-white">';
                echo 'Giornata ' . htmlspecialchars($giornata);
                echo '</div>';
                // body
                echo '<div class="card-body">';
                foreach ($matches as $m) {
                    $date = $m['data_partita']
                        ? date('d/m/Y', strtotime($m['data_partita']))
                        : '';
                    $home = htmlspecialchars(getTeamName($m['squadra_casa_id'], $db));
                    $away = htmlspecialchars(getTeamName($m['squadra_trasferta_id'], $db));
                    $score = intval($m['gol_casa']) . '‑' . intval($m['gol_trasferta']);
                    echo "<p class=\"mb-2\"><strong>{$date}</strong> {$home} – {$away} <span class=\"fw-bold\">{$score}</span></p>";
                }
                echo '</div>';
                // footer (vuoto per ora)
                echo '<div class="card-footer">&nbsp;</div>';
                echo '</div>';
                echo '</div>';

                $idx++;
            }
            echo '</div></div>';
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
                        <a href="?page=seasons_details&season_id=<?= urlencode($_GET['season_id']) ?>
                      &tab=<?= $m ?>" class="card-title h5 text-decoration-none">
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