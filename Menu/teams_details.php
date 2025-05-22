<?php
// --------------------------------------------------
// TEAMS PAGE
// --------------------------------------------------

if (!isset($_GET['team_id'])) {
    header("Location: ?page=teams");
    exit;
}

// Parametro tab attivo (default seasons_list)
$activeTab = $_GET['tab'] ?? '';
$squadra = $db->getOne("squadre", "id = ?", [$_GET['team_id']]);
$params = json_decode($help->getParamsbyID($squadra['id'], "squadre"));
$style = $help->createTeam($params->colore_sfondo, $params->colore_testo, $params->colore_bordo);

$stagioni = $db->getAll("stagioni", '*', '', [], 'anno DESC');
?>

<div class="container py-5 ">
    <div class="title text-center">
        <div class='h1 rounded-pill fw-bold px-4 py-2 text-uppercase' style='<?= $style ?>'>
            <?= $squadra['nome'] ?>
        </div>
    </div>
    <div class="body px-5">
        <div class='h2 fw-bold'>
            <?= $help->getTranslation('info', $langfile) ?>:
        </div>

        <?php foreach ($params as $key => $value): ?>
            <?php if (strpos($key, 'colore') !== 0): ?>
                <div><strong><?= $help->getTranslation($key, $langfile) ?>:</strong> <?= htmlspecialchars($value) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
        <div class='h2 fw-bold'>
            <?= $help->getTranslation('seasons', $langfile) ?>:
        </div>
        <div class="row">
            <?php foreach ($stagioni as $s): ?>
                <?php
                $comp = $help->getCompetitionbyCode($s['codice_stagione']);
                $squadre = json_decode($s["squadre"]);
                $teamId = $_GET['team_id'] ?? null;
                if (in_array($teamId, $squadre)):
                    ?>
                    <div class="col">
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <a href="?page=seasons_details&season_id=<?= $s['codice_stagione'] ?>"
                                            class="text-decoration-none text-primary fw-semibold">
                                            <?= $comp ?>
                                        </a>
                                    </h5>
                                    <small class="text-muted">
                                        <?= $help->getTranslation("season", $langfile) ?>
                                        <?= $s['anno'] . '/' . ($s['anno'] + 1) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>