<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

require_once __DIR__ . '/config.class.php';

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
    public const TABLE_LOG_MARKET = 'log_marktplatz';
    public const TABLE_LOG_MESSAGES = 'log_nachrichten';
    public const TABLE_LOG_CONTRACTS = 'log_vertraege';
    public const TABLE_UPDATE_INFO = 'update_info';
    public const TABLE_RUNTIME_CONFIG = 'runtime_config';

    private static ?Database $INSTANCE = null;

    public static function getInstanceForInstallCheck(): Database
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new Database(false);
        }
        return self::$INSTANCE;
    }

    public static function getInstance(): Database
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new Database(true);
        }
        return self::$INSTANCE;
    }

    private PDO $link;
    private int $queries = 0;
    private ?string $sql = null;
    private array $warnings = array();
    private float $slow_query_threshold;

    function __construct(bool $dieOnInitError)
    {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                Config::get(Config::SECTION_DATABASE, 'hostname'),
                Config::get(Config::SECTION_DATABASE, 'database'),
            );
            $this->link = new PDO(
                $dsn,
                Config::get(Config::SECTION_DATABASE, 'username'),
                Config::get(Config::SECTION_DATABASE, 'password'),
                array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            $this->link->query("SET time_zone = '" . date_default_timezone_get() . "'");
            $this->queries++;
            $this->slow_query_threshold = Config::getFloat(Config::SECTION_DATABASE, 'slow_query_threshold') / 1000;

            // load runtime configuration
            $stmt = $this->prepare("SELECT conf_name, conf_value FROM " . self::TABLE_RUNTIME_CONFIG);
            @Config::enhanceFromDb($this->executeAndExtractRows($stmt));
        } catch (PDOException $e) {
            if ($dieOnInitError) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    function __destruct()
    {
        if ($this->link->inTransaction()) {
            $this->link->rollBack();
        }
        $this->queries = 0;
        $this->warnings = array();
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

    public function getWarnings(): array
    {
        return $this->warnings;
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
            $stmt = $this->prepare(sprintf("UPDATE %s SET %s WHERE 1=1%s", $table, implode(", ", $fields), implode(" ", $wheres)));
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
        if ($id === 0) {
            return 'System';
        }
        $stmt = $this->prepare("SELECT Name FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function getPlayerAndSitterPasswordsById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Passwort AS 'Benutzer', s.Passwort as 'Sitter'
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_SITTER . " s ON m.ID = s.user_id
            WHERE m.ID = :id
            AND m.ID > 0");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAuftragByIdAndVon(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_JOBS . " WHERE ID = :id AND user_id = :user");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerIdByNameOrEmailAndActivationToken(?string $name, ?string $email, string $code): ?int
    {
        $stmt = $this->prepare("SELECT ID FROM " . self::TABLE_USERS . " WHERE ID > 0 AND (Name = :name OR EMail = :email) AND EMailAct = :code");
        $stmt->bindParam("name", $name);
        $stmt->bindParam("email", $email);
        $stmt->bindParam("code", $code);
        return $this->executeAndExtractField($stmt, 'ID');
    }

    public function getPasswordRequestByUserId(int $id): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_PASSWORD_RESET . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerIdAndNameByEmail(string $email): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM " . self::TABLE_USERS . " WHERE ID > 0 AND EMail = :email");
        $stmt->bindParam("email", $email);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndEmailById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Name, EMail FROM " . self::TABLE_USERS . " WHERE ID > 0 AND ID = :id");
        $stmt->bindParam("id", $id);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerRankById(int $id): ?int
    {
        $stmt = $this->prepare("with stats as (
                SELECT ROW_NUMBER() OVER (ORDER BY Punkte DESC, ID) AS rnum, ID, Punkte FROM " . self::TABLE_USERS . " WHERE ID > 0 AND EmailAct IS NULL
            ) select * from stats where ID = :id");
        $stmt->bindParam("id", $id);
        return $this->executeAndExtractField($stmt, 'rnum');
    }

    public function getPlayerRankByName(string $name): ?int
    {
        $stmt = $this->prepare("with stats as (
                SELECT ROW_NUMBER() OVER (ORDER BY Punkte DESC, ID) AS rnum, ID, Name, Punkte FROM " . self::TABLE_USERS . " WHERE ID > 0 AND EmailAct IS NULL
            ) select * from stats where Name = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractField($stmt, 'rnum');
    }

    public function getPlayerPointsAndNameAndMoneyAndGruppeAndZaunById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, Geld, Gruppe, Gebaeude7 FROM " . self::TABLE_USERS . " WHERE ID = :id AND ID > 0");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerCount(): ?int
    {
        return $this->executeAndExtractField($this->prepare("SELECT count(1) AS count FROM " . self::TABLE_USERS . " WHERE ID > 0"), 'count');
    }

    public function getAllMessagesByAnCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_MESSAGES . " WHERE An = :id ORDER BY Zeit DESC");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllMessagesByAnEntries(int $blm_user, string $page, string $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS VonID, coalesce(m.Name, 'Gelöscht') AS VonName
            FROM " . self::TABLE_MESSAGES . " n LEFT OUTER JOIN " . self::TABLE_USERS . " m ON n.Von = m.ID
            WHERE n.An = :id ORDER BY n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam("id", $blm_user);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllMessagesByVonCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_MESSAGES . " WHERE Von = :id ORDER BY Zeit DESC");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllMessagesByVonEntries(int $blm_user, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS AnID, coalesce(m.Name, 'Gelöscht') AS AnName
            FROM " . self::TABLE_MESSAGES . " n LEFT OUTER JOIN " . self::TABLE_USERS . " m ON n.An = m.ID
            WHERE n.Von = :id ORDER BY n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam("id", $blm_user);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getRanglisteUserEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT m.Name AS BenutzerName, m.ID AS BenutzerID, m.LastAction, 
                m.Admin AS IstAdmin, m.Betatester AS IstBetatester, m.Punkte, g.Kuerzel AS GruppeName, m.Gruppe AS GruppeID
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_GROUP . " g ON m.Gruppe = g.ID
            WHERE m.ID > 0 AND m.EmailAct IS NULL ORDER BY m.Punkte DESC, m.ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getEwigePunkteEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT ID, Name, EwigePunkte
            FROM " . self::TABLE_USERS . " WHERE EwigePunkte > 0 ORDER BY EwigePunkte DESC, ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getRanglisteGroupEntries(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("WITH stats AS (
    SELECT m.Gruppe AS GruppeID, SUM(m.Punkte) AS Punkte, COUNT(1) AS AnzMitglieder, CONCAT(';', GROUP_CONCAT(m.ID SEPARATOR ';'), ';') AS Mitglieder
    FROM " . self::TABLE_USERS . " m
    WHERE Gruppe IS NOT NULL
    GROUP BY m.Gruppe
)
SELECT s.*, g.Kuerzel AS GruppeKuerzel, g.Name AS GruppeName FROM stats s INNER JOIN " . self::TABLE_GROUP . " g ON s.GruppeID = g.ID ORDER BY s.Punkte DESC, AnzMitglieder LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupIdAndNameAndKuerzelAndErstellt(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT ID, Name, Kuerzel, Erstellt
            FROM " . self::TABLE_GROUP . " ORDER BY ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getLeaderOnlineTime(int $count = 1): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Onlinezeit
            FROM " . self::TABLE_USERS . "
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
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
            FROM " . self::TABLE_USERS . "
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

    public function getAdminBioladenLogCount(?string $werFilter, ?int $wareFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_SHOP . " WHERE ((1))",
            array(
                "playerName LIKE :wer" => $werFilter,
                "item = :ware" => $wareFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBioladenLogEntries(?string $werFilter, ?int $wareFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_SHOP . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "playerName LIKE :wer" => $werFilter,
                "item = :ware" => $wareFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminBankLogCount(?string $werFilter, ?string $wohinFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_BANK . " WHERE ((1))",
            array(
                "playerName LIKE :wer" => $werFilter,
                "target = :wohin" => $wohinFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBankLogEntries(?string $werFilter, ?string $wohinFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_BANK . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "playerName LIKE :wer" => $werFilter,
                "target = :wohin" => $wohinFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminGroupTreasuryLogCount(?string $werFilter, ?string $wenFilter, ?int $groupFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_GROUP_CASH . " WHERE ((1))",
            array(
                "groupId = :gruppe" => $groupFilter,
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminGroupTreasuryLogEntries(?string $werFilter, ?string $wenFilter, ?int $groupFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_GROUP_CASH . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "groupId = :gruppe" => $groupFilter,
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminLoginLogCount(?string $werFilter, ?string $ipFilter, ?int $artFilter, ?int $successFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_LOGIN . " WHERE ((1))",
            array(
                "playerName LIKE :wer" => $werFilter,
                "IP LIKE :ip" => $ipFilter,
                "sitter = :art" => $artFilter,
                "success = :success" => $successFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminLoginLogEntries(?string $werFilter, ?string $ipFilter, ?int $artFilter, ?int $successFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_LOGIN . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "playerName LIKE :wer" => $werFilter,
                "IP LIKE :ip" => $ipFilter,
                "sitter = :art" => $artFilter,
                "success = :success" => $successFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminMafiaLogCount(?string $werFilter, ?string $wenFilter, ?string $artFilter, ?int $filter_success): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_MAFIA . " WHERE ((1))",
            array(
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
                "action = :art" => $artFilter,
                "success = :success" => $filter_success,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminMafiaLogEntries(?string $werFilter, ?string $wenFilter, ?string $artFilter, ?int $filter_success, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_MAFIA . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
                "action = :art" => $artFilter,
                "success = :success" => $filter_success,
            )
        );

        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminVertraegeLogCount(?string $werFilter, ?string $wenFilter, ?int $wareFilter, ?int $angenommenFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_CONTRACTS . " WHERE ((1))",
            array(
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
                "item = :ware" => $wareFilter,
                "accepted = :angenommen" => $angenommenFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminVertraegeLogEntries(?string $werFilter, ?string $wenFilter, ?int $wareFilter, ?int $angenommenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_CONTRACTS . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "senderName LIKE :wer" => $werFilter,
                "receiverName LIKE :wen" => $wenFilter,
                "item = :ware" => $wareFilter,
                "accepted = :angenommen" => $angenommenFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminMarketLogCount(?string $verkaeuferFilter, ?string $kaeuferFilter, ?int $wareFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_MARKET . " WHERE ((1))",
            array(
                "sellerName LIKE :wer" => $verkaeuferFilter,
                "buyerName LIKE :wen" => $kaeuferFilter,
                "item = :ware" => $wareFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminMarketLogEntries(?string $verkaeuferFilter, ?string $kaeuferFilter, ?int $wareFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_MARKET . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "sellerName LIKE :wer" => $verkaeuferFilter,
                "buyerName LIKE :wen" => $kaeuferFilter,
                "item = :ware" => $wareFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminMessageLogCount(?string $senderFilter, ?string $receiverFilter): ?int
    {
        $stmt = $this->prepareAndBind("SELECT count(1) AS count FROM " . self::TABLE_LOG_MESSAGES . " WHERE ((1))",
            array(
                "senderName LIKE :wer" => $senderFilter,
                "receiverName LIKE :wen" => $receiverFilter,
            )
        );
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminMessageLogEntries(?string $senderFilter, ?string $receiverFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepareAndBind("SELECT * FROM " . self::TABLE_LOG_MESSAGES . " WHERE ((1)) ORDER BY created DESC LIMIT :offset, :count",
            array(
                "senderName LIKE :wer" => $senderFilter,
                "receiverName LIKE :wen" => $receiverFilter,
            )
        );
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getMarktplatzEntryById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Von, Menge, Was, Preis FROM " . self::TABLE_MARKET . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getMarktplatzEntryByIdAndVon(int $id, int $von): ?array
    {
        $stmt = $this->prepare("SELECT ID, Von, Menge, Was, Preis FROM " . self::TABLE_MARKET . " WHERE ID = :id AND Von = :von");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("von", $von, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getVertragEntryById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name AS Von, An AS AnId, m2.Name AS AnName, Menge, Was, Preis
            FROM (" . self::TABLE_CONTRACTS . " v JOIN " . self::TABLE_USERS . " m1 ON m1.ID = v.Von) JOIN " . self::TABLE_USERS . " m2 ON m2.ID = v.An
            WHERE v.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerEmailAndBeschreibungAndSitterSettingsById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.EMail, m.Beschreibung, s.*
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_SITTER . " s ON m.ID = s.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $blm_user);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getNotizblock(int $id): ?string
    {
        $stmt = $this->prepare("SELECT Notizblock FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Notizblock');
    }

    public function getMarktplatzCount(array $warenFilter = array()): ?int
    {
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_MARKET);
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_MARKET . "
                         WHERE Was IN (" . str_repeat('?, ', count($warenFilter) - 1) . "?)");
        }
        return $this->executeAndExtractField($stmt, 'count', $warenFilter);
    }

    public function getMarktplatzEntries(array $warenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM " . self::TABLE_MARKET . " m1 JOIN " . self::TABLE_USERS . " m2 ON m2.ID = m1.Von ORDER BY ID DESC LIMIT :offset, :count");
        } else {
            $fields = array();
            for ($i = 0; $i < count($warenFilter); $i++) {
                $fields[] = sprintf(':ware_%d', $i);
            }
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM " . self::TABLE_MARKET . " m1 JOIN " . self::TABLE_USERS . " m2 ON m2.ID = m1.Von
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
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_GROUP_MESSAGES . " WHERE Gruppe = :id");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getGroupMessageEntries(int $groupId, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT n.*, m.ID AS VonID, coalesce(m.Name, 'Gelöscht') AS VonName
            FROM " . self::TABLE_GROUP_MESSAGES . " n LEFT OUTER JOIN " . self::TABLE_USERS . " m ON n.Von = m.ID
            WHERE n.Gruppe = :id ORDER BY n.Festgepinnt DESC, n.Zeit DESC LIMIT :offset, :count");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupLogCount(int $groupId): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_GROUP_LOG . " WHERE Gruppe = :id");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getGroupLogEntries(int $groupId, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_GROUP_LOG . " WHERE Gruppe = :id ORDER BY Datum DESC LIMIT :offset, :count");
        $stmt->bindParam('id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getVertragCount(string $werFilter, string $wenFilter): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_CONTRACTS . " WHERE Von LIKE :wer AND AN LIKE :wen");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getVertragEntries(string $werFilter, string $wenFilter, int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name as VonName, An AS AnId, m2.Name AS AnName, Was, Menge, Preis, Menge * Preis AS Gesamtpreis 
            FROM (" . self::TABLE_CONTRACTS . " v JOIN " . self::TABLE_USERS . " m1 ON m1.ID = v.Von) JOIN " . self::TABLE_USERS . " m2 ON m2.Id = v.An
            WHERE Von LIKE :wer AND AN LIKE :wen LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupCount(): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_GROUP);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getEwigePunkteCount(): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_USERS . " WHERE EwigePunkte > 0");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllGroupIdsAndName(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM " . self::TABLE_GROUP);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndName(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM " . self::TABLE_USERS . " WHERE ID > 0 ORDER BY Name");
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndNameAndEMailAndRegistriertAmAndGesperrtAndVerwarnungen(int $page, int $entriesPerPage): ?array
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT ID, Name, EMail, RegistriertAm, Gesperrt, Verwarnungen FROM " . self::TABLE_USERS . " WHERE ID > 0 ORDER BY ID LIMIT :offset, :count");
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndNameAndEmailAndEmailActAndLastLogin(): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, EMail, EMailAct, LastLogin FROM " . self::TABLE_USERS . " WHERE ID > 0 ORDER BY Name");
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupIdAndPasswordByNameOrTag(string $name): ?array
    {
        $stmt = $this->prepare("SELECT ID, Passwort FROM " . self::TABLE_GROUP . " WHERE Name = :name OR Kuerzel = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupIdAndNameByNameOrTag(string $name): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM " . self::TABLE_GROUP . " WHERE Name = :name OR Kuerzel = :name");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupIdAndNameById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name FROM " . self::TABLE_GROUP . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerDataByName(string $name): ?array
    {
        $stmt = $this->prepare("SELECT m.ID AS ID, Name, Admin, EMailAct, Gesperrt, m.Passwort AS user_password, s.Passwort AS sitter_password
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_SITTER . " s ON m.ID = s.user_id
            WHERE m.Name = :name
            AND m.ID > 0");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerDataById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_USERS . " WHERE ID = :id AND ID > 0");
        $stmt->bindParam("id", $id);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndBankAndMoneyAndGroupById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Name, Bank, Geld, Gruppe, Gebaeude9 FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndGroupIdAndGroupRightsById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Name, m.Gruppe, r.*
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_GROUP_RIGHTS . " r ON m.Gruppe = r.group_id AND m.ID = r.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerStock(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT " . getAllStockFields() . " FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerPlantageAndBauhofLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Gebaeude1, Gebaeude5 FROM " . self::TABLE_USERS . " WHERE ID = :id AND ID > 0");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerPointsAndMoneyAndNextMafiaAndGroupById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Punkte, Geld, NextMafia, Gruppe from " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNameAndPointsAndGruppeAndPlantageLevelById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.Name, m.Punkte, m.Gebaeude1, gr.ID AS GruppeID, gr.Name AS GruppeName
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_GROUP . " gr ON m.Gruppe = gr.ID
            WHERE m.ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupInformationById(int $group): ?array
    {
        $stmt = $this->prepare("SELECT g.ID, g.Name, g.Kuerzel, g.Erstellt, g.Beschreibung, (SELECT SUM(Punkte) FROM " . self::TABLE_USERS . " m WHERE m.Gruppe = g.ID) AS Punkte, g.LastImageChange, g.Kasse
            FROM " . self::TABLE_GROUP . " g INNER JOIN " . self::TABLE_USERS . " m ON g.ID = m.Gruppe
            WHERE g.ID = :id");
        $stmt->bindParam("id", $group, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupRightsByUserId(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_GROUP_RIGHTS . " WHERE user_id = :uid");
        $stmt->bindParam("uid", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllGroupRightsByGroupId(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT r.*, m.ID AS UserId, coalesce(m.Name, 'Gelöscht') AS UserName
            FROM " . self::TABLE_GROUP_RIGHTS . " r LEFT OUTER JOIN " . self::TABLE_USERS . " m ON r.user_id = m.ID
            WHERE r.group_id = :gid");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupCashById(int $group_id): ?float
    {
        $stmt = $this->prepare("SELECT Kasse FROM " . self::TABLE_GROUP . " WHERE ID = :gid");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Kasse');
    }

    public function getGroupCashSumByUserId(int $blm_user): ?float
    {
        $stmt = $this->prepare("SELECT coalesce(SUM(amount), 0) AS summe FROM " . self::TABLE_GROUP_CASH . " WHERE user_id = :uid");
        $stmt->bindParam("uid", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'summe');
    }

    public function getAllGroupCashById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT k.*, m.ID AS UserID, coalesce(m.Name, 'Gelöscht') AS UserName, if(m.Gruppe = g.ID, 1, 0) AS IstMitglied
            FROM (" . self::TABLE_GROUP_CASH . " k LEFT OUTER JOIN " . self::TABLE_USERS . " m ON k.user_id = m.ID) LEFT OUTER JOIN " . self::TABLE_GROUP . " g On k.group_id = g.ID
            WHERE k.group_id = :gid ORDER BY k.amount DESC");
        $stmt->bindParam("gid", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupMembersById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, LastAction FROM " . self::TABLE_USERS . " WHERE Gruppe = :id ORDER BY Punkte DESC");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupMemberCountById(int $group_id): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_USERS . " WHERE Gruppe = :id ORDER BY Punkte DESC");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllGroupDiplomacyById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT gd.ID, gd.Von, gd.An, gr.ID AS GruppeID, gr.Name AS GruppeName, gd.Typ, gd.Aktiv, gd.Seit, gd.Betrag
            FROM " . self::TABLE_GROUP_DIPLOMACY . " gd INNER JOIN " . self::TABLE_GROUP . " gr ON gd.An = gr.ID
            WHERE gd.Von = :id
            UNION
            SELECT gd.ID, gd.Von, gd.An, gr.ID AS GruppeID, gr.Name AS GruppeName, gd.Typ, gd.Aktiv, gd.Seit, gd.Betrag
            FROM " . self::TABLE_GROUP_DIPLOMACY . " gd INNER JOIN " . self::TABLE_GROUP . " gr ON gd.Von = gr.ID
            WHERE gd.An = :id");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPendingGroupDiplomacyById(int $group_id): ?array
    {
        $stmt = $this->prepare("SELECT gd.ID, gd.Von, gr.ID AS VonId, gr.Name AS VonName, gd.Typ, gd.Seit
            FROM " . self::TABLE_GROUP_DIPLOMACY . " gd INNER JOIN " . self::TABLE_GROUP . " gr ON gd.Von = gr.ID
            WHERE gd.An = :id AND gd.Aktiv = 0");
        $stmt->bindParam("id", $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getPlayerPointsAndGruppeAndMoneyAndNextMafiaAndPizzeriaById(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Punkte, Gruppe, Geld, NextMafia, Gebaeude8
            FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerMoneyAndBuildingLevelsAndPointsAndEinnahmenZinsen(int $id): ?array
    {
        $stmt = $this->prepare("SELECT m.Geld, m.Punkte, " . getAllBuildingFields() . ", s.EinnahmenZinsen
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerMoneyAndResearchLevelsAndPlantageLevel(int $id): ?array
    {
        $stmt = $this->prepare("SELECT Geld, Gebaeude1, " . getAllResearchFields() . " FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel(int $id): ?array
    {
        $stmt = $this->prepare("SELECT " . getAllResearchFields() . ", " . getAllStockFields() . ", Gebaeude3,  Gebaeude6
            FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getInformationForBuero(int $id): ?array
    {
        $stmt = $this->prepare("SELECT *
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON s.user_id = m.ID
            WHERE m.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerNextMafiaAndMoneyAndBank(int $id): ?array
    {
        $stmt = $this->prepare("SELECT NextMafia, Geld, Bank from " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function existsPlayerByNameOrEmail(string $name, string $email): ?bool
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM " . self::TABLE_USERS . " WHERE Name = :name OR EMail = :email");
        $stmt->bindParam("name", $name);
        $stmt->bindParam("email", $email);
        return $this->executeAndExtractField($stmt, 'count') > 0;
    }

    public function getPlayerIDByName(string $name): ?int
    {
        $stmt = $this->prepare("SELECT ID FROM " . self::TABLE_USERS . " WHERE Name = :name AND ID > 0");
        $stmt->bindParam("name", $name);
        return $this->executeAndExtractField($stmt, 'ID');
    }

    public function getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller(int $blm_user, int $minWas = 0, int $maxWas = 999): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_JOBS . " WHERE user_id = :id AND item >= :min AND item < :max ORDER BY finished");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        $stmt->bindParam("min", $minWas, PDO::PARAM_INT);
        $stmt->bindParam("max", $maxWas, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllExpiredAuftraegeByVon(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_JOBS . " WHERE user_id = :id AND finished <= CURRENT_TIMESTAMP()");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getPlayerMoneyAndResearchLevelsAndPlantageLevelAndResearchLabLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Geld, " . getAllResearchFields() . ", Gebaeude1, Gebaeude2
            FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerBankAndMoneyGroupIdAndBioladenLevelAndDoenerstandLevel(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT Bank, Geld, Gruppe, Gebaeude3, Gebaeude4
            FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getUnreadMessageCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count FROM " . self::TABLE_MESSAGES . " WHERE An = :id AND Gelesen = 0");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getOpenContractCount(int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count FROM " . self::TABLE_CONTRACTS . " WHERE An = :id");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getOnlinePlayerCount(): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count
            FROM " . self::TABLE_USERS . "
            WHERE LastAction >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getUnreadGroupMessageCount(int $group_id, int $blm_user): ?int
    {
        $stmt = $this->prepare("SELECT COUNT(1) AS count
            FROM " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_GROUP_MESSAGES . " n ON m.Gruppe = n.Gruppe
            WHERE m.ID = :userId AND m.Gruppe = :groupId AND n.Zeit > m.GruppeLastMessageZeit");
        $stmt->bindParam("groupId", $group_id, PDO::PARAM_INT);
        $stmt->bindParam("userId", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getServerStatistics(): ?array
    {
        // @formatter:off
        $stmt = $this->prepare("with
glob_stats as (
    SELECT
        SUM(AusgabenGebaeude + AusgabenForschung + AusgabenZinsen + AusgabenProduktion + AusgabenMarkt + AusgabenVertraege + AusgabenMafia) AS AusgabenGesamt,
        SUM(EinnahmenGebaeude + EinnahmenVerkauf + EinnahmenZinsen + EinnahmenMarkt + EinnahmenVertraege + EinnahmenMafia) AS EinnahmenGesamt,
        SUM(AusgabenForschung) AS AusgabenForschung,
        SUM(AusgabenGebaeude) AS AusgabenGebaeude
    FROM " . self::TABLE_STATISTICS . "
), glob_mitglieder as (
    SELECT
        SUM(" . getAllResearchFields('+') . ") AS GesamtForschung,
        SUM(" . getAllBuildingFields('+') . ") AS GesamtGebaeude,
        COUNT(1) AS AnzahlSpieler,
        SUM(IGMGesendet) AS AnzahlIGMs
    FROM " . self::TABLE_USERS . "
    WHERE ID > 0
)
SELECT
    s.*,
    m.*,
    (SELECT COUNT(1) FROM " . self::TABLE_GROUP . ")  AS AnzahlGruppen,
    (SELECT COUNT(1) FROM " . self::TABLE_USERS . " WHERE Gruppe IS NOT NULL) AS AnzahlSpielerInGruppe
FROM
    glob_stats s, glob_mitglieder m");
        // @formatter:on
        $result = $this->executeAndExtractFirstRow($stmt);
        if ($result !== null) {
            $stmt = $this->prepare("SHOW TABLE STATUS FROM `" . Config::get(Config::SECTION_DATABASE, 'database') . "` WHERE `name` = :table");
            $table = self::TABLE_JOBS;
            $stmt->bindParam("table", $table);
            $result['AnzahlAuftraege'] = $this->executeAndExtractField($stmt, 'Auto_increment');
        }
        return $result;
    }

    public function getAllContractsByAnEquals(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.ID AS VonID, m.Name AS VonName
            FROM " . self::TABLE_CONTRACTS . " v INNER JOIN " . self::TABLE_USERS . " m ON v.Von = m.ID
            WHERE An = :id ORDER BY Wann");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllContractsByVonEquals(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.ID AS AnID, m.Name AS AnName
            FROM " . self::TABLE_CONTRACTS . " v INNER JOIN " . self::TABLE_USERS . " m ON v.An = m.ID
            WHERE Von = :id ORDER BY Wann");
        $stmt->bindParam("id", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getContractByIdAndAn(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT v.*, m.Name AS AnName
            FROM " . self::TABLE_CONTRACTS . " v LEFT OUTER JOIN " . self::TABLE_USERS . " m ON v.An = m.ID
            WHERE v.ID = :id AND v.An = :user");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getContractByIDAndAnOrVonEquals(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_CONTRACTS . " WHERE ID = :id AND (An = :user OR Von = :user)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getMessageByIdAndAnOrVonEquals(int $id, int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT n.*, mVon.ID AS VonID, coalesce(mVon.Name, 'Gelöscht') AS VonName, mAn.ID AS AnID, coalesce(mAn.Name, 'Gelöscht') AS AnName
            FROM (" . self::TABLE_MESSAGES . " n LEFT OUTER JOIN " . self::TABLE_USERS . " mVon ON n.Von = mVon.ID) LEFT OUTER JOIN " . self::TABLE_USERS . " mAn ON n.An = mAn.ID
            WHERE n.ID = :id AND (n.An = :user OR n.Von = :user)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->bindParam("user", $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllPlayerIdAndBankAndBioladenAndDoenerstandAndBank(): ?array
    {
        return $this->executeAndExtractRows($this->prepare("SELECT ID, Bank, Gebaeude" . building_shop . ", Gebaeude" . building_kebab_stand . ", Gebaeude" . building_bank . " FROM " . self::TABLE_USERS . " WHERE ID > 0 AND EmailAct IS NULL"));
    }

    public function getAllPlayerIdAndResearchLevels(): ?array
    {
        return $this->executeAndExtractRows($this->prepare("SELECT ID, " . getAllResearchFields() . " FROM " . self::TABLE_USERS . " WHERE ID > 0 AND EmailAct IS NULL"));
    }

    public function getPlayerCardByID(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT m.ID, m.Name, coalesce(m.Beschreibung, '[i]Keine[/i]') AS Beschreibung, 
                m.RegistriertAm, m.Punkte, m.Verwarnungen, m.Gesperrt, m.LastLogin, m.IgmGesendet, m.IgmEmpfangen,
                g.ID AS GruppeID, coalesce(g.Name, 'Keine') AS GruppeName, m.LastImageChange
            FROM " . self::TABLE_USERS . " m LEFT OUTER JOIN " . self::TABLE_GROUP . " g ON m.Gruppe = g.ID
            WHERE m.ID = :id
            AND m.ID > 0");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getPlayerEspionageDataByID(int $blm_user): ?array
    {
        $stmt = $this->prepare("SELECT ID, Name, Geld, " . getAllBuildingFields() . ", " . getAllStockFields() . "
            FROM " . self::TABLE_USERS . " WHERE ID = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupDiplomacyTypeById(int $left, int $right): ?int
    {
        $stmt = $this->prepare("SELECT `Typ` FROM " . self::TABLE_GROUP_DIPLOMACY . " WHERE (Von = :left OR Von = :right) AND (An = :left OR An = :right) AND Aktiv = 1");
        $stmt->bindParam('left', $left, PDO::PARAM_INT);
        $stmt->bindParam('right', $right, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Typ');
    }

    public function getGroupDiplomacyById(int $id): ?array
    {
        $stmt = $this->prepare("SELECT d.*, gAn.ID AS GruppeAnId, gAn.Name AS GruppeAnName, gVon.ID AS GruppeVonId, gVon.Name AS GruppeVonName
            FROM (" . self::TABLE_GROUP_DIPLOMACY . " d LEFT OUTER JOIN " . self::TABLE_GROUP . " gAn ON d.An = gAn.ID) LEFT OUTER JOIN " . self::TABLE_GROUP . " gVon ON gVon.ID = d.Von
            WHERE d.ID = :id");
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getGroupDiplomacyByIdAndAn(int $id, int $an): ?array
    {
        $stmt = $this->prepare("SELECT d.*, gAn.ID AS GruppeAnId, gAn.Name AS GruppeAnName, gVon.ID AS GruppeVonId, gVon.Name AS GruppeVonName
            FROM (" . self::TABLE_GROUP_DIPLOMACY . " d LEFT OUTER JOIN " . self::TABLE_GROUP . " gAn ON d.An = gAn.ID) LEFT OUTER JOIN " . self::TABLE_GROUP . " gVon ON gVon.ID = d.Von
            WHERE d.ID = :id AND d.An = :an");
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->bindParam('an', $an, PDO::PARAM_INT);
        return $this->executeAndExtractFirstRow($stmt);
    }

    public function getAllPlayerIdAndNameWhereMafiaPossible(float $myPoints, int $myId, ?int $myGroup, float $pointsRange, int $pointsCutoff): ?array
    {
        if ($pointsRange <= 1.0) {
            $pointsRange = 1000;
        }
        if ($myPoints >= $pointsCutoff) {
            $lowPoints = min($pointsCutoff, $myPoints / $pointsRange);
            $highPoints = $pointsCutoff * 1000;
        } else {
            $lowPoints = max(Config::getFloat(Config::SECTION_MAFIA, 'min_points'), $myPoints / $pointsRange);
            $highPoints = $myPoints * $pointsRange;
        }
        $stmt = $this->prepare("SELECT ID, Name, Gruppe, Punkte
FROM " . self::TABLE_USERS . " m
WHERE (
            coalesce(m.Gruppe, -2) != coalesce(:myGroup, -1) -- exclude my own group
        AND m.ID != :myId -- exclude myself (if user isn't in a group)
        AND (m.Punkte >= :lowPoints AND m.Punkte <= :highPoints -- include by points range
        AND m.Gruppe NOT IN -- exclude NAP and BND
            (SELECT d.Von
             FROM " . self::TABLE_GROUP_DIPLOMACY . " d
             WHERE d.Von = m.Gruppe
               AND d.An = coalesce(:myGroup, -1)
               AND d.Aktiv = 1
               AND d.typ != " . group_diplomacy_war . "
             UNION
             SELECT d.An
             FROM " . self::TABLE_GROUP_DIPLOMACY . " d
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
         FROM " . self::TABLE_GROUP_DIPLOMACY . " d
         WHERE d.Von = m.Gruppe
           AND d.An = coalesce(:myGroup, -1)
           AND d.Aktiv = 1
           AND d.typ = " . group_diplomacy_war . "
         UNION
         SELECT d.An
         FROM " . self::TABLE_GROUP_DIPLOMACY . " d
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
        $stmt = $this->prepare("UPDATE " . self::TABLE_USERS . " SET OnlineZeitSinceLastCron = OnlineZeitSinceLastCron + TIMESTAMPDIFF(SECOND, LastAction, NOW()), LastAction = NOW() WHERE ID = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function updatePlayerOnlineTimes(): ?int
    {
        return $this->executeAndGetAffectedRows($this->prepare("UPDATE " . self::TABLE_USERS . " SET OnlineZeit = OnlineZeit + OnlineZeitSinceLastCron, OnlineZeitSinceLastCron = 0"));
    }

    public function updatePlayerPoints(): ?int
    {
        $stmt = $this->prepare("UPDATE " . self::TABLE_STATISTICS . " s SET
            s.GebaeudePlus = floor(s.AusgabenGebaeude / " . Config::getInt(Config::SECTION_BASE, 'expense_points_factor') . "),
            s.ForschungPlus = floor(s.AusgabenForschung / " . Config::getInt(Config::SECTION_BASE, 'expense_points_factor') . "),
            s.MafiaPlus = floor(s.AusgabenMafia / " . Config::getInt(Config::SECTION_BASE, 'expense_points_factor') . ")
            WHERE s.user_id > 0");
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return null;
        }
        $stmt = $this->prepare("UPDATE " . self::TABLE_USERS . " m INNER JOIN " . self::TABLE_STATISTICS . " s ON m.ID = s.user_id SET
            m.Punkte = s.GebaeudePlus + s.ForschungPlus + s.MafiaPlus
            WHERE m.ID > 0");
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return null;
        }
        $stmt = $this->prepare("UPDATE " . self::TABLE_RUNTIME_CONFIG . " SET
            conf_value = UNIX_TIMESTAMP()
            WHERE conf_name = 'lastpoints'");
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function gdprCleanLoginLog(): ?int
    {
        $stmt = $this->prepare("UPDATE " . self::TABLE_LOG_LOGIN . "
            SET ip = concat('ANON_', CRC32(SHA1(SHA1(ip)))), anonymized = true
            WHERE anonymized = false AND created < date_sub(now(), interval 30 day)");
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function updateLastCron(): ?int
    {
        return $this->executeAndGetAffectedRows($this->prepare("REPLACE INTO " . self::TABLE_RUNTIME_CONFIG . " SET conf_name = 'lastcron', conf_value = UNIX_TIMESTAMP()"));
    }

    public function countPendingGroupDiplomacy(int $group_id): ?int
    {
        $stmt = $this->prepare("SELECT count(1) AS Count FROM " . self::TABLE_GROUP_DIPLOMACY . " WHERE An = :id AND Aktiv = 0");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Count');
    }

    public function deleteAllMessagesForUser(int $blm_user): ?int
    {
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_MESSAGES . " WHERE An = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function getSitterPermissions(int $blm_user, string $right): ?string
    {
        $stmt = $this->prepare("SELECT * FROM " . self::TABLE_SITTER . " WHERE user_id = :id");
        $stmt->bindParam('id', $blm_user, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, $right);
    }

    public function deleteGroup(int $group_id): ?string
    {
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP_DIPLOMACY . " WHERE Von = :id OR An = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_diplomatie';
        }
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP_LOG . " WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_logbuch';
        }
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP_MESSAGES . " WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_nachrichten';
        }
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP_RIGHTS . " WHERE group_id = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_rechte';
        }
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP_CASH . " WHERE group_id = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe_kasse';
        }
        $stmt = $this->prepare("DELETE FROM " . self::TABLE_GROUP . " WHERE ID = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'gruppe';
        }
        $stmt = $this->prepare("UPDATE " . self::TABLE_USERS . " SET Gruppe = NULL WHERE Gruppe = :id");
        $stmt->bindParam('id', $group_id, PDO::PARAM_INT);
        if ($this->executeAndGetAffectedRows($stmt) === null) {
            return 'mitglieder';
        }
        return null;
    }

    public function createUser(string $name, $email, ?string $email_activation_code, string $password): ?int
    {
        $defaults = Config::getSection(Config::SECTION_STARTING_VALUES);
        $defaults['Name'] = $name;
        $defaults['EMail'] = $email;
        $defaults['EMailAct'] = $email_activation_code;
        $defaults['Passwort'] = hashPassword($password);
        if (Database::getInstance()->createTableEntry(Database::TABLE_USERS, $defaults) === null) {
            return null;
        }
        $id = Database::getInstance()->lastInsertId();
        if (Database::getInstance()->createTableEntry(Database::TABLE_STATISTICS, array('user_id' => $id)) === null) {
            return null;
        }
        return $id;
    }

    public function getInstallScriptChecksum(string $script): ?string
    {
        $stmt = $this->prepare("SELECT Checksum FROM " . self::TABLE_UPDATE_INFO . " WHERE Script = :script");
        $stmt->bindParam('script', $script);
        return $this->executeAndExtractField($stmt, 'Checksum');
    }

    public function tableExists(string $table): bool
    {
        $db = Config::get(Config::SECTION_DATABASE, 'database');
        $stmt = $this->prepare("SELECT count(1) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table");
        $stmt->bindParam('schema', $db);
        $stmt->bindParam('table', $table);
        return $this->executeAndExtractField($stmt, 'count') > 0;
    }

    public function executeFile(string $script): ?string
    {
        $commands = explode(';', file_get_contents($script));
        for ($i = 0; $i < count($commands); $i++) {
            $sql = $commands[$i];
            if (trim($sql) == '') continue;
            $stmt = $this->prepare($sql);
            if (!$stmt->execute()) {
                return $sql;
            }
        }
        return null;
    }

    public function selectForExport($table, $whereOr, $userId): ?array
    {
        $conditions = array();
        $fields = array();
        $i = 0;
        if (is_array($whereOr)) {
            foreach ($whereOr as $field) {
                $conditions[] = sprintf('%s = :whr%d', $field, ++$i);
                $fields['whr' . $i] = $userId;
            }
        } else {
            $conditions[] = sprintf('%s = :whr0', $whereOr);
            $fields['whr0'] = $userId;
        }
        $sql = sprintf("SELECT * FROM %s WHERE %s", $table, implode(" OR ", $conditions));
        return $this->executeAndExtractRows($this->prepare($sql), $fields);
    }

    public function getQueryCount(): int
    {
        return $this->queries;
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
        error_log(sprintf("%s:%d | %s", basename($bt[$i]["file"]), $bt[$i]["line"], $text));
    }

    private function executeAndExtractField(PDOStatement $stmt, string $fieldName, array $executeParam = array()): ?string
    {
        if (!($this->execute($stmt, $executeParam))) {
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
        if (!($this->execute($stmt, $executeParam))) {
            $this->error($stmt, "Could not execute statement");
            return null;
        }
        return $stmt->rowCount();
    }

    private function executeAndExtractRows(PDOStatement $stmt, array $executeParam = array()): ?array
    {
        if (!($this->execute($stmt, $executeParam))) {
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

    private function prepare(string $sql): ?PDOStatement
    {
        $this->queries++;
        $this->sql = $sql;
        $pre = microtime(true);
        $stmt = $this->link->prepare($sql);
        $post = microtime(true);
        if ($post - $pre > $this->slow_query_threshold) {
            $this->warnings[] = sprintf("Statement took %.02fms to prepare: %s", ($post - $pre) * 1000, $this->sql);
        }
        if ($stmt === false) {
            $this->error($this->link, "Could not prepare statement: " . $sql);
            return null;
        }
        return $stmt;
    }

    private function prepareAndBind(string $template, array $wheres = array()): ?PDOStatement
    {
        $wheres = array_filter($wheres, fn($value) => $value !== null);
        if (empty($wheres)) {
            return $this->prepare(str_replace("((1))", "1=1", $template));
        }

        $sql = str_replace("((1))", implode(" AND ", array_keys($wheres)), $template);
        $stmt = $this->prepare($sql);
        if ($stmt === null) return null;

        foreach ($wheres as $key => &$value) {
            $bindField = substr($key, strrpos($key, ':') + 1);
            $stmt->bindParam($bindField, $value);
        }

        return $stmt;
    }

    private function execute(PDOStatement $stmt, array $executeParam = array()): bool
    {
        $pre = microtime(true);
        if (count($executeParam) == 0) {
            $executeResult = $stmt->execute();
        } else {
            $executeResult = $stmt->execute($executeParam);
        }
        $post = microtime(true);
        if ($post - $pre > $this->slow_query_threshold) {
            $this->warnings[] = sprintf("Statement took %.02fms to execute: %s", ($post - $pre) * 1000, $this->sql);
        }
        return $executeResult;
    }
}
