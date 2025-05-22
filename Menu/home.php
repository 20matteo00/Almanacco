<?php
$competizioni = $db->getAll('competizioni', '*', '', [], 'id LIMIT 4');
$stagioni = $db->getAll('stagioni', '*', '', [], 'anno DESC LIMIT 4');
$squadre = $db->getAll('squadre', '*', '', [], 'id LIMIT 12');
?>


<!-- Hero Section -->
<div class="py-5 text-center bg-light">
    <div class="container">
        <h1 class="display-4"><?= $help->getTranslation('home_welcome', $langfile) ?></h1>
        <p class="lead"><?= $help->getTranslation('home_lead', $langfile) ?></p>
        <div class="d-flex justify-content-evenly">
            <a class="btn btn-info" href="#competitions"><?= $help->getTranslation('competitions', $langfile) ?></a>
            <a class="btn btn-info" href="#seasons"><?= $help->getTranslation('seasons', $langfile) ?></a>
            <a class="btn btn-info" href="#teams"><?= $help->getTranslation('teams', $langfile) ?></a>
        </div>
    </div>
</div>

<!-- Competitions Section -->
<section class="py-5" id="competitions">
    <div class="container">
        <h2 class="display-4 text-center fw-bold mb-4"><?= $help->getTranslation('home_competitions', $langfile) ?></h2>
        <div class="row">
            <?php foreach ($competizioni as $comp): ?>
                <?php $params = json_decode($help->getParamsbyID($comp['id'], "competizioni")); ?>
                <div class="col mb-4">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <a href="?page=competitions_details&comp_id=<?= $comp['id'] ?>"
                                class="btn btn-outline-info btn-sm mt-3"><h2 class="m-0 p-3"><?= $comp['nome'] ?></h2></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-success" href="?page=competitions"><?= $help->getTranslation('view_all', $langfile) ?></a>
        </div>
    </div>
</section>

<!-- Seasons Section -->
<section class="py-5" id="seasons">
    <div class="container">
        <h2 class="display-4 text-center fw-bold mb-4"><?= $help->getTranslation('home_seasons', $langfile) ?></h2>
        <div class="row">
            <?php foreach ($stagioni as $s): ?>
                <?php $comp = $help->getCompetitionbyCode($s['codice_stagione']); ?>
                <div class="col mb-4">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <a href="?page=seasons_details&season_id=<?= $s['codice_stagione'] ?>"
                                class="btn btn-outline-info btn-sm mt-3"><h4 class="m-0 p-3"><?= $comp ?>     <?= $s['anno'] ?>/<?= $s['anno'] + 1 ?></h4></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-success" href="?page=seasons"><?= $help->getTranslation('view_all', $langfile) ?></a>
        </div>
    </div>
</section>

<!-- Teams Section -->
<section class="py-5" id="teams">
    <div class="container">
        <h2 class="display-4 text-center fw-bold mb-4"><?= $help->getTranslation('home_teams', $langfile) ?></h2>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($squadre as $team): ?>
                <?php $params = json_decode($help->getParamsbyID($team['id'], "squadre")); $style = $help->createTeam($params->colore_sfondo, $params->colore_testo, $params->colore_bordo ) ?>
                <div class="col mb-4">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <a href="?page=teams_details&team_id=<?= $team['id'] ?>"
                                class="btn btn-secondary rounded-pill w-100 fw-bold" style="<?= $style ?>"><?= $team['nome'] ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-success" href="?page=teams"><?= $help->getTranslation('view_all', $langfile) ?></a>
        </div>
    </div>
</section>