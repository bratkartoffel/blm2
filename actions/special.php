<?php
/**
 * Führt eie Aktion zu einem Event aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/database.class.php");

if (!IstAngemeldet()) {        // Wer nicht angemeldet ist, kann auch nichts abbrechen...
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if ($_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=index&m=138");
    die();
}

$hash = $_GET['hash'];

if (!preg_match('/^[a-f0-9]{40}$/i', $hash)) {
    header("location: ../?p=index&m=112");
    die();
}

$sql_abfrage = "SELECT
    Abgeholt
FROM
    special
WHERE
    Hash='" . $hash . "'
AND
    Wer='" . $_SESSION['blm_user'] . "'
;";
$sql_ergebnis = mysql_query($sql_abfrage);

$temp = mysql_fetch_object($sql_ergebnis);

if ($temp->Abgeholt != '0') {
    header("location: ../?p=index&m=112");
    die();
}

srand(time() + microtime(true));

$ware = rand(1, ANZAHL_WAREN);
$menge = rand(500, 5000);

$sql_abfrage = "UPDATE
    lagerhaus
SET
    Lager" . $ware . "=Lager" . $ware . "+" . $menge . "
WHERE
    ID='" . $_SESSION['blm_user'] . "'
;";
mysql_query($sql_abfrage);

header("location: ../?p=special&hash=" . $hash . "&ware=" . $ware . "&menge=" . $menge);
