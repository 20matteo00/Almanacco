<?php
class Database
{
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private $charset = 'utf8mb4';
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    private $ignoreErrors = false;

    /**
     * Costruttore della classe Database
     * 
     * @param string $host Host del database
     * @param string $username Username per la connessione
     * @param string $password Password per la connessione
     * @param string $database Nome del database (opzionale)
     */
    public function __construct($host = 'localhost', $username = 'root', $password = 'Matteo00', $database = 'Almanacco')
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }


    /**
     * Abilita o disabilita l'ignore degli errori
     * 
     * @param bool $ignore True per ignorare gli errori, false altrimenti
     */
    public function setIgnoreErrors($ignore)
    {
        $this->ignoreErrors = (bool) $ignore;
    }

    /**
     * Connetti al database e crealo se non esiste
     * 
     * @return bool True se la connessione ha successo, false altrimenti
     */
    public function connect()
    {
        try {
            // Prima prova a connetterti al database specificato
            if ($this->database !== null) {
                $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
                $this->connection = new PDO($dsn, $this->username, $this->password, $this->options);
                return true;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 1049 && $this->database !== null) {
                // Database non esiste, proviamo a crearlo
                return $this->createDatabase();
            }

            if (!$this->ignoreErrors) {
                throw $e;
            }
            return false;
        }

        return false;
    }

    /**
     * Crea il database se non esiste
     * 
     * @return bool True se il database è stato creato con successo, false altrimenti
     */
    private function createDatabase()
    {
        try {
            // Connessione senza specificare il database
            $tempConnection = new PDO(
                "mysql:host={$this->host};charset={$this->charset}",
                $this->username,
                $this->password,
                $this->options
            );

            // Crea il database
            $sql = "CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $tempConnection->exec($sql);

            // Ora connettiti al database appena creato
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $this->connection = new PDO($dsn, $this->username, $this->password, $this->options);

            return true;
        } catch (PDOException $e) {
            if (!$this->ignoreErrors) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Verifica se la connessione al database è attiva
     * 
     * @return bool True se connesso, false altrimenti
     */
    public function isConnected()
    {
        return $this->connection !== null;
    }

    /**
     * Esegui una query SQL
     * 
     * @param string $sql Query SQL da eseguire
     * @param array $params Parametri per la prepared statement
     * @return PDOStatement|false Il risultato della query o false in caso di errore
     */
    public function query($sql, $params = [])
    {
        if (!$this->isConnected()) {
            if (!$this->ignoreErrors) {
                throw new PDOException("Nessuna connessione al database");
            }
            return false;
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (!$this->ignoreErrors) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Ottieni tutte le righe da una tabella
     * 
     * @param string $table Nome della tabella
     * @param string $where Condizione WHERE (opzionale)
     * @param array $params Parametri per la condizione WHERE
     * @return array|false Array di righe o false in caso di errore
     */
    public function getAll($table, $where = '', $params = [])
    {
        $sql = "SELECT * FROM `{$table}`";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * Ottieni una singola riga da una tabella
     * 
     * @param string $table Nome della tabella
     * @param string $where Condizione WHERE
     * @param array $params Parametri per la condizione WHERE
     * @return array|false La riga trovata o false in caso di errore
     */
    public function getOne($table, $where, $params = [])
    {
        $sql = "SELECT * FROM `{$table}` WHERE {$where} LIMIT 1";
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    /**
     * Inserisci una nuova riga in una tabella
     * 
     * @param string $table Nome della tabella
     * @param array $data Dati da inserire (associativo colonna => valore)
     * @return bool True se l'inserimento è riuscito, false in caso di errore
     */
    public function insert($table, $data)
    {
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($data);

        $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->query($sql, $values);

        return $stmt ? true : false;
    }

    /**
     * Aggiorna righe in una tabella
     * 
     * @param string $table Nome della tabella
     * @param array $data Dati da aggiornare (associativo colonna => valore)
     * @param string $where Condizione WHERE
     * @param array $params Parametri aggiuntivi per la condizione WHERE
     * @return int|false Numero di righe aggiornate o false in caso di errore
     */
    public function update($table, $data, $where, $params = [])
    {
        if (empty($data)) {
            return false;
        }

        $setParts = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = "UPDATE `{$table}` SET " . implode(', ', $setParts) . " WHERE {$where}";
        $stmt = $this->query($sql, array_merge($values, $params));

        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Elimina righe da una tabella
     * 
     * @param string $table Nome della tabella
     * @param string $where Condizione WHERE
     * @param array $params Parametri per la condizione WHERE
     * @return int|false Numero di righe eliminate o false in caso di errore
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Conta il numero di righe in una tabella
     * 
     * @param string $table Nome della tabella
     * @param string $where Condizione WHERE (opzionale)
     * @param array $params Parametri per la condizione WHERE
     * @return int|false Numero di righe o false in caso di errore
     */
    public function count($table, $where = '', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM `{$table}`";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return false;
        }

        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Verifica se una tabella esiste
     * 
     * @param string $table Nome della tabella
     * @return bool True se la tabella esiste, false altrimenti
     */
    public function tableExists($table)
    {
        try {
            $result = $this->query("SELECT 1 FROM `{$table}` LIMIT 1");
            return $result !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Crea tutte le tabelle necessarie per l'almanacco sportivo
     */
    public function createAllTables()
    {
        $this->createUsersTable();
        $this->createCompetizioniTable();
        $this->createSeasonsTable();
        $this->createTeamsTable();
        $this->createMatchesTable();
    }

    /**
     * Tabella users
     */
    private function createUsersTable()
    {
        $columns = [
            'id' => 'INT AUTO_INCREMENT',
            'username' => 'VARCHAR(50) NOT NULL UNIQUE',
            'password' => 'VARCHAR(255) NOT NULL',
            'livello' => 'TINYINT NOT NULL DEFAULT 1',
        ];
        $primaryKey = 'id';

        if ($this->createTable('users', $columns, $primaryKey)) {
            // Inserimento utente admin
            $data = [
                'username' => 'admin',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'livello' => 5
            ];
            $this->insert('users', $data);
        }
    }

    /**
     * Tabella competizioni
     */
    private function createCompetizioniTable()
    {
        $columns = [
            'id' => 'INT AUTO_INCREMENT',
            'nome' => 'VARCHAR(100) NOT NULL UNIQUE',
            'descrizione' => 'TEXT',
            'params' => 'JSON', // dati extra di competizione
        ];
        $primaryKey = 'id';

        $this->createTable('competizioni', $columns, $primaryKey);
    }

    /**
     * Tabella stagioni
     */
    private function createSeasonsTable()
    {
        $columns = [
            'codice_stagione' => 'VARCHAR(20) NOT NULL', // es. '1_2024'
            'competizione_id' => 'INT NOT NULL',
            'anno' => 'INT NOT NULL',
            'squadre' => 'JSON', // elenco squadre in formato JSON
            'params' => 'JSON', // dettagli extra in futuro
        ];
        $primaryKey = 'codice_stagione';

        if ($this->createTable('stagioni', $columns, $primaryKey)) {
            $this->query("ALTER TABLE `stagioni` ADD CONSTRAINT `fk_stagioni_competizioni` FOREIGN KEY (`competizione_id`) REFERENCES `competizioni`(`id`) ON DELETE CASCADE");
        }
    }

    /**
     * Tabella squadre
     */
    private function createTeamsTable()
    {
        $columns = [
            'id' => 'INT AUTO_INCREMENT',
            'nome' => 'VARCHAR(100) NOT NULL UNIQUE',
            'params' => 'JSON', // dati extra sui team
        ];
        $primaryKey = 'id';

        $this->createTable('squadre', $columns, $primaryKey);
    }

    /**
     * Tabella partite
     */
    private function createMatchesTable()
    {
        $columns = [
            'stagione_id'           => 'VARCHAR(20) NOT NULL',
            'giornata'              => 'INT NOT NULL',
            'squadra_casa_id'       => 'INT NOT NULL',
            'squadra_trasferta_id'  => 'INT NOT NULL',
            'gol_casa'              => 'TINYINT',
            'gol_trasferta'         => 'TINYINT',
            'data_partita'          => 'DATETIME',
            'params'                => 'JSON',
        ];
        $primaryKey = ['stagione_id', 'giornata', 'squadra_casa_id', 'squadra_trasferta_id'];

        if ($this->createTable('partite', $columns, $primaryKey)) {
            // FK su codice_stagione con cascade
            $this->query("ALTER TABLE `partite` ADD CONSTRAINT `fk_partite_stagione` FOREIGN KEY (`stagione_id`) REFERENCES `stagioni`(`codice_stagione`) ON DELETE CASCADE");
            // FK su squadra_casa con cascade
            $this->query("ALTER TABLE `partite` ADD CONSTRAINT `fk_partite_casa` FOREIGN KEY (`squadra_casa_id`) REFERENCES `squadre`(`id`) ON DELETE CASCADE");
            // FK su squadra_trasferta con cascade
            $this->query("ALTER TABLE `partite` ADD CONSTRAINT `fk_partite_trasferta` FOREIGN KEY (`squadra_trasferta_id`) REFERENCES `squadre`(`id`) ON DELETE CASCADE");
            // Check squadre distinte
            $this->query("ALTER TABLE `partite` ADD CONSTRAINT `chk_squadre_distinte` CHECK (squadra_casa_id <> squadra_trasferta_id)");
        }
    }



    /**
     * Crea una nuova tabella
     * 
     * @param string $table Nome della tabella
     * @param array $columns Array di definizioni delle colonne
     * @param string|array $primaryKey Chiave primaria (opzionale)
     * @param array $options Opzioni aggiuntive (ENGINE, CHARSET, ecc.)
     * @return bool True se la tabella è stata creata, false altrimenti
     */
    public function createTable($table, $columns, $primaryKey = null, $options = [])
    {
        if (empty($columns)) {
            return false;
        }

        $columnDefs = [];
        foreach ($columns as $name => $def) {
            $columnDefs[] = "`{$name}` {$def}";
        }

        // Gestisci la chiave primaria, se è un array crea una chiave primaria composta
        if ($primaryKey !== null) {
            if (is_array($primaryKey)) {
                // Se è un array, aggiungi la chiave primaria composta
                $primaryKeyString = implode("`, `", $primaryKey);
                $columnDefs[] = "PRIMARY KEY (`{$primaryKeyString}`)";
            } else {
                $columnDefs[] = "PRIMARY KEY (`{$primaryKey}`)";
            }
        }

        $defaultOptions = [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ];

        $options = array_merge($defaultOptions, $options);

        $optionsStr = '';
        foreach ($options as $key => $value) {
            $optionsStr .= " {$key}={$value}";
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (" . implode(', ', $columnDefs) . "){$optionsStr}";
        return $this->query($sql) !== false;
    }

    /**
     * Elimina una tabella
     * 
     * @param string $table Nome della tabella
     * @return bool True se la tabella è stata eliminata, false altrimenti
     */
    public function dropTable($table)
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        return $this->query($sql) !== false;
    }

    /**
     * Esegui una transazione
     * 
     * @param callable $callback Funzione da eseguire nella transazione
     * @return mixed Il risultato della callback o false in caso di errore
     */
    public function transaction(callable $callback)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $this->connection->beginTransaction();
            $result = $callback($this);
            $this->connection->commit();
            return $result;
        } catch (Exception $e) {
            $this->connection->rollBack();
            if (!$this->ignoreErrors) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Chiudi la connessione al database
     */
    public function close()
    {
        $this->connection = null;
    }

    /**
     * Distruttore: chiude la connessione quando l'oggetto viene distrutto
     */
    public function __destruct()
    {
        $this->close();
    }
}
