<?php
// LOGIN CHECK
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = $db->getOne("users", "username = '$username'");
    if ($user) {
        if ($user && !is_null($password) && password_verify($password, $user['password']) && $username === $user['username']) {
            $_SESSION['logged'] = true;
            $_SESSION['level'] = $user['livello'];
            $_SESSION['username'] = $user['username'];
        } else {
            $error = "Credenziali non valide.";
        }
    }
}

// LOGOUT CHECK
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php?page=riservato');
    exit();
}

// DUMP CHECK
if (isset($_POST['dump'])) {
    $db->dump();
}

// HANDLER FORM
if (isset($_SESSION['logged']) && $_SESSION['logged']) {
    if (isset($_POST['save_comp']) || isset($_POST['save_squadra']) || isset($_POST['save_stagione']) || isset($_POST['add_partita'])) {
        if (isset($_POST['save_comp'])) {
            $db->insert('competizioni', [
                'nome' => $_POST['nome'],
                'descrizione' => $_POST['desc'] ?? '',
                'params' => json_encode([
                    'livello' => $_POST['livello'] ?? 0,
                    'stato' => $_POST['stato'] ?? 0
                ])
            ]);
        }
        if (isset($_POST['save_squadra'])) {
            $db->insert('squadre', [
                'nome' => $_POST['nome'],
                'params' => json_encode([
                    'citta' => $_POST['citta'] ?? '',
                    'stadio' => $_POST['stadio'] ?? '',
                    'colore_sfondo' => $_POST['colore_sfondo'] ?? '',
                    'colore_testo' => $_POST['colore_testo'] ?? '',
                    'colore_bordo' => $_POST['colore_bordo'] ?? ''
                ])
            ]);
        }
        if (isset($_POST['save_stagione'])) {
            $db->insert('stagioni', [
                'competizione_id' => $_POST['competizione_id'],
                'anno' => $_POST['anno'],
                'squadre' => json_encode($_POST['squadre']),
                'codice_stagione' => $_POST['competizione_id'] . '_' . $_POST['anno'],
                'params' => json_encode([
                    'promozione' => $_POST['promozione'] ?? 1,
                    'retrocessione' => $_POST['retrocessione'] ?? 3,
                    'playoff' => $_POST['playoff'] ?? 0,
                    'playout' => $_POST['playout'] ?? 0,
                    'giornate' => $_POST['giornate'] ?? 38,
                    'vincitore' => $_POST['vincitore'] ?? 0
                ])
            ]);
        }
        if (isset($_POST['add_partita'])) {
            if ($_POST['squadra1_id'] === $_POST['squadra2_id']) {
                $error = "Le squadre non possono essere uguali.";
            } else {
                $db->insert('partite', [
                    'stagione_id' => $_POST['stagione_id'],
                    'giornata' => $_POST['giornata'],
                    'squadra_casa_id' => $_POST['squadra1_id'],
                    'squadra_trasferta_id' => $_POST['squadra2_id'],
                    'gol_casa' => $_POST['gol1'],
                    'gol_trasferta' => $_POST['gol2'],
                    'data_partita' => empty($_POST['data_partita']) ? null : $_POST['data_partita'],
                    'params' => json_encode([])
                ]);
            }
        }

        header('Location: index.php?page=riservato');
        exit();
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

// MODIFICA RECORD
if (isset($_POST['update_comp']) || isset($_POST['update_squadra']) || isset($_POST['update_stagione']) || isset($_POST['update_partita'])) {
    if (isset($_POST['update_comp'])) {
        $db->update('competizioni', [
            'nome' => $_POST['nome'],
            'descrizione' => $_POST['desc'] ?? '',
            'params' => json_encode([
                'livello' => $_POST['livello'] ?? 0,
                'stato' => $_POST['stato'] ?? 0
            ])
        ], 'id = ?', [$_POST['comp_id']]);
    }
    if (isset($_POST['update_squadra'])) {
        $db->update('squadre', [
            'nome' => $_POST['nome'],
            'params' => json_encode([
                'citta' => $_POST['citta'] ?? '',
                'stadio' => $_POST['stadio'] ?? '',
                'colore_sfondo' => $_POST['colore_sfondo'] ?? '',
                'colore_testo' => $_POST['colore_testo'] ?? '',
                'colore_bordo' => $_POST['colore_bordo'] ?? ''
            ])
        ], 'id = ?', [$_POST['squadra_id']]);
    }
    if (isset($_POST['update_stagione'])) {
        $db->update('stagioni', [
            'competizione_id' => $_POST['competizione_id'],
            'anno' => $_POST['anno'],
            'squadre' => json_encode($_POST['squadre']),
            'codice_stagione' => $_POST['competizione_id'] . '_' . $_POST['anno'],
            'params' => json_encode([
                'promozione' => $_POST['promozione'] ?? 1,
                'retrocessione' => $_POST['retrocessione'] ?? 3,
                'playoff' => $_POST['playoff'] ?? 0,
                'playout' => $_POST['playout'] ?? 0,
                'giornate' => $_POST['giornate'] ?? 38,
                'vincitore' => $_POST['vincitore'] ?? 0
            ])
        ], 'codice_stagione = ?', [$_POST['codice_stagione']]);
    }
    if (isset($_POST['update_partita'])) {
        $up = $db->update('partite', [
            'giornata' => $_POST['new_giornata'],
            'gol_casa' => $_POST['gol1'],
            'gol_trasferta' => $_POST['gol2'],
            'data_partita' => empty($_POST['data_partita']) ? null : $_POST['data_partita'],
            'params' => json_encode([])
        ], 'stagione_id = ? AND squadra_casa_id = ? AND squadra_trasferta_id = ? AND giornata = ?', [
            $_POST['stagione_id'],
            $_POST['squadra_casa_id'],
            $_POST['squadra_trasferta_id'],
            $_POST['giornata']
        ]);
    }
    header('Location: index.php?page=riservato');
    exit();
}
$competizioni = $db->getAll('competizioni');
$squadre = $db->getAll('squadre');
$stagioni = $db->getAll('stagioni');
$partite = $db->getAll('partite', '*', '', [], 'data_partita DESC LIMIT 20');
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
    <?php elseif (!isset($_POST['edit_comp']) && !isset($_POST['edit_squadra']) && !isset($_POST['edit_stagione']) && !isset($_POST['edit_partita'])): ?>
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
                                <div class="col-auto mb-3">
                                    <label class="form-label">Stato</label>
                                    <input type="text" name="stato" class="form-control">
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
                <div class="table-responsive my-max-table">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Descrizione</th>
                                <th scope="col">Livello</th>
                                <th scope="col">Stato</th>
                                <th scope="col">Azioni</th> <!-- nuova colonna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($competizioni) === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nessuna competizione trovata</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($competizioni as $c): ?>
                                    <?php $c['params'] = json_decode($c['params'], true); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['nome']) ?></td>
                                        <td><?= htmlspecialchars($c['descrizione']) ?></td>
                                        <td><?= htmlspecialchars($c['params']['livello'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($c['params']['stato'] ?? 'N/A') ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="comp_id" value="<?= $c['id'] ?>">
                                                <button type="submit" name="edit_comp"
                                                    class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_comp"
                                                    class="btn btn-sm btn-danger">Elimina</button>
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
                                    <input type="color" name="colore_sfondo" class="form-control" value="#000000">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Colore Testo</label>
                                    <input type="color" name="colore_testo" class="form-control" value="#ffffff">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Colore Bordo</label>
                                    <input type="color" name="colore_bordo" class="form-control" value="#000000">
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
                <div class="table-responsive my-max-table">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Città</th>
                                <th scope="col">Stadio</th>
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
                                        <td><?= htmlspecialchars($params['stadio']) ?></td>
                                        <td>
                                            <!-- FORM MODIFICA/ELIMINA -->
                                            <form method="post" class="d-inline" action="">
                                                <input type="hidden" name="squadra_id" value="<?= $s['id'] ?>">
                                                <button type="submit" name="edit_squadra"
                                                    class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_squadra"
                                                    class="btn btn-sm btn-danger">Elimina</button>
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
                                            <option value="<?= htmlspecialchars($c['id']) ?>">
                                                <?= htmlspecialchars($c['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Squadre</label>
                                    <select name="squadre[]" class="form-control" size="4" multiple required>
                                        <option value="" disabled>Seleziona Squadre</option>
                                        <?php foreach ($squadre as $s): ?>
                                            <option value="<?= htmlspecialchars($s['id']) ?>">
                                                <?= htmlspecialchars($s['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Anno Inizio</label>
                                    <input type="number" name="anno" class="form-control" min="1850" max="2150" required>
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Promozione</label>
                                    <input type="number" name="promozione" class="form-control" min="0">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Retrocessione</label>
                                    <input type="number" name="retrocessione" class="form-control" min="0">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Playoff</label>
                                    <input type="number" name="playoff" class="form-control" min="0">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Playout</label>
                                    <input type="number" name="playout" class="form-control" min="0">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Giornate</label>
                                    <input type="number" name="giornate" class="form-control" min="0">
                                </div>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Vincitore</label>
                                    <select name="vincitore" class="form-control" size="4">
                                        <option value="" disabled>Seleziona Squadra</option>
                                        <?php foreach ($squadre as $s): ?>
                                            <option value="<?= htmlspecialchars($s['id']) ?>">
                                                <?= htmlspecialchars($s['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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
                <div class="table-responsive my-max-table">
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
                                                <button type="submit" name="edit_stagione"
                                                    class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_stagione"
                                                    class="btn btn-sm btn-danger">Elimina</button>
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
                    <?php $squadre = [] ?>
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
                                            $s = json_decode($st['squadre'], true);
                                            $squadrenomi = [];
                                            foreach ($s as $squadra_id) {
                                                $squadra = $db->getOne('squadre', 'id = ?', [$squadra_id]);
                                                $squadre[] = $squadra_id;
                                                if ($squadra) {
                                                    $squadrenomi[] = $squadra['nome'];
                                                }
                                            }
                                            ?>
                                            <option value="<?= htmlspecialchars($st['codice_stagione']) ?>">
                                                <?= htmlspecialchars($nomecompetizione) ?> -
                                                <?= htmlspecialchars($st['anno']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php $squadre = array_unique($squadre) ?>
                                <div class="col-auto mb-3">
                                    <label class="form-label">Squadra Casa</label>
                                    <select name="squadra1_id" id="squadra1_id" class="form-control" required>
                                        <option value="" disabled>Seleziona Squadra</option>
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
                                        <option value="" disabled>Seleziona Squadra</option>
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
                                <div class="col-auto mb-3">
                                    <label class="form-label">Data</label>
                                    <input type="date" name="data_partita" class="form-control" value="">
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
                <div class="table-responsive my-max-table">
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
                                        <td><?= htmlspecialchars($nc['nome'] . " " . $nomecompetizione[1]) ?></td>
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
                                                <input type="hidden" name="squadra_trasferta_id"
                                                    value="<?= $p['squadra_trasferta_id'] ?>">
                                                <input type="hidden" name="giornata" value="<?= $p['giornata'] ?>">
                                                <button type="submit" name="edit_partita"
                                                    class="btn btn-sm btn-warning">Modifica</button>
                                                <button type="submit" name="delete_partita"
                                                    class="btn btn-sm btn-danger">Elimina</button>
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
    <?php elseif (
        isset($_POST['edit_comp']) ||
        isset($_POST['edit_squadra']) ||
        isset($_POST['edit_stagione']) ||
        isset($_POST['edit_partita'])
    ): ?>
        <h2 class="mb-4">Modifica Record</h2>

        <?php if (isset($_POST['edit_comp'])):
            // carico competizione
            $c = $db->getOne('competizioni', 'id = ?', [$_POST['comp_id']]);
            $params = json_decode($c['params'], true);
            ?>
            <!-- Modifica Competizione -->
            <div class="card mb-5">
                <div class="card-header">Modifica Competizione</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="comp_id" value="<?= $c['id'] ?>">
                        <div class="row">
                            <div class="col-auto mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" required
                                    value="<?= htmlspecialchars($c['nome']) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Descrizione</label>
                                <textarea name="desc" class="form-control"
                                    rows="2"><?= htmlspecialchars($c['descrizione']) ?></textarea>
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Livello</label>
                                <input type="number" name="livello" class="form-control" required
                                    value="<?= htmlspecialchars($params['livello'] ?? 0) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Stato</label>
                                <input type="text" name="stato" class="form-control"
                                    value="<?= htmlspecialchars($params['stato'] ?? '') ?>">
                            </div>
                            <div class="col-auto mb-3 d-flex align-items-end">
                                <button type="submit" name="update_comp" class="btn btn-warning me-2">Aggiorna</button>
                                <button type="submit" name="cancel_comp" class="btn btn-secondary">Annulla</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif (isset($_POST['edit_squadra'])):
            // carico squadra
            $s = $db->getOne('squadre', 'id = ?', [$_POST['squadra_id']]);
            $p = json_decode($s['params'], true);
            ?>
            <!-- Modifica Squadra -->
            <div class="card mb-5">
                <div class="card-header">Modifica Squadra</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="squadra_id" value="<?= $s['id'] ?>">
                        <div class="row">
                            <div class="col-auto mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" required
                                    value="<?= htmlspecialchars($s['nome']) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Città</label>
                                <input type="text" name="citta" class="form-control"
                                    value="<?= htmlspecialchars($p['citta'] ?? '') ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Stadio</label>
                                <input type="text" name="stadio" class="form-control"
                                    value="<?= htmlspecialchars($p['stadio'] ?? '') ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Colore Sfondo</label>
                                <input type="color" name="colore_sfondo" class="form-control"
                                    value="<?= htmlspecialchars($p['colore_sfondo'] ?? '#000000') ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Colore Testo</label>
                                <input type="color" name="colore_testo" class="form-control"
                                    value="<?= htmlspecialchars($p['colore_testo'] ?? '#ffffff') ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Colore Bordo</label>
                                <input type="color" name="colore_bordo" class="form-control"
                                    value="<?= htmlspecialchars($p['colore_bordo'] ?? '#000000') ?>">
                            </div>
                            <div class="col-auto mb-3 d-flex align-items-end">
                                <button type="submit" name="update_squadra" class="btn btn-warning me-2">Aggiorna</button>
                                <button type="submit" name="cancel_squadra" class="btn btn-secondary">Annulla</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif (isset($_POST['edit_stagione'])):
            // carico stagione
            $st = $db->getOne('stagioni', 'codice_stagione = ?', [$_POST['stagione_id']]);
            $sel = json_decode($st['squadre'], true);
            $params = json_decode($st['params'], true);
            $partite_in_stagione = $db->getone('partite', 'stagione_id = ?', [$_POST['stagione_id']]);
            if ($partite_in_stagione) {
                header('Location: ?page=riservato');
                exit;
            }
            ?>
            <!-- Modifica Stagione -->
            <div class="card mb-5">
                <div class="card-header">Modifica Stagione</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="codice_stagione" value="<?= htmlspecialchars($st['codice_stagione']) ?>">
                        <div class="row">
                            <div class="col-auto mb-3">
                                <label class="form-label">Competizione</label>
                                <select name="competizione_id" class="form-control" required>
                                    <?php foreach ($competizioni as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $st['competizione_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Squadre</label>
                                <select name="squadre[]" class="form-control" size="5" multiple required>
                                    <?php foreach ($squadre as $s2): ?>
                                        <option value="<?= $s2['id'] ?>" <?= in_array($s2['id'], $sel) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s2['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Anno</label>
                                <input type="number" name="anno" class="form-control"
                                    value="<?= htmlspecialchars($st['anno']) ?>" min="1850" max="2150" required>
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Promozione</label>
                                <input type="number" name="promozione" class="form-control" value="<?= htmlspecialchars($params['promozione'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Retrocessione</label>
                                <input type="number" name="retrocessione" class="form-control" value="<?= htmlspecialchars($params['retrocessione'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Playoff</label>
                                <input type="number" name="playoff" class="form-control" value="<?= htmlspecialchars($params['playoff'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Playout</label>
                                <input type="number" name="playout" class="form-control" value="<?= htmlspecialchars($params['playout'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Giornate</label>
                                <input type="number" name="giornate" class="form-control" value="<?= htmlspecialchars($params['giornate'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Vincitore</label>
                                <select name="vincitore" class="form-control" size="4">
                                    <option value="" disabled>Seleziona Squadra</option>
                                    <?php foreach ($sel as $s): ?>
                                        <option value="<?= htmlspecialchars($s) ?>" <?= ($params['vincitore'] == $s) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($help->getTeamNameByID($s)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto mb-3 d-flex align-items-end">
                                <button type="submit" name="update_stagione" class="btn btn-warning me-2">Aggiorna</button>
                                <button type="submit" name="cancel_stagione" class="btn btn-secondary">Annulla</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif (isset($_POST['edit_partita'])):
            // carico partita
            $p = $db->getOne(
                'partite',
                'stagione_id = ? AND giornata = ? AND squadra_casa_id = ? AND squadra_trasferta_id = ?',
                [$_POST['stagione_id'], $_POST['giornata'], $_POST['squadra_casa_id'], $_POST['squadra_trasferta_id']]
            );
            ?>
            <!-- Modifica Partita -->
            <div class="card mb-5">
                <div class="card-header">Modifica Partita</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="stagione_id" value="<?= htmlspecialchars($p['stagione_id']) ?>">
                        <input type="hidden" name="giornata" value="<?= htmlspecialchars($p['giornata']) ?>">
                        <input type="hidden" name="squadra_casa_id" value="<?= htmlspecialchars($p['squadra_casa_id']) ?>">
                        <input type="hidden" name="squadra_trasferta_id"
                            value="<?= htmlspecialchars($p['squadra_trasferta_id']) ?>">
                        <div class="row">
                            <div class="col-auto mb-3">
                                <label class="form-label">Gol Casa</label>
                                <input type="number" name="gol1" class="form-control" required
                                    value="<?= htmlspecialchars($p['gol_casa']) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Gol Trasferta</label>
                                <input type="number" name="gol2" class="form-control" required
                                    value="<?= htmlspecialchars($p['gol_trasferta']) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Giornata</label>
                                <input type="number" name="new_giornata" class="form-control"
                                    value="<?= htmlspecialchars($p['giornata']) ?>">
                            </div>
                            <div class="col-auto mb-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="data_partita" class="form-control"
                                    value="<?= htmlspecialchars($p['data_partita']) ?>">
                            </div>
                            <div class="col-auto mb-3 d-flex align-items-end">
                                <button type="submit" name="update_partita" class="btn btn-warning me-2">Aggiorna</button>
                                <button type="submit" name="cancel_partita" class="btn btn-secondary">Annulla</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>