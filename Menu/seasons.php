<?php
$stagioni = $db->getAll("stagioni", '*', '', [], 'anno DESC');
$competizioni = $db->getAll("competizioni");
$squadre = $db->getAll("squadre");
?>


<div class="container my-5">
    <h1 class="mb-4 text-center"><?= $help->getTranslation('seasons', $langfile) ?></h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($stagioni as $s): ?>
            <?php
            if (isset($_GET['comp_id']) && $_GET['comp_id'] != $s['competizione_id']) {
                continue;
            }

            $nomecompetizione = $db->getOne("competizioni", "id = ?", [$s['competizione_id']])['nome'];
            $squadre = json_decode($s['squadre'], true);
            foreach ($squadre as $key => $squadra) {
                $squadre[$key] = $db->getOne("squadre", "id = ?", [$squadra])['nome'];
            }
            sort($squadre);
            ?>
            <div class="col">
                <div
                    class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($nomecompetizione) ?></h5>
                        <p class="card-text">
                            <strong><?= $help->getTranslation('season', $langfile) ?>: </strong> <?= $s['anno'] . '/' . ($s['anno'] + 1) ?? "N/A" ?><br>
                            <strong><?= $help->getTranslation('teams', $langfile) ?>: </strong> <?= implode(", ", array_map('htmlspecialchars', $squadre)) ?? "N/A" ?><br>
                        </p>
                    </div>
                    <div class="card-footer text-end">
                        <a href="?page=seasons_details&season_id=<?= $s['codice_stagione'] ?>" class="btn btn-primary"><?= $help->getTranslation("view", $langfile) ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>