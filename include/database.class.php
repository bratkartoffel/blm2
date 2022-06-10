<?php

class Database
{
    public const TABLE_JOBS = 'auftrag';
    public const TABLE_GROUP = 'gruppe';
    public const TABLE_GROUP_DIPLOMACY = 'gruppe_diplomatie';
    public const TABLE_GROUP_CASH = 'gruppe_kasse';
    public const TABLE_GROUP_LOG = 'gruppe_logbuch';
    public const TABLE_GROUP_MESSAGES = 'gruppe_nachrichten';
    public const TABLE_GROUP_RIGHTS = 'gruppe_rechte';
    public const TABLE_MARKET = 'marktplatz';
    public const TABLE_USERS = 'mitglieder';
    public const TABLE_MESSAGES = 'nachrichten';
    public const TABLE_PASSWORD_RESET = 'passwort_reset';
    public const TABLE_SITTER = 'sitter';
    public const TABLE_STATISTICS = 'statistik';
    public const TABLE_CONTRACTS = 'vertraege';
    public const TABLE_LOG_BANK = 'log_bank';
    public const TABLE_LOG_SHOP = 'log_bioladen';
    public const TABLE_LOG_GROUP_CASH = 'log_gruppenkasse';
    public const TABLE_LOG_LOGIN = 'log_login';
    public const TABLE_LOG_MAFIA = 'log_mafia';
    public const TABLE_LOG_CONTRACTS = 'log_vertraege';

    private static ?Database $INSTANCE = null;

    public static function getInstance(): Database
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new Database();
        }
        return self::$INSTANCE;
    }

    private PDO $link;
    private int $queries = 0;

    function __construct()
    {
        try {
            $this->link = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                database_hostname, database_database), database_username, database_password,
                array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    function __destruct()
    {
        if ($this->link->inTransaction()) {
            $this->link->rollBack();
        }
        $this->queries = 0;
    }

    public function begin(): bool
    {
        return $this->link->beginTransaction();
    }

    public function commit(): void
    {
        if (!$this->link->commit()) {
            $this->error($this->link, 'Could not commit transaction!');
            die();
        }
    }

    public function rollBack(): void
    {
        $this->link->rollBack();
    }

    public function lastInsertId(): int
    {
        return $this->link->lastInsertId();
    }

    public function createTableEntry(string $table, array $values = array()): ?int
    {
        $columnNames = array_keys($values);
        $columnParameters = array();
        foreach ($values as $field => $value) {
            $columnParameters[] = ':' . $field;
        }

        $stmt = $this->prepare(sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, implode(", ", $columnNames), implode(", ", $columnParameters)));
        return $this->executeAndGetAffectedRows($stmt, $values);
    }

    public function existsTableEntry(string $table, array $wheres = array()): bool
    {
        $conditions = array();
        $fields = array();
        $i = 0;
        foreach ($wheres as $field => $value) {
            $conditions[] = sprintf('%s = :whr%d', $field, ++$i);
            $fields['whr' . $i] = $value;
        }
        $sql = sprintf("SELECT 1 AS found WHERE EXISTS(SELECT 1 FROM %s WHERE %s)", $table, implode(" AND ", $conditions));
        return $this->executeAndExtractField($this->prepare($sql), 'found', $fields) !== null;
    }

    public function updateTableEntry(string $table, ?int $id, array $changes = array(), array $additionWhere = array()): ?int
    {
        $fields = array();
        foreach ($changes as $field => $value) {
            if ($value === null) {
                $fields[] = sprintf("%s = NULL", $field);
                unset($changes[$field]);
            } else {
                $fields[] = sprintf("%s = :%s", $field, $field);
            }
        }
        $wheres = array();
        $i = 0;
        foreach ($additionWhere as $cond => $value) {
            if (is_int($cond)) {
                $wheres[] = sprintf(" AND %s", $value);
            } else {
                $wheres[] = sprintf(" AND %s", $cond);
                $changes['whr' . $i++] = $value;
            }
        }
        if ($id != null) {
            $stmt = $this->prepare(sprintf("UPDATE %s SET %s WHERE ID = :id%s", $table, implode(", ", $fields), implode(" ", $wheres)));
            $changes['id'] = $id;
        } else {
            $stmt = $this->prepare(sprintf("UPDATE %s SET %s WHERE ID = ID%s", $table, implode(", ", $fields), implode(" ", $wheres)));
        }
        return $this->executeAndGetAffectedRows($stmt, $changes);
    }

    public function updateTableEntryCalculate(string $table, ?int $id, array $changes = array(), array $additionWhere = array()): ?int
    {
        $fields = array();
        foreach ($changes as $field => $value) {
            $fields[] = sprintf("%s = %s + :%s", $field, $field, $field);
        }
        $wheres = array();
        $i = 0;
        foreach ($additionWhere as $cond => $value) {
            $wheres[] = $cond;
            $changes['whr' . $i++] = $value;
        }
        if (count($wheres) == 0) {
            $wheres[] = '1=1';
        }
        if ($id !== null) {
            $stmt = $this->prepare(sprintf("UPDATE %s SET %s WHERE ID = :id AND %s", $table, implode(", ", $fields), implode(" AND ", $wheres)));
            $changes['id'] = $id;
        } else {
            $stmt = $this->prepare(sprintf("UPDATE %s SET %s WHERE %s", $table, implode(", ", $fields), implode(" AND ", $wheres)));
        }
        return $this->executeAndGetAffectedRows($stmt, $changes);
    }

    public function deleteTableEntry(string $table, int $id): ?int
    {
        return $this->deleteTableEntryWhere($table, array('ID' => $id));
    }

    public function deleteTableEntryWhere(string $table, array $wheres): ?int
    {
        $where = array();
        foreach ($wheres as $cond => $value) {
            $where[] = sprintf("%s = :%s", $cond, $cond);
        }
        $stmt = $this->prepare(sprintf("DELETE FROM %s WHERE " . implode(" AND ", $where), $table));
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt, $wheres);
    }

    public function truncateTables(array $tables): ?string
    {
        foreach ($tables as $table) {
            $stmt = $this->prepare(sprintf("DELETE FROM %s WHERE 1", $table));
            $status = $this->executeAndGetAffectedRows($stmt);
            if ($status === null) {
                return $table;
            }
        }
        foreach ($tables as $table) {
            $stmt = $this->prepare(sprintf("ALTER TABLE %s AUTO_INCREMENT 1;", $table));
            $status = $this->executeAndGetAffectedRows($stmt);
            if ($status === null) {
                return $table;
            }
        }
        return null;
    }

    public function getPlayerNameById(int $id): ?string
    {
        $stmt = $this->prepare("SELECT Name FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function getPlayerAndSitterPasswordsById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Passwort AS 'Benutzer', s.Passwort as 'Sitter'
            FROM mitglieder m LEFT OUTER JOIN sitter s ON m.ID = s.user_id
            WHERE m.ID = :id
            AND m.ID > 0");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAuftragByIdAndVon(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM auftrag WHERE ID = :id AND user_id = :user");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerIdByNameOrEmailAndActivationToken(?string $name, ?string $email, string $code): ?int
    {
        $stmt = $this->prepare("SELECT ID FROM mitglieder WHERE ID > 0 AND (Name = :name OR EMail = :email) AND EMailAct = :code");
        $stmt->bindParam("name", $name);
        $stmt->bindParam("email", $email);
        $stmt->bindParam("code", $code);
        return $this->executeAndExtractField($stmt, 'ID');
    }

    public function getPasswordRequestByUserId(int $id): ?array
    {
        $stmt = $this->prepare("SELECT * FROM passwort_reset WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerIdAndNameByEmail(string $email): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM mitglieder WHERE ID > 0 AND EMail = :email");
        $stmt->bindParam("email", $email);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndEmailById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Name, EMail FROM mitglieder WHERE ID > 0 AND ID = :id");
        $stmt->bindParam("id", $id);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerRankById(int $id): ?int
    {
        $stmt = $this->prepare("SELECT `row_number` FROM (SELECT (@row_number := @row_number + 1) AS `row_number`, t.ID FROM mitglieder t, (SELECT @row_number := 0) r WHERE ID > 0 ORDER BY t.Punkte DESC, t.ID) as rnN WHERE ID = :id");
        $stmt->bindParam("id", $id);
        return $this->executeAndExtractField($stmt, 'row_number');
    }

    public function getPlayerRankByName(string $name): ?int
    {
        $stmt = $this->prepare("SELECT `row_number` FROM (SELECT (@row_number := @row_number + 1) AS `row_number`, t.Name FROM mitglieder t, (SELECT @row_number := 0) r WHERE ID > 0 ORDER BY t.Punkte DESC, t.ID) as rnN WHERE Name = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractField($stmt, 'row_number');
    }

    public function getPlayerPointsAndNameAndMoneyAndGruppeAndZaunById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, Geld, Gruppe, Gebaeude7 FROM mitglieder WHERE ID = :id AND ID > 0");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerCount(): ?int
    {
        return $this->executeAndExtractField($this->prepare("SELECT count(1) AS count FROM mitglieder WHERE ID > 0"), 'count');
    }

    public function getAdminBankLogCount(string $werFilter): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_bank WHERE playerName LIKE :wer");
        $stmt->bindParam("wer", $werFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBankLogEntries(string $werFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT * FROM log_bank WHERE log_bank.playerName LIKE :wer ORDER BY created DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllMessagesByAnCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM nachrichten WHERE An = :id ORDER BY Zeit DESC");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllMessagesByAnEntries(int $blm_user, string $page, string $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS VonID, coalesce(m.Name, 'Gelöscht') AS VonName
            FROM nachrichten n LEFT OUTER JOIN mitglieder m ON n.Von = m.ID
            WHERE n.An = :id ORDER BY n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam("id", $blm_user);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllMessagesByVonCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM nachrichten WHERE Von = :id ORDER BY Zeit DESC");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllMessagesByVonEntries(int $blm_user, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS AnID, coalesce(m.Name, 'Gelöscht') AS AnName
            FROM nachrichten n LEFT OUTER JOIN mitglieder m ON n.An = m.ID
            WHERE n.Von = :id ORDER BY n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam("id", $blm_user);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getRanglisteUserEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT m.Name AS BenutzerName, m.ID AS BenutzerID, m.LastAction, m.Admin AS IstAdmin, m.Betatester AS IstBetatester, m.Punkte, g.Kuerzel AS GruppeName, m.Gruppe AS GruppeID
            FROM mitglieder m LEFT OUTER JOIN gruppe g ON m.Gruppe = g.ID
            WHERE m.ID > 0 ORDER BY m.Punkte DESC, m.ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getEwigePunkteEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT ID, Name, EwigePunkte
            FROM mitglieder WHERE EwigePunkte > 0 ORDER BY EwigePunkte DESC, ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getRanglisteGroupEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("WITH stats AS (
    SELECT m.Gruppe AS GruppeID, SUM(m.Punkte) AS Punkte, COUNT(1) AS AnzMitglieder, CONCAT(';', GROUP_CONCAT(m.ID SEPARATOR ';'), ';') AS Mitglieder
    FROM mitglieder m
    WHERE Gruppe IS NOT NULL
    GROUP BY m.Gruppe
)
SELECT s.*, g.Kuerzel AS GruppeKuerzel, g.Name AS GruppeName FROM stats s INNER JOIN gruppe g ON s.GruppeID = g.ID ORDER BY s.Punkte DESC, AnzMitglieder LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getLeaderOnlineTime(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Onlinezeit
            FROM mitglieder
            WHERE ID > 0
            ORDER BY Onlinezeit DESC, ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderMafia(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.AusgabenMafia
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.AusgabenMafia DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderMarket(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.AusgabenMarkt
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.AusgabenMarkt DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderBuildings(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.AusgabenGebaeude
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.AusgabenGebaeude DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderResearch(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.AusgabenForschung
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.AusgabenForschung DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderProduction(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.AusgabenProduktion
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.AusgabenProduktion DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderInterest(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, s.EinnahmenZinsen
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            ORDER BY s.EinnahmenZinsen DESC, m.ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getLeaderIgmSent(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, IgmGesendet
            FROM mitglieder
            WHERE ID > 0
            ORDER BY IgmGesendet DESC, ID LIMIT 0, :limit");
        $stmt->bindParam('limit', $count, PDO::PARAM_INT);
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } elseif ($count == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getAdminBioladenLogCount(string $werFilter): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_bioladen WHERE playerName LIKE :wer");
        $stmt->bindParam("wer", $werFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBioladenLogEntries(string $werFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT * FROM log_bioladen
            WHERE playerName LIKE :wer ORDER BY created DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminGroupTreasuryLogCount(string $werFilter, string $wenFilter, ?int $groupFilter): ?int
    {
        if ($groupFilter == null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_gruppenkasse
                WHERE senderName LIKE :wer AND (receiverId IS NULL OR receiverName LIKE :wen)");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_gruppenkasse
                WHERE groupId = :gruppe AND (senderName LIKE :wer AND (receiverId IS NULL OR receiverName LIKE :wen))");
            $stmt->bindParam("gruppe", $groupFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminGroupTreasuryLogEntries(string $werFilter, string $wenFilter, ?int $groupFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        if ($groupFilter == null) {
            $stmt = $this->prepare("SELECT * FROM log_gruppenkasse
                WHERE senderName LIKE :wer AND (receiverId IS NULL OR receiverName LIKE :wen) ORDER BY created DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT * FROM log_gruppenkasse
                WHERE groupId = :gruppe AND (senderName LIKE :wer AND (receiverId IS NULL OR receiverName LIKE :wen)) ORDER BY created DESC LIMIT :offset, :count");
            $stmt->bindParam("gruppe", $groupFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminLoginLogCount(string $werFilter, string $ipFilter, ?int $artFilter): ?int
    {
        if ($artFilter === null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_login WHERE playerName LIKE :wer AND IP LIKE :ip");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_login WHERE playerName LIKE :wer AND IP LIKE :ip AND sitter = :art");
            $stmt->bindParam("art", $artFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("ip", $ipFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminLoginLogEntries(string $werFilter, string $ipFilter, ?int $artFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        if ($artFilter == null) {
            $stmt = $this->prepare("SELECT * FROM log_login
                WHERE playerName LIKE :wer AND IP LIKE :ip ORDER BY created DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT * FROM log_login
                WHERE playerName LIKE :wer AND IP LIKE :ip AND sitter = :art ORDER BY created DESC LIMIT :offset, :count");
            $stmt->bindParam("art", $artFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("ip", $ipFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminMafiaLogCount(string $werFilter, string $wenFilter): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_mafia
            WHERE senderName LIKE :wer AND receiverName LIKE :wen");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminMafiaLogEntries(string $werFilter, string $wenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT * FROM log_mafia
            WHERE senderName LIKE :wer AND receiverName LIKE :wen ORDER BY created DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminVertraegeLogCount(string $werFilter, string $wenFilter, ?int $angenommenFilter): ?int
    {
        if ($angenommenFilter === null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_vertraege
                WHERE senderName LIKE :wer AND receiverName LIKE :wen");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_vertraege
                WHERE senderName LIKE :wer AND receiverName LIKE :wen AND accepted = :angenommen");
            $stmt->bindParam("angenommen", $angenommenFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminVertraegeLogEntries(string $werFilter, string $wenFilter, ?int $angenommenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        if ($angenommenFilter === null) {
            $stmt = $this->prepare("SELECT * FROM log_vertraege
                WHERE senderName LIKE :wer AND receiverName LIKE :wen ORDER BY created DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT * FROM log_vertraege
                WHERE senderName LIKE :wer AND receiverName LIKE :wen AND accepted = :angenommen ORDER BY created DESC LIMIT :offset, :count");
            $stmt->bindParam("angenommen", $angenommenFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getMarktplatzEntryById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Von, Menge, Was, Preis FROM marktplatz WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getMarktplatzEntryByIdAndVon(int $id, int $von): ?array
    {
        $stmt = $this->prepare("SELECT ID, Von, Menge, Was, Preis FROM marktplatz WHERE ID = :id AND Von = :von");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("von", $von, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getVertragEntryById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name AS Von, An AS AnId, m2.Name AS AnName, Menge, Was, Preis
            FROM (vertraege v JOIN mitglieder m1 ON m1.ID = v.Von) JOIN mitglieder m2 ON m2.ID = v.An
            WHERE v.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerEmailAndBeschreibungAndSitterSettingsById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.EMail, m.Beschreibung, s.*
            FROM mitglieder m LEFT OUTER JOIN sitter s ON m.ID = s.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getNotizblock(int $id): ?string
    {
        $stmt = $this->prepare("SELECT Notizblock FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Notizblock');
    }

    public function getMarktplatzCount(array $warenFilter = array()): ?int
    {
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz
                         WHERE Was IN (" . str_repeat('?, ', count($warenFilter) - 1) . "?)");
        }
        return $this->executeAndExtractField($stmt, 'count', $warenFilter);
    }

    public function getMarktplatzEntries(array $warenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM marktplatz m1 JOIN mitglieder m2 ON m2.ID = m1.Von ORDER BY ID DESC LIMIT :offset, :count");
        } else {
            $fields = array();
            for ($i = 0; $i < count($warenFilter); $i++) {
                $fields[] = sprintf(':ware_%d', $i);
            }
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM marktplatz m1 JOIN mitglieder m2 ON m2.ID = m1.Von
                WHERE m1.Was IN (" . implode(', ', $fields) . ") ORDER BY ID DESC LIMIT :offset, :count");
            for ($i = 0; $i < count($warenFilter); $i++) {
                $stmt->bindParam($fields[$i], $warenFilter[$i], PDO::PARAM_INT);
            }

        }
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupMessageCount(int $groupId): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe_nachrichten WHERE Gruppe = :id");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getGroupMessageEntries(int $groupId, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS VonID, coalesce(m.Name, 'Gelöscht') AS VonName
            FROM gruppe_nachrichten n LEFT OUTER JOIN mitglieder m ON n.Von = m.ID
            WHERE n.Gruppe = :id ORDER BY n.Festgepinnt DESC, n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupLogCount(int $groupId): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe_logbuch WHERE Gruppe = :id");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getGroupLogEntries(int $groupId, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT * FROM gruppe_logbuch WHERE Gruppe = :id ORDER BY Datum DESC LIMIT :offset, :count");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getVertragCount(string $werFilter, string $wenFilter): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM vertraege WHERE Von LIKE :wer AND AN LIKE :wen");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getVertragEntries(string $werFilter, string $wenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name as VonName, An AS AnId, m2.Name AS AnName, Was, Menge, Preis, Menge * Preis AS Gesamtpreis 
            FROM (vertraege v JOIN mitglieder m1 ON m1.ID = v.Von) JOIN mitglieder m2 ON m2.Id = v.An
            WHERE Von LIKE :wer AND AN LIKE :wen LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupCount(): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getEwigePunkteCount(): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE EwigePunkte > 0");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllGroupIdsAndName(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM gruppe");
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndName(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM mitglieder WHERE ID > 0 ORDER BY Name");
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndNameAndEmailAndEmailActAndLastLogin(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, EMail, EMailAct, LastLogin FROM mitglieder WHERE ID > 0 ORDER BY Name");
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupIdAndPasswordByNameOrTag(string $name): ?array
    {
        $stmt = $this->prepare("SELECT ID, Passwort FROM gruppe WHERE Name = :name OR Kuerzel = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupIdAndNameByNameOrTag(string $name): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM gruppe WHERE Name = :name OR Kuerzel = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupIdAndNameById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM gruppe WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerDataByName(string $name): ?array
    {
        $stmt = $this->prepare("SELECT m.ID AS ID, Name, Admin, EMailAct, Gesperrt, m.Passwort AS user_password, s.Passwort AS sitter_password
            FROM mitglieder m LEFT OUTER JOIN sitter s ON m.ID = s.user_id
            WHERE m.Name = :name
            AND m.ID > 0");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndBankAndMoneyAndGroupById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Name, Bank, Geld, Gruppe FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndGroupIdAndGroupRightsById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Name, m.Gruppe, r.*
            FROM mitglieder m LEFT OUTER JOIN gruppe_rechte r ON m.Gruppe = r.group_id AND m.ID = r.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerStock(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Lager1, Lager2, Lager3, Lager4, Lager5, Lager6, Lager7, Lager8,
       Lager9, Lager10, Lager11, Lager12, Lager13, Lager14, Lager15 FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerPlantageAndBauhofLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Gebaeude1, Gebaeude5 FROM mitglieder WHERE ID = :id AND ID > 0");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerPointsAndMoneyAndNextMafiaAndGroupById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Punkte, Geld, NextMafia, Gruppe from mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndPointsAndGruppeAndPlantageLevelById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.Name, m.Punkte, m.Gebaeude1, gr.ID AS GruppeID, gr.Name AS GruppeName
            FROM mitglieder m LEFT OUTER JOIN gruppe gr ON m.Gruppe = gr.ID
            WHERE m.ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupInformationById(int $group): ?array
    {
        $stmt = $this->prepare("SELECT g.ID, g.Name, g.Kuerzel, g.Beschreibung, (SELECT SUM(Punkte) FROM mitglieder m WHERE m.Gruppe = g.ID) AS Punkte, g.LastImageChange
            FROM gruppe g INNER JOIN mitglieder m ON g.ID = m.Gruppe
            WHERE g.ID = :id");
        $stmt->bindParam("id", $group, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupRightsByUserId(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM gruppe_rechte WHERE user_id = :uid");
        $stmt->bindParam("uid", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllGroupRightsByGroupId(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT r.*, m.ID AS UserId, coalesce(m.Name, 'Gelöscht') AS UserName
            FROM gruppe_rechte r LEFT OUTER JOIN mitglieder m ON r.user_id = m.ID
            WHERE r.group_id = :gid");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupCashById(int $group_id): ?float
    {
        $stmt = $this->prepare("SELECT Kasse FROM gruppe WHERE ID = :gid");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Kasse');
    }

    public function getAllGroupCashById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT k.*, m.ID AS UserID, coalesce(m.Name, 'Gelöscht') AS UserName, if(m.Gruppe = g.ID, 1, 0) AS IstMitglied
            FROM (gruppe_kasse k LEFT OUTER JOIN mitglieder m ON k.user_id = m.ID) LEFT OUTER JOIN gruppe g On k.group_id = g.ID
            WHERE k.group_id = :gid ORDER BY k.amount DESC");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupMembersById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, LastAction FROM mitglieder WHERE Gruppe = :id ORDER BY Punkte DESC");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupMemberCountById(int $group_id): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Gruppe = :id ORDER BY Punkte DESC");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllGroupDiplomacyById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT gd.ID, gd.Von, gd.An, gr.ID AS GruppeID, gr.Name AS GruppeName, gd.Typ, gd.Aktiv, gd.Seit, gd.Betrag
            FROM gruppe_diplomatie gd INNER JOIN gruppe gr ON gd.An = gr.ID
            WHERE gd.Von = :id
            UNION
            SELECT gd.ID, gd.Von, gd.An, gr.ID AS GruppeID, gr.Name AS GruppeName, gd.Typ, gd.Aktiv, gd.Seit, gd.Betrag
            FROM gruppe_diplomatie gd INNER JOIN gruppe gr ON gd.Von = gr.ID
            WHERE gd.An = :id");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPendingGroupDiplomacyById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT gd.ID, gd.Von, gr.ID AS VonId, gr.Name AS VonName, gd.Typ, gd.Seit
            FROM gruppe_diplomatie gd INNER JOIN gruppe gr ON gd.Von = gr.ID
            WHERE gd.An = :id AND gd.Aktiv = 0");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getPlayerPointsAndGruppeAndMoneyAndNextMafiaAndPizzeriaById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, Gruppe, Geld, NextMafia, Gebaeude8
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerMoneyAndBuildingLevelsAndExpenseMafia(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Geld, m.Gebaeude1, m.Gebaeude2, m.Gebaeude3, m.Gebaeude4,
                m.Gebaeude5, m.Gebaeude6, m.Gebaeude7, m.Gebaeude8, s.AusgabenMafia
            FROM mitglieder m INNER JOIN statistik s ON m.ID = s.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerMoneyAndResearchLevelsAndPlantageLevel(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Geld, Forschung1, Forschung2, Forschung3, Forschung4, Forschung5, Forschung6, Forschung7,
       Forschung8, Forschung9, Forschung10, Forschung11, Forschung12, Forschung13, Forschung14, Forschung15, Gebaeude1
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Forschung1, Forschung2, Forschung3, Forschung4, Forschung5, Forschung6, Forschung7,
       Forschung8, Forschung9, Forschung10, Forschung11, Forschung12, Forschung13, Forschung14, Forschung15,
       Lager1, Lager2, Lager3, Lager4, Lager5, Lager6, Lager7, Lager8, Lager9, Lager10, Lager11, Lager12, Lager13, Lager14, Lager15,
       Gebaeude3,  Gebaeude6
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getInformationForBuero(int $id): ?array
    {
        $stmt = $this->prepare("SELECT *
            FROM mitglieder m INNER JOIN statistik s ON s.user_id = m.ID
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNextMafiaAndMoneyAndBank(int $id): ?array
    {
        $stmt = $this->prepare("SELECT NextMafia, Geld, Bank from mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function existsPlayerByNameOrEmail(string $name, string $email): ?bool
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Name = :name OR EMail = :email");
        $stmt->bindParam("name", $name);
        $stmt->bindParam("email", $email);
        return $this->executeAndExtractField($stmt, 'count') > 0;
    }

    public function getPlayerIDByName(string $name): ?int
    {
        $stmt = $this->prepare("SELECT ID FROM mitglieder WHERE Name = :name AND ID > 0");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractField($stmt, 'ID');
    }

    public function getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller(int $blm_user, int $minWas = 0, int $maxWas = 999): ?array
    {
        $stmt = $this->prepare("SELECT * FROM auftrag WHERE user_id = :id AND item >= :min AND item < :max ORDER BY finished");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        $stmt->bindParam("min", $minWas, PDO::PARAM_INT);
        $stmt->bindParam("max", $maxWas, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllExpiredAuftraegeByVon(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM auftrag WHERE user_id = :id AND finished <= CURRENT_TIMESTAMP()");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getPlayerMoneyAndResearchLevelsAndPlantageLevelAndResearchLabLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Geld, Forschung1, Forschung2, Forschung3, Forschung4, Forschung5, Forschung6, 
            Forschung7, Forschung8, Forschung9, Forschung10, Forschung11, Forschung12, 
            Forschung13, Forschung14, Forschung15, Gebaeude1, Gebaeude2
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerBankAndMoneyGroupIdAndBioladenLevelAndDoenerstandLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Bank, Geld, Gruppe, Gebaeude3, Gebaeude4
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getUnreadMessageCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count FROM nachrichten WHERE An = :id AND Gelesen = 0");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getOpenContractCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count FROM vertraege WHERE An = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getOnlinePlayerCount(): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count
            FROM mitglieder
            WHERE LastAction >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getUnreadGroupMessageCount(int $group_id, int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count
            FROM gruppe_nachrichten
            WHERE Gruppe = :groupId AND Zeit > (SELECT m.GruppeLastMessageZeit FROM mitglieder m WHERE id = :userId)");
        $stmt->bindParam("groupId", $group_id, PDO::PARAM_INT);
        $stmt->bindParam("userId", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getServerStatistics(): ?array
    {
        $stmt = $this->prepare("SELECT (SELECT SUM(AusgabenGebaeude + AusgabenForschung + AusgabenZinsen + AusgabenProduktion + AusgabenMarkt +
                   AusgabenVertraege + AusgabenMafia)
        FROM statistik)                                           AS AusgabenGesamt,
       (SELECT SUM(EinnahmenGebaeude + EinnahmenVerkauf + EinnahmenZinsen + EinnahmenMarkt + EinnahmenVertraege +
                   EinnahmenMafia)
        FROM statistik)                                           AS EinnahmenGesamt,
       (SELECT SUM(Forschung1 + Forschung2 + Forschung3 + Forschung4 + Forschung5 + Forschung6 + Forschung7 +
                   Forschung8 + Forschung9 + Forschung10 + Forschung11 + Forschung12 + Forschung13 + Forschung14 +
                   Forschung15)
        FROM mitglieder)                                          AS GesamtForschung,
       (SELECT SUM(AusgabenForschung) FROM statistik)             AS AusgabenForschung,
       (SELECT COUNT(*) FROM mitglieder WHERE ID > 0)             AS AnzahlSpieler,
       (SELECT SUM(IGMGesendet) FROM mitglieder)                  AS AnzahlIGMs,
       (SELECT SUM(AusgabenGebaeude) FROM statistik)              AS AusgabenGebaeude,
       (SELECT SUM(Gebaeude1 + Gebaeude2 + Gebaeude3 + Gebaeude4 + Gebaeude5 + Gebaeude6 + Gebaeude7 + Gebaeude8)
        FROM mitglieder)                                          AS GesamtGebaeude,
       (SELECT COUNT(*) FROM gruppe)                              AS AnzahlGruppen,
       (SELECT COUNT(*) FROM mitglieder WHERE Gruppe IS NOT NULL) AS AnzahlSpielerInGruppe;
");
        $result = $this->executeAndExtractRows($stmt);
        if (count($result) == 0) {
            return null;
        } else {
            $stmt = $this->prepare("SHOW TABLE STATUS FROM `" . database_database . "` WHERE `name` = 'auftrag'");
            $result[0]['AnzahlAuftraege'] = $this->executeAndExtractField($stmt, 'Auto_increment');
            return $result[0];
        }
    }

    public function getAllContractsByAnEquals(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.ID AS VonID, m.Name AS VonName
            FROM vertraege v INNER JOIN mitglieder m ON v.Von = m.ID
            WHERE An = :id ORDER BY Wann");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllContractsByVonEquals(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.ID AS AnID, m.Name AS AnName
            FROM vertraege v INNER JOIN mitglieder m ON v.An = m.ID
            WHERE Von = :id ORDER BY Wann");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getContractByIdAndAn(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.Name AS AnName
            FROM vertraege v LEFT OUTER JOIN mitglieder m ON v.An = m.ID
            WHERE v.ID = :id AND v.An = :user");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getContractByIDAndAnOrVonEquals(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM vertraege WHERE ID = :id AND (An = :user OR Von = :user)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getMessageByIdAndAnOrVonEquals(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT n.*, mVon.ID AS VonID, coalesce(mVon.Name, 'Gelöscht') AS VonName, mAn.ID AS AnID, coalesce(mAn.Name, 'Gelöscht') AS AnName
            FROM (nachrichten n LEFT OUTER JOIN mitglieder mVon ON n.Von = mVon.ID) LEFT OUTER JOIN mitglieder mAn ON n.An = mAn.ID
            WHERE n.ID = :id AND (n.An = :user OR n.Von = :user)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllPlayerIdAndBankAndBioladenAndDoenerstand(): ?array
    {
        return $this->executeAndExtractRows($this->prepare("SELECT ID, Bank, Gebaeude3, Gebaeude4 FROM mitglieder"));
    }

    public function getAllPlayerIdAndResearchLevels(): ?array
    {
        return $this->executeAndExtractRows($this->prepare("SELECT ID,
        Forschung1, Forschung2, Forschung3, Forschung4, Forschung5, Forschung6, 
        Forschung7, Forschung8, Forschung9, Forschung10, Forschung11, Forschung12, 
        Forschung13, Forschung14, Forschung15 FROM mitglieder"));
    }

    public function getAllPlayerIdAndNameBankSmallerEquals(float $amount): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM mitglieder WHERE Bank <= :amount AND ID > 0");
        $stmt->bindParam('amount', $amount);
        return $this->executeAndExtractRows($stmt);
    }

    public function getPlayerCardByID(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, coalesce(m.Beschreibung, '[i]Keine[/i]') AS Beschreibung, 
                m.RegistriertAm, m.Punkte, m.Verwarnungen, m.Gesperrt, m.LastLogin, m.IgmGesendet, m.IgmEmpfangen,
                g.ID AS GruppeID, coalesce(g.Name, 'Keine') AS GruppeName, m.LastImageChange
            FROM mitglieder m LEFT OUTER JOIN gruppe g ON m.Gruppe = g.ID
            WHERE m.ID = :id
            AND m.ID > 0");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerEspionageDataByID(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Geld, Gebaeude1, Gebaeude2, Gebaeude3, Gebaeude4,
            Gebaeude5, Gebaeude6, Gebaeude7, Gebaeude8,
            Lager1, Lager2, Lager3, Lager4, Lager5, Lager6, 
            Lager7, Lager8, Lager9, Lager10, Lager11, Lager12, 
            Lager13, Lager14, Lager15
            FROM mitglieder WHERE ID = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupDiplomacyTypeById(int $left, int $right): ?int
    {
        $stmt = $this->prepare("SELECT `Typ` FROM gruppe_diplomatie WHERE (Von = :left OR Von = :right) AND (An = :left OR An = :right) AND Aktiv = 1");
        $stmt->bindParam('left', $left, PDO::PARAM_INT);
        $stmt->bindParam('right', $right, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Typ');
    }

    public function getGroupDiplomacyById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT d.*, gAn.ID AS GruppeAnId, gAn.Name AS GruppeAnName, gVon.ID AS GruppeVonId, gVon.Name AS GruppeVonName
            FROM (gruppe_diplomatie d LEFT OUTER JOIN gruppe gAn ON d.An = gAn.ID) LEFT OUTER JOIN gruppe gVon ON gVon.ID = d.Von
            WHERE d.ID = :id");
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupDiplomacyByIdAndAn(int $id, int $an): ?array
    {
        $stmt = $this->prepare("SELECT d.*, gAn.ID AS GruppeAnId, gAn.Name AS GruppeAnName, gVon.ID AS GruppeVonId, gVon.Name AS GruppeVonName
            FROM (gruppe_diplomatie d LEFT OUTER JOIN gruppe gAn ON d.An = gAn.ID) LEFT OUTER JOIN gruppe gVon ON gVon.ID = d.Von
            WHERE d.ID = :id AND d.An = :an");
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->bindParam('an', $an, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllPlayerIdAndNameWhereMafiaPossible(float $myPoints, int $myId, ?int $myGroup, float $pointsRange): ?array
    {
        $lowPoints = $myPoints / $pointsRange;
        $highPoints = $myPoints * $pointsRange;
        $stmt = $this->prepare("SELECT ID, Name, Gruppe, Punkte
FROM mitglieder m
WHERE (
            coalesce(m.Gruppe, -2) != coalesce(:myGroup, -1) -- exclude my own group
        AND m.ID != :myId -- exclude myself (if user isn't in a group)
        AND (m.Punkte >= :lowPoints AND m.Punkte <= :highPoints -- include by points range
        AND m.Gruppe NOT IN -- exclude NAP and BND
            (SELECT d.Von
             FROM gruppe_diplomatie d
             WHERE d.Von = m.Gruppe
               AND d.An = coalesce(:myGroup, -1)
               AND d.Aktiv = 1
               AND d.typ != " . group_diplomacy_war . "
             UNION
             SELECT d.An
             FROM gruppe_diplomatie d
             WHERE d.An = m.Gruppe
               AND d.Von = coalesce(:myGroup, -1)
               AND d.Aktiv = 1
               AND d.typ != " . group_diplomacy_war . ")
                )
    )
   OR
   -- include all WAR opponents, regardless of points
        coalesce(m.Gruppe, -2) IN
        (SELECT d.Von
         FROM gruppe_diplomatie d
         WHERE d.Von = m.Gruppe
           AND d.An = coalesce(:myGroup, -1)
           AND d.Aktiv = 1
           AND d.typ = " . group_diplomacy_war . "
         UNION
         SELECT d.An
         FROM gruppe_diplomatie d
         WHERE d.An = m.Gruppe
           AND d.Von = coalesce(:myGroup, -1)
           AND d.Aktiv = 1
           AND d.typ = " . group_diplomacy_war . ")
ORDER BY m.Name");
        $stmt->bindParam('myId', $myId, PDO::PARAM_INT);
        $stmt->bindParam('myGroup', $myGroup, PDO::PARAM_INT);
        $stmt->bindParam('lowPoints', $lowPoints);
        $stmt->bindParam('highPoints', $highPoints);
        return $this->executeAndExtractRows($stmt);
    }

    public function updatePlayerOnlinezeit(int $blm_user): ?int
    {
        $stmt = $this->prepare("UPDATE mitglieder SET OnlineZeitSinceLastCron = OnlineZeitSinceLastCron + TIMESTAMPDIFF(SECOND, LastAction, NOW()), LastAction = NOW() WHERE ID = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function updatePlayerOnlineTimes(): ?int
    {
        return $this->executeAndGetAffectedRows($this->prepare("UPDATE mitglieder SET OnlineZeit = OnlineZeit + OnlineZeitSinceLastCron, OnlineZeitSinceLastCron = 0"));
    }

    public function countPendingGroupDiplomacy(int $group_id): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS Count FROM gruppe_diplomatie WHERE An = :id AND Aktiv = 0");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Count');
    }

    public function deleteAllMessagesForUser(int $blm_user): ?int
    {
        $stmt = $this->prepare("DELETE FROM nachrichten WHERE An = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function getSitterPermissions(int $blm_user, string $right): ?string
    {
        $stmt = $this->prepare("SELECT * FROM sitter WHERE user_id = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, $right);
    }

    public function deleteGroup(int $group_id): ?string
    {
        $stmt = $this->prepare("DELETE FROM gruppe_diplomatie WHERE Von = :id OR An = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_diplomatie';
        }
        $stmt = $this->prepare("DELETE FROM gruppe_logbuch WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_logbuch';
        }
        $stmt = $this->prepare("DELETE FROM gruppe_nachrichten WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_nachrichten';
        }
        $stmt = $this->prepare("DELETE FROM gruppe_rechte WHERE group_id = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_rechte';
        }
        $stmt = $this->prepare("DELETE FROM gruppe_kasse WHERE group_id = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_kasse';
        }
        $stmt = $this->prepare("DELETE FROM gruppe WHERE ID = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe';
        }
        $stmt = $this->prepare("UPDATE mitglieder SET Gruppe = NULL WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'mitglieder';
        }
        return null;
    }

    public function getQueryCount(): int
    {
        return $this->queries;
    }

    private function prepare(string $sql): ?PDOStatement
    {
        $this->queries++;
        $stmt = $this->link->prepare($sql);
        if ($stmt === false) {
            $this->error($this->link, "Could not prepare statement: " . $sql);
            return null;
        }
        return $stmt;
    }

    private function error($handle, string $text): void
    {
        $errorInfo = $handle->errorInfo();
        if (sizeof($errorInfo) > 0 && $errorInfo[0] != '00000') {
            $text .= " (" . var_export($errorInfo, true) . ")";
        }
        $file = __FILE__;
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        for ($i = 1; $i < count($bt); $i++) {
            if ($bt[$i]["file"] != $file) {
                break;
            }
        }
        $text = str_replace(["\n ", "\n"], [" ", ""], $text);
        trigger_error(sprintf("%s:%d | %s", basename($bt[$i]["file"]), $bt[$i]["line"], $text), E_USER_WARNING);
    }

    private function executeAndExtractField(PDOStatement $stmt, string $fieldName, array $executeParam = array()): ?string
    {
        if (count($executeParam) == 0) {
            $executeResult = $stmt->execute();
        } else {
            $executeResult = $stmt->execute($executeParam);
        }
        if (!$executeResult) {
            $this->error($stmt, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return null;
        }
        return $result[$fieldName];
    }

    public function executeAndGetAffectedRows(PDOStatement $stmt, array $executeParam = array()): ?int
    {
        if (count($executeParam) == 0) {
            $executeResult = $stmt->execute();
        } else {
            $executeResult = $stmt->execute($executeParam);
        }
        if (!$executeResult) {
            $this->error($stmt, "Could not execute statement");
            return null;
        }
        return $stmt->rowCount();
    }

    private function executeAndExtractRows(PDOStatement $stmt): ?array
    {
        if (!$stmt->execute()) {
            $this->error($stmt, "Could not execute statement");
            return null;
        }
        $results = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    private function executeAndExtractFirstRow(PDOStatement $stmt): ?array
    {
        $result = $this->executeAndExtractRows($stmt);
        if ($result === null || count($result) == 0) {
            return null;
        } else {
            return $result[0];
        }
    }
}
