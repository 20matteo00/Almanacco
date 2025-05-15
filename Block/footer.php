<footer class="bg-dark text-white pt-4 pb-2">
    <div class="container">
        <div class="row">
            <!-- Colonna 1 -->
            <div class="col-md-6 mb-3">
                <h5><?= $langfile['contact'] ?></h5>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a class="text-white text-decoration-none" href="tel:+123456789" target="_blank"><i class="bi bi-phone"></i> <?= $langfile['phone'] ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="text-white text-decoration-none" href="mailto:aaaa.aaaaa@aaa.aaa" target="_blank"><i class="bi bi-envelope"></i> <?= $langfile['email'] ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="text-white text-decoration-none" href="https://www.google.com/maps/place/@44.4228856,8.8664596,16.17z" target="_blank"><i class="bi bi-geo-alt"></i> <?= $langfile['address'] ?></a>
                    </li>
                </ul>
            </div>

            <!-- Colonna 2 -->
            <div class="col-md-2 mb-3">
                <h5><?= $langfile['links'] ?></h5>
                <ul class="list-unstyled">
                    <?php foreach ($help->menu as $m): ?>
                        <li class="nav-item">
                            <a class="text-white text-decoration-none" aria-current="page" href="?page=<?= $m ?>&lang=<?= $lang ?>"><?= $langfile[$m] ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Colonna 3 -->
            <div class="col-md-4 mb-3">
                <h5><?= $langfile['social'] ?></h5>
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="#" class="text-white">
                            <i class="bi bi-facebook fs-4"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white">
                            <i class="bi bi-discord fs-4"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white">
                            <i class="bi bi-twitter-x fs-4"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white">
                            <i class="bi bi-instagram fs-4"></i>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white">
                            <i class="bi bi-threads fs-4"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>