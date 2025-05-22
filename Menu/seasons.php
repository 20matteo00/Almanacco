<?php
// Preleva tutte le competizioni
$competizioni = $db->getAll("competizioni");

// Preleva tutte le stagioni, ordinate per anno discendente
$stagioni = $db->getAll("stagioni", '*', '', [], 'anno DESC');

// Riorganizza le stagioni in un array associativo [competizione_id => [stagioni...]]
$stagioniPerCompetizione = [];
foreach ($stagioni as $s) {
    $stagioniPerCompetizione[$s['competizione_id']][] = $s;
}
?>

<div class="container my-5">
    <h1 class="mb-4 text-center"><?= $help->getTranslation('seasons', $langfile) ?></h1>

    <?php foreach ($competizioni as $comp): 
        // Se non ci sono stagioni per questa competizione, skip
        if (!isset($stagioniPerCompetizione[$comp['id']])) {
            continue;
        }
        $max = 0;
        $nomeComp = htmlspecialchars($comp['nome']);
        ?>
        <div class="mb-5">
            <h2 class="fw-bold"><?= $nomeComp ?></h2>
            <div class="row">
                <?php foreach ($stagioniPerCompetizione[$comp['id']] as $s): 
                    // decodifica e trasforma gli id delle squadre in nomi
                    $idsSquadre = json_decode($s['squadre'], true) ?: [];
                    $nomiSquadre = [];
                    foreach ($idsSquadre as $idSq) {
                        $row = $db->getOne("squadre", "id = ?", [$idSq]);
                        if ($row) {
                            $nomiSquadre[] = htmlspecialchars($row['nome']);
                        }
                    }
                    sort($nomiSquadre);
                    $max++;
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <p class="card-text mb-1">
                                    <strong><?= $help->getTranslation('season', $langfile) ?>:</strong>
                                    <?= $s['anno'] ?>/<?= $s['anno'] + 1 ?>
                                </p>
                                <p class="card-text mb-0">
                                    <strong><?= $help->getTranslation('teams', $langfile) ?>:</strong>
                                    <?= !empty($nomiSquadre) ? implode(", ", $nomiSquadre) : "N/A" ?>
                                </p>
                            </div>
                            <div class="card-footer text-end">
                                <a href="?page=seasons_details&season_id=<?= urlencode($s['codice_stagione']) ?>"
                                   class="btn btn-sm btn-primary">
                                    <?= $help->getTranslation("view", $langfile) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if($max === 4) break; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
