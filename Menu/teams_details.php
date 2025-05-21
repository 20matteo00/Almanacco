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
    </div>
</div>