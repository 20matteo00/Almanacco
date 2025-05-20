<?php
$competizioni = $db->getAll("competizioni");
?>


<div class="container my-5">
    <h1 class="mb-4 text-center"><?= $help->getTranslation('competitions', $langfile) ?></h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($competizioni as $c): ?>
            <?php
            $params = json_decode($c['params'], true);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h1 class="card-title fw-bold text-center text-uppercase"><?= htmlspecialchars($c['nome']) ?></h1>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($c['descrizione'])): ?>
                            <p class="card-text text-center"><?= nl2br(htmlspecialchars($c['descrizione'])) ?></p>
                        <?php endif; ?>
                        <p class="card-text">
                            <strong><?= $help->getTranslation('level', $langfile) ?>: </strong>
                            <?= $params['livello'] ?? "N/A" ?><br>
                            <strong><?= $help->getTranslation('state', $langfile) ?>: </strong>
                            <?= $params['stato'] ?? "N/A" ?><br>
                        </p>
                    </div>
                    <div class="card-footer text-end">
                        <a href="?page=competitions_details&comp_id=<?= $c['id'] ?>" class="btn btn-success w-100"><?= $help->getTranslation('go', $langfile) ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>