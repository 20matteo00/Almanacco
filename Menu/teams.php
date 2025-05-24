<?php
$squadre = $db->getAll("squadre");
?>
<div class="container my-5">
    <h1 class="mb-4 text-center">
        <?= $help->getTranslation('teams', $langfile) ?>
    </h1>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        <?php foreach ($squadre as $s):
            $params = json_decode($s['params']);
            $style = $help->createTeam(
                $params->colore_sfondo ?? "#000000",
                $params->colore_testo ?? "#ffffff",
                $params->colore_bordo ?? "#000000"
            );
            $name = htmlspecialchars($s['nome']);
            $url = '?page=teams_details&team_id=' . intval($s['id']);
            ?>
            <div class="col d-flex justify-content-center">
                <div class="team-wrapper w-100 text-center">
                    <a href="<?= $url ?>" class="btn btn-secondary rounded-pill w-100 fw-bold" style="<?= $style ?>">
                        <?= $name ?>
                    </a>
                    <div class="team-tooltip">
                        <strong><?= $help->getTranslation('stadium', $langfile) ?>:</strong> <?= $params->stadio ?><br>
                        <strong><?= $help->getTranslation('city', $langfile) ?>:</strong> <?= $params->citta ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>