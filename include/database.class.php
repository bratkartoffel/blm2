<?php
require_once(dirname(__FILE__) . '/config.inc.php');

class Database
{
    private static $INSTANCE = null;

    public static function getInstance()
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new Database();
        }
        return self::$INSTANCE;
    }

    private $link;
    private $queries;

    function __construct()
    {
        try {
            $this->link = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_DATENBANK . ";charset=utf8", DB_BENUTZER, DB_PASSWORT,
                array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $Exception) {
            die('Database connection failed!');
        }
    }

    function __destruct()
    {
        if ($this->link->inTransaction()) {
            $this->link->rollBack();
        }
        $this->queries = 0;
    }

    public function begin()
    {
        return $this->link->beginTransaction();
    }

    public function commit()
    {
        return $this->link->commit();
    }

    public function rollBack()
    {
        return $this->link->rollBack();
    }

    public function prepare($sql)
    {
        $this->queries++;
        $stmt = $this->link->prepare($sql);
        if ($stmt == false) {
            $this->error($this->link, "Could not prepare statement: " . $sql);
            return null;
        }
        return $stmt;
    }

    public function createTableEntry($table, $values = array())
    {
        $columnNames = array_keys($values);
        $columnParameters = array();
        foreach ($values as $field => $value) {
            $columnParameters[] = ':' . $field;
        }

        $stmt = $this->prepare("INSERT INTO " . $table . " (" . implode(", ", $columnNames) . ") VALUES (" . implode(", ", $columnParameters) . ")");
        return $this->executeAndGetAffectedRows($stmt, $values);
    }

    public function updateTableEntry($table, $id, $changes = array())
    {
        $fields = array();
        foreach ($changes as $field => $value) {
            $fields[] = sprintf("%s = :%s", $field, $field);
        }
        /** @noinspection SqlResolve */
        $stmt = $this->prepare("UPDATE " . $table . " SET " . implode(", ", $fields) . " WHERE ID = :id");
        $changes['id'] = $id;
        return $this->executeAndGetAffectedRows($stmt, $changes);
    }

    public function deleteTableEntry($table, $id)
    {
        /** @noinspection SqlResolve */
        $stmt = $this->prepare("DELETE FROM " . $table . " WHERE ID = :id");
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        return $this->executeAndGetAffectedRows($stmt);
    }

    public function getPlayerNameByRank($rank)
    {
        // rank is 1-based, but query parameter is 0-based
        $rank--;
        $stmt = $this->prepare("SELECT Name FROM mitglieder ORDER BY Punkte DESC, Name LIMIT :rank, 1");
        $stmt->bindParam("rank", $rank, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function getPlayerNameById($id)
    {
        $stmt = $this->prepare("SELECT Name FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function getPlayerIdByNameAndActivationToken($name, $code)
    {
        $stmt = $this->prepare("SELECT ID FROM mitglieder WHERE Name = :name AND EMailAct = :code");
        $stmt->bindParam("name", $name);
        $stmt->bindParam("code", $code);
        return $this->executeAndExtractField($stmt, 'ID');
    }

    public function getPlayerRankById($id)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Punkte > (SELECT Punkte FROM mitglieder WHERE ID = :id)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'count') + 1;
    }

    public function getPlayerPointsById($id)
    {
        $stmt = $this->prepare("SELECT Punkte FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return round($this->executeAndExtractField($stmt, 'Punkte'));
    }

    public function getPlayerCount($nameFilter = "%")
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Name LIKE :name");
        $stmt->bindParam("name", $nameFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBankLogCount($werFilter)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_bank_view WHERE Wer LIKE :wer");
        $stmt->bindParam("wer", $werFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBankLogEntries($werFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT Wer, WerId, UNIX_TIMESTAMP(Wann) AS WannTs, Wieviel, Aktion FROM log_bank_view
            WHERE Wer LIKE :wer ORDER BY Wann DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminBioladenLogCount($werFilter)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_bioladen_view WHERE Wer LIKE :wer");
        $stmt->bindParam("wer", $werFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBioladenLogEntries($werFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT Wer, WerId, UNIX_TIMESTAMP(Wann) AS WannTs, Was, Wieviel, Einzelpreis, Gesamtpreis FROM log_bioladen_view
            WHERE Wer LIKE :wer ORDER BY Wann DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminGroupTreasuryLogCount($werFilter, $wenFilter, $groupFilter)
    {
        if ($groupFilter == null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_gruppenkasse_view WHERE Wer LIKE :wer AND Wen LIKE :wen");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_gruppenkasse_view WHERE Wer LIKE :wer AND Wen LIKE :wen AND GruppeId = :gruppe");
            $stmt->bindParam("gruppe", $groupFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminGroupTreasuryLogEntries($werFilter, $wenFilter, $groupFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        if ($groupFilter == null) {
            $stmt = $this->prepare("SELECT Wer, WerId, Wen, WenId, Gruppe, GruppeId, UNIX_TIMESTAMP(Wann) AS WannTs, Wieviel, Wohin FROM log_gruppenkasse_view
            WHERE Wer LIKE :wer AND Wen LIKE :wen ORDER BY Wann DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT Wer, WerId, Wen, WenId, Gruppe, GruppeId, UNIX_TIMESTAMP(Wann) AS WannTs, Wieviel, Wohin FROM log_gruppenkasse_view
            WHERE Wer LIKE :wer AND Wen LIKE :wen AND GruppeId = :gruppe ORDER BY Wann DESC LIMIT :offset, :count");
            $stmt->bindParam("gruppe", $groupFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminLoginLogCount($werFilter, $ipFilter, $artFilter)
    {
        if ($artFilter == null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_login_view WHERE Wer LIKE :wer AND IP LIKE :ip");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_login_view WHERE Wer LIKE :wer AND IP LIKE :ip AND ArtId = :art");
            $stmt->bindParam("art", $artFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("ip", $ipFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminLoginLogEntries($werFilter, $ipFilter, $artFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        if ($artFilter == null) {
            $stmt = $this->prepare("SELECT Wer, WerId, IP, UNIX_TIMESTAMP(Wann) AS WannTs, Art FROM log_login_view
            WHERE Wer LIKE :wer AND IP LIKE :ip ORDER BY Wann DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT Wer, WerId, IP, UNIX_TIMESTAMP(Wann) AS WannTs, Art FROM log_login_view
            WHERE Wer LIKE :wer AND IP LIKE :ip AND ArtId = :art ORDER BY Wann DESC LIMIT :offset, :count");
            $stmt->bindParam("art", $artFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("ip", $ipFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminMafiaLogCount($werFilter, $wenFilter)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_mafia_view WHERE Wer LIKE :wer AND Wen LIKE :wen");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminMafiaLogEntries($werFilter, $wenFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT Wer, WerId, Wen, WenId, UNIX_TIMESTAMP(Wann) AS WannTs, Art, Wieviel, Erfolgreich FROM log_mafia_view
           WHERE Wer LIKE :wer AND Wen LIKE :wen ORDER BY Wann DESC LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getAdminVertraegeLogCount($werFilter, $wenFilter, $angenommenFilter)
    {
        if ($angenommenFilter == null) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_vertraege_view WHERE Wer LIKE :wer AND Wen LIKE :wen");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM log_vertraege_view WHERE Wer LIKE :wer AND Wen AND Angenommen = :angenommen LIKE :wen");
            $stmt->bindParam("angenommen", $angenommenFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminVertraegeLogEntries($werFilter, $wenFilter, $angenommenFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        if ($angenommenFilter == null) {
            $stmt = $this->prepare("SELECT Wer, WerId, Wen, WenId, UNIX_TIMESTAMP(Wann) AS WannTs, Ware, Wieviel, Einzelpreis, Gesamtpreis, Angenommen FROM log_vertraege_view
           WHERE Wer LIKE :wer AND Wen LIKE :wen ORDER BY Wann DESC LIMIT :offset, :count");
        } else {
            $stmt = $this->prepare("SELECT Wer, WerId, Wen, WenId, UNIX_TIMESTAMP(Wann) AS WannTs, Ware, Wieviel, Einzelpreis, Gesamtpreis, Angenommen FROM log_vertraege_view
           WHERE Wer LIKE :wer AND Wen LIKE :wen AND Angenommen = :angenommen ORDER BY Wann DESC LIMIT :offset, :count");
            $stmt->bindParam("angenommen", $angenommenFilter, PDO::PARAM_INT);
        }
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getMarktplatzEntryById($id)
    {
        $stmt = $this->prepare("SELECT ID, Von AS VonId, Menge, Was, Preis FROM marktplatz WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getVertragEntryById($id)
    {
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name AS Von, An AS AnId, m2.Name AS AnName, Menge, Was, Preis
            FROM (vertraege v JOIN mitglieder m1 on m1.ID = v.Von) JOIN mitglieder m2 on m2.ID = v.An WHERE v.ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getMarktplatzCount($warenFilter = array())
    {
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz WHERE Was IN (" . str_repeat('?, ', count($warenFilter) - 1) . "?)");
        }
        return $this->executeAndExtractField($stmt, 'count', $warenFilter);
    }

    public function getMarktplatzEntries($warenFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        if (sizeof($warenFilter) == 0) {
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM marktplatz m1 JOIN mitglieder m2 on m2.ID = m1.Von LIMIT :offset, :count");
        } else {
            $fields = array();
            for ($i = 0; $i < count($warenFilter); $i++) {
                $fields[] = sprintf(':ware_%d', $i);
            }
            $stmt = $this->prepare("SELECT m1.ID, m1.Von AS VonId, m2.Name AS VonName, m1.Was, m1.Menge, m1.Preis, m1.Menge * m1.Preis AS Gesamtpreis 
                FROM marktplatz m1 JOIN mitglieder m2 on m2.ID = m1.Von WHERE m1.Was IN (" . implode(', ', $fields) . ") LIMIT :offset, :count");
            for ($i = 0; $i < count($warenFilter); $i++) {
                $stmt->bindParam($fields[$i], $warenFilter[$i], PDO::PARAM_INT);
            }

        }
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getVertragCount($werFilter, $wenFilter)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM vertraege WHERE Von LIKE :wer AND AN LIKE :wen");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getVertragEntries($werFilter, $wenFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT v.ID, Von AS VonId, m1.Name as VonName, An AS AnId, m2.Name AS AnName, Was, Menge, Preis, Menge * Preis AS Gesamtpreis 
            FROM (vertraege v JOIN mitglieder m1 on m1.ID = v.Von) JOIN mitglieder m2 ON m2.Id = v.An WHERE Von LIKE :wer AND AN LIKE :wen LIMIT :offset, :count");
        $stmt->bindParam("wer", $werFilter);
        $stmt->bindParam("wen", $wenFilter);
        $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam("count", $entriesPerPage, PDO::PARAM_INT);
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupCount()
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAllGroupIdsAndName()
    {
        $stmt = $this->prepare("SELECT ID, Name FROM gruppe");
        return $this->executeAndExtractRows($stmt);
    }

    public function getAllPlayerIdsAndName()
    {
        $stmt = $this->prepare("SELECT ID, Name FROM mitglieder");
        return $this->executeAndExtractRows($stmt);
    }

    public function getGroupNameById($id)
    {
        $stmt = $this->prepare("SELECT Name FROM gruppe WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function getQueryCount()
    {
        return $this->queries;
    }

    private function error($handle, $text, $level = E_USER_WARNING)
    {
        $errorInfo = $handle->errorInfo();
        if (sizeof($errorInfo) > 0 && $errorInfo[0] != '00000') {
            $text .= " (" . var_export($errorInfo, true) . ")";
        }
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        for ($i = 1; $i < count($bt); $i++) {
            if ($bt[$i]["file"] != __FILE__) {
                break;
            }
        }
        trigger_error($bt[$i]["function"] . ": " . $text, $level);
    }

    private function executeAndExtractField($stmt, $fieldName, $executeParam = array())
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
            $this->error($stmt, "No result found");
            return null;
        }
        return $result[$fieldName];
    }

    private function executeAndGetAffectedRows($stmt, $executeParam = array())
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

    private function executeAndExtractRows($stmt)
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
}
