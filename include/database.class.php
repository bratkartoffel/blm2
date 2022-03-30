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
        if ($stmt === false) {
            $this->error($this->link, "prepare", "Could not prepare statement");
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
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return $result['Name'];
    }

    public function getPlayerNameById($id)
    {
        $stmt = $this->prepare("SELECT Name FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return $result['Name'];
    }

    public function getPlayerRankById($id)
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Punkte > (SELECT Punkte FROM mitglieder WHERE ID = :id)");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return $result['count'] + 1;
    }

    public function getPlayerPointsById($id)
    {
        $stmt = $this->prepare("SELECT Punkte FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return round($result['Punkte']);
    }

    public function getPlayerCount($nameFilter = "%")
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM mitglieder WHERE Name LIKE :name");
        $stmt->bindParam("name", $nameFilter);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return $result['count'];
    }

    public function getMarktplatzCount($wasFilter = array())
    {
        if (sizeof($wasFilter) == 0) {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz");
        } else {
            $stmt = $this->prepare("SELECT count(1) AS count FROM marktplatz WHERE Was IN (" . str_repeat('?, ', count($wasFilter) - 1) . "?)");
        }
        if (!$stmt->execute($wasFilter)) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return 0;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return 0;
        }
        return $result['count'];
    }

    public function getGroupCount()
    {
        $stmt = $this->prepare("SELECT count(1) AS count FROM gruppe");
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return 0;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return 0;
        }
        return $result['count'];
    }

    public function getGroupNameById($id)
    {
        $stmt = $this->prepare("SELECT Name FROM gruppe WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->error($stmt, __FUNCTION__, "No result found");
            return null;
        }
        return $result['Name'];
    }

    public function deletePlayerById($id)
    {
        $stmt = $this->prepare("DELETE FROM mitglieder WHERE ID = :id");
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $this->error($stmt, __FUNCTION__, "Could not execute statement");
            return 0;
        }
        return $stmt->rowCount() == 1;
    }

    public function getQueryCount()
    {
        return $this->queries;
    }

    private function error($handle, $method, $text, $level = E_USER_WARNING)
    {
        $errorInfo = $handle->errorInfo();
        if (sizeof($errorInfo) > 0 && $errorInfo[0] != '00000') {
            $text .= " (" . var_export($errorInfo, true) . ")";
        }
        trigger_error($method . ": " . $text, $level);
    }
}
