<?php
$competizioni = $db->getAll("competizioni");
?>


<div class="container my-5">
    <h1 class="mb-4 text-center"><?= $langfile['competitions'] ?></h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($competizioni as $c): ?>
            <?php
            $params = json_decode($c['params'], true);
            ?>
                <div class="col"> <div
                    class="card h-100 shadow-sm">
                    <?php if (!empty($c['logo'])): ?>
                        <img
                        src="<?= htmlspecialchars($c['logo']) ?>" class="card-img-top" alt="<?= htmlspecialchars($c['nome']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($c['nome']) ?></h5>
                        <?php if (!empty($c['descrizione'])): ?>
                            <p class="card-text"><?= nl2br(htmlspecialchars($c['descrizione'])) ?></p>
                        <?php endif; ?>
                        <p class="card-text">
                            <strong><?= $langfile['level'] ?>: </strong> <?= $params['livello'] ?><br>
                            <strong><?= $langfile['state'] ?>: </strong> <?= $params['stato'] ?><br>
                        </p>
                    </div>
                    <div class="card-footer text-end">
                        <a href="competizione.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm">Vai</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

