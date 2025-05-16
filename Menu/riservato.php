<?php
// LOGIN CHECK
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = $db->getOne("users", "username = '$username'");
    if (password_verify($password, $user['password']) && $username === $user['username']) {
        $_SESSION['logged'] = true;
        $_SESSION['level'] = $user['livello'];
        $_SESSION['username'] = $user['username'];
    } else {
        $error = "Credenziali non valide.";
    }
}

// LOGOUT CHECK
if (isset($_POST['logout'])) {
    session_destroy();
}

// DUMP CHECK
if (isset($_POST['dump'])) {
    file_put_contents('backup.sql', $db->dump());
}


// HANDLER FORM
if (isset($_SESSION['logged']) && $_SESSION['logged']) {
    if (isset($_POST['save_comp']) || isset($_POST['save_squadra']) || isset($_POST['save_stagione']) || isset($_POST['add_partita'])) {
        if (isset($_POST['save_comp'])) {
            $db->insert('competizioni', [
                'nome'    => $_POST['nome'],
                'descrizione' => $_POST['desc'] ?? '',
                'params'  => json_encode([
                    'livello' => $_POST['livello'] ?? 0
                ])
            ]);
        }
        if (isset($_POST['save_squadra'])) {
            $db->insert('squadre', [
                'nome'   => $_POST['nome'],
                'params' => json_encode([
                    'citta'         => $_POST['citta'] ?? '',
                    'stadio'        => $_POST['stadio'] ?? '',
                    'colore_sfondo' => $_POST['colore_sfondo'] ?? '',
                    'colore_testo'  => $_POST['colore_testo'] ?? '',
                    'colore_bordo'  => $_POST['colore_bordo'] ?? ''
                ])
            ]);
        }
        if (isset($_POST['save_stagione'])) {
            $db->insert('stagioni', [
                'competizione_id' => $_POST['competizione_id'],
                'anno'     => $_POST['anno'],
                'squadre'  => json_encode($_POST['squadre']),
                'codice_stagione' => $_POST['competizione_id'] . '_' . $_POST['anno'],
                'params'          => json_encode([])
            ]);
        }
        if (isset($_POST['add_partita'])) {
            if ($_POST['squadra1_id'] === $_POST['squadra2_id']) {
                $error = "Le squadre non possono essere uguali.";
            } else {
                $db->insert('partite', [
                    'stagione_id'         => $_POST['stagione_id'],
                    'giornata'            => $_POST['giornata'],
                    'squadra_casa_id'     => $_POST['squadra1_id'],
                    'squadra_trasferta_id' => $_POST['squadra2_id'],
                    'gol_casa'            => $_POST['gol1'],
                    'gol_trasferta'       => $_POST['gol2'],
                    'data_partita'        => $_POST['data_partita'] ?? null,
                    'params'              => json_encode([])
                ]);
            }
        }
        /* header('Location: index.php?page=riservato');
        exit(); */
    }
    if (isset($_POST['delete_comp']) || isset($_POST['delete_squadra']) || isset($_POST['delete_stagione']) || isset($_POST['delete_partita'])) {
        if (isset($_POST['delete_comp'])) {
            $db->delete('competizioni', 'id = ?', [$_POST['comp_id']]);
        }
        if (isset($_POST['delete_squadra'])) {
            $db->delete('squadre', 'id = ?', [$_POST['squadra_id']]);
        }
        if (isset($_POST['delete_stagione'])) {
            $db->delete('stagioni', 'codice_stagione = ?', [$_POST['stagione_id']]);
        }
        if (isset($_POST['delete_partita'])) {
            $db->delete('partite', 'stagione_id = ? AND squadra_casa_id = ? AND squadra_trasferta_id = ? AND giornata = ?', [
                $_POST['stagione_id'],
                $_POST['squadra_casa_id'],
                $_POST['squadra_trasferta_id'],
                $_POST['giornata']
            ]);
        }
        header('Location: index.php?page=riservato');
        exit();
    }
}
$competizioni = $db->getAll('competizioni');
$squadre = $db->getAll('squadre');
$stagioni = $db->getAll('stagioni');
$partite = $db->getAll('partite');
?>






<div class="container">
    <?php if (isset($_SESSION['logged']) && $_SESSION['logged']): ?>
        <form class="ms-auto" method="post">
            <button class="btn btn-danger" name="logout">Logout</button>
            <button class="btn btn-success" name="dump">Dump</button>
        </form>
    <?php endif; ?>
    <?php if (!isset($_SESSION['logged'])): ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-3">Login</h3>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Accedi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h2 class="mb-4">Area Riservata</h2>

        <div class="row g-4">
            <!-- Competizione -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">Aggiungi Competizione</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-auto mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Descrizione</label>
                                    <textarea name="desc" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Livello</label>
                                    <input type="number" name="livello" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3 d-flex align-items-end">
                                    <button type="submit" name="save_comp" class="btn btn-success">Salva</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h3>Elenco Competizioni</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Descrizione</th>
                                <th scope="col">Livello</th>
                                <th scope="col">Azioni</th> <!-- nuova colonna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($competizioni) === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nessuna competizione trovata</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($competizioni as $c): ?>
                                    <?php $c['params'] = json_decode($c['params'], true); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['nome']) ?></td>
                                        <td><?= htmlspecialchars($c['descrizione']) ?></td>
                                        <td><?= htmlspecialchars($c['params']['livello'] ?? 'N/A') ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="comp_id" value="<?= $c['id'] ?>">
                                                <button type="submit" name="edit_comp" class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_comp" class="btn btn-sm btn-danger">Elimina</button>
                                            </form>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Squadra -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">Aggiungi Squadra</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-auto mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Città</label>
                                    <input type="text" name="citta" class="form-control">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Stadio</label>
                                    <input type="text" name="stadio" class="form-control">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Colore Sfondo</label>
                                    <input type="color" name="colore_sfondo" class="form-control" default="#000000">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Colore Testo</label>
                                    <input type="color" name="colore_testo" class="form-control" default="#ffffff">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Colore Bordo</label>
                                    <input type="color" name="colore_bordo" class="form-control" default="#000000">
                                </div>
                                <div class="col-auto mb-3 d-flex align-items-end">
                                    <button type="submit" name="save_squadra" class="btn btn-success">Salva</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h3>Elenco Squadre</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Città</th>
                                <th scope="col">Azioni</th> <!-- nuova colonna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($squadre) === 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Nessuna squadra trovata</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($squadre as $s): ?>
                                    <?php $params = json_decode($s['params'], true); ?>
                                    <tr>
                                        <td>
                                            <div class="rounded-pill p-2 text-center fw-bold" style="
                                            background-color: <?= htmlspecialchars($params['colore_sfondo']) ?>; 
                                            color: <?= htmlspecialchars($params['colore_testo']) ?>; 
                                            border: 2px solid <?= htmlspecialchars($params['colore_bordo']) ?>;">
                                                <?= htmlspecialchars(strtoupper($s['nome'])) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($params['citta']) ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="squadra_id" value="<?= $s['id'] ?>">
                                                <button type="submit" name="edit_squadra" class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_squadra" class="btn btn-sm btn-danger">Elimina</button>
                                            </form>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Stagione -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">Aggiungi Stagione</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-auto mb-3">
                                    <label class="form-label">Competizione</label>
                                    <select name="competizione_id" class="form-control" required>
                                        <option value="">Seleziona Competizione</option>
                                        <?php foreach ($competizioni as $c): ?>
                                            <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Competizione</label>
                                    <select name="squadre[]" class="form-control" size="4" multiple required>
                                        <option value="" disabled>Seleziona Squadre</option>
                                        <?php foreach ($squadre as $s): ?>
                                            <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Anno Inizio</label>
                                    <input type="number" name="anno" class="form-control" min="1850" max="2150" required>
                                </div>
                                <div class="col-auto mb-3 d-flex align-items-end">
                                    <button type="submit" name="save_stagione" class="btn btn-success">Salva</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h3>Elenco Stagioni</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Competizione</th>
                                <th scope="col">Anno</th>
                                <th scope="col">Squadre</th>
                                <th scope="col">Azioni</th> <!-- nuova colonna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($stagioni) === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nessuna stagione trovata</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stagioni as $st): ?>
                                    <?php
                                    // Chiamo getOne con WHERE completo e passo il parametro in array
                                    $comp = $db->getOne(
                                        'competizioni',
                                        'id = ?',
                                        [$st['competizione_id']]
                                    );

                                    // Estraggo il nome (o metto stringa vuota se nulla)
                                    $nomecompetizione = $comp ? $comp['nome'] : '';
                                    // Decodifico le squadre
                                    $squadre = json_decode($st['squadre'], true);
                                    $squadrenomi = [];
                                    foreach ($squadre as $squadra_id) {
                                        $squadra = $db->getOne('squadre', 'id = ?', [$squadra_id]);
                                        if ($squadra) {
                                            $squadrenomi[] = $squadra['nome'];
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($nomecompetizione) ?></td>
                                        <td><?= htmlspecialchars($st['anno']) ?></td>
                                        <td><?= htmlspecialchars(implode(', ', $squadrenomi)) ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="stagione_id" value="<?= $st['codice_stagione'] ?>">
                                                <button type="submit" name="edit_stagione" class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_stagione" class="btn btn-sm btn-danger">Elimina</button>
                                            </form>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Partita -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">Aggiungi Partita</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-auto mb-3">
                                    <label class="form-label">ID Stagione</label>
                                    <select name="stagione_id" id="stagione_id" class="form-control" required>
                                        <option value="">Seleziona Stagione</option>
                                        <?php foreach ($stagioni as $st): ?>
                                            <?php
                                            // Chiamo getOne con WHERE completo e passo il parametro in array
                                            $comp = $db->getOne(
                                                'competizioni',
                                                'id = ?',
                                                [$st['competizione_id']]
                                            );

                                            // Estraggo il nome (o metto stringa vuota se nulla)
                                            $nomecompetizione = $comp ? $comp['nome'] : '';
                                            // Decodifico le squadre
                                            $squadre = json_decode($st['squadre'], true);
                                            $squadrenomi = [];
                                            foreach ($squadre as $squadra_id) {
                                                $squadra = $db->getOne('squadre', 'id = ?', [$squadra_id]);
                                                if ($squadra) {
                                                    $squadrenomi[] = $squadra['nome'];
                                                }
                                            }
                                            ?>
                                            <option value="<?= htmlspecialchars($st['codice_stagione']) ?>"><?= htmlspecialchars($nomecompetizione) ?> - <?= htmlspecialchars($st['anno']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Squadra Casa</label>
                                    <select name="squadra1_id" id="squadra1_id" class="form-control" required>
                                        <option value="">Seleziona Squadra</option>
                                        <?php foreach ($squadre as $squadra_id): ?>
                                            <?php
                                            $squadra = $db->getOne('squadre', 'id = ?', [$squadra_id]);
                                            if ($squadra) {
                                                echo '<option value="' . htmlspecialchars($squadra['id']) . '">' . htmlspecialchars($squadra['nome']) . '</option>';
                                            }
                                            ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Squadra Trasferta</label>
                                    <select name="squadra2_id" id="squadra2_id" class="form-control" required>
                                        <option value="">Seleziona Squadra</option>
                                        <?php foreach ($squadre as $squadra_id): ?>
                                            <?php
                                            $squadra = $db->getOne('squadre', 'id = ?', [$squadra_id]);
                                            if ($squadra) {
                                                echo '<option value="' . htmlspecialchars($squadra['id']) . '">' . htmlspecialchars($squadra['nome']) . '</option>';
                                            }
                                            ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Gol Casa</label>
                                    <input type="number" name="gol1" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Gol Trasferta</label>
                                    <input type="number" name="gol2" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Giornata</label>
                                    <input type="number" name="giornata" class="form-control" required>
                                </div>
                                <div class="col-auto mb-3 d-flex align-items-end">
                                    <button type="submit" name="add_partita" class="btn btn-success">Salva</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h3>Elenco Partite</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Stagione</th>
                                <th scope="col">Squadra Casa</th>
                                <th scope="col">Squadra Trasferta</th>
                                <th scope="col">Gol Casa</th>
                                <th scope="col">Gol Trasferta</th>
                                <th scope="col">Giornata</th>
                                <th scope="col">Data Partita</th>
                                <th scope="col">Azioni</th> <!-- nuova colonna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($partite) === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nessuna partita trovata</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($partite as $p): ?>
                                    <?php 
                                        $nomecompetizione = explode('_', $p['stagione_id']);
                                        $nc = $db->getOne('competizioni', 'id = ?', [$nomecompetizione[0]]);

                                        $squadra_casa = $db->getOne('squadre', 'id = ?', [$p['squadra_casa_id']]);
                                        $squadra_trasferta = $db->getOne('squadre', 'id = ?', [$p['squadra_trasferta_id']]);
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($nc['nome']. " " . $nomecompetizione[1]) ?></td>
                                        <td><?= htmlspecialchars($squadra_casa['nome']) ?></td>
                                        <td><?= htmlspecialchars($squadra_trasferta['nome']) ?></td>
                                        <td><?= htmlspecialchars($p['gol_casa']) ?></td>
                                        <td><?= htmlspecialchars($p['gol_trasferta']) ?></td>
                                        <td><?= htmlspecialchars($p['giornata']) ?></td>
                                        <td><?= htmlspecialchars($p['data_partita'] ?? '') ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="stagione_id" value="<?= $p['stagione_id'] ?>">
                                                <input type="hidden" name="squadra_casa_id" value="<?= $p['squadra_casa_id'] ?>">
                                                <input type="hidden" name="squadra_trasferta_id" value="<?= $p['squadra_trasferta_id'] ?>">
                                                <input type="hidden" name="giornata" value="<?= $p['giornata'] ?>">
                                                <button type="submit" name="edit_partita" class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_partita" class="btn btn-sm btn-danger">Elimina</button>
                                            </form>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
