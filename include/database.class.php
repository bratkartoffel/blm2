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

    public function __construct()
    {
        $this->link = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_DATENBANK . ";charset=utf8", DB_BENUTZER, DB_PASSWORT,
            array(PDO::ATTR_PERSISTENT => true));
    }

    function __destruct()
    {
        if ($this->link->inTransaction()) {
            $this->link->rollBack();
        }
        $this->queries = 0;
    }

    public function beginTransaction()
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

    public function getAdminBankLogCount($nameFilter)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM log_bank_view WHERE Wer LIKE :name");
        $stmt->bindParam("name", $nameFilter);
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getAdminBankLogEntries($nameFilter, $page, $entriesPerPage)
    {
        $offset = $page * $entriesPerPage;
        $stmt = $this->prepare("SELECT Wer, WerId, UNIX_TIMESTAMP(Wann) AS WannTs, Wieviel, Aktion FROM log_bank_view
            WHERE Wer LIKE :name ORDER BY Wann DESC LIMIT :offset, :count");
        $stmt->bindParam("name", $nameFilter);
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

    public function getMarktplatzCount($wasFilter = array())
    {
        if (sizeof($wasFilter) == 0) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz WHERE Was IN (" . str_repeat('?, ', count($wasFilter) - 1) . "?)");
        }
        return $this->executeAndExtractField($stmt, 'count', $wasFilter);
    }

    public function getGroupCount()
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe");
        return $this->executeAndExtractField($stmt, 'count');
    }

    public function getGroupNameById($id)
    {
        $stmt = $this->prepare("SELECT Name FROM gruppe WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        return $this->executeAndExtractField($stmt, 'Name');
    }

    public function deletePlayerById($id)
    {
        $stmt = $this->prepare("DELETE FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, "Could not execute statement");
            return 0;
        }
        return $stmt->rowCount() == 1;
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

    private function executeAndExtractRows($stmt, $executeParam = array())
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
        $results = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }
}
