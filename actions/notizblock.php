<?php
/**
 * Führt die Aktionen des Benutzers auf seinem Notizblock aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wer nicht angemeldet ist, hat auch keinen Notzblock ;)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if ($_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=nachrichten_liste&m=112&" . time());
    die();
}

$notizblock = trim($_POST['notizblock']);

if ($notizblock != "") {        // Will der Benutzer überhaupt was drinnen stehen haben?
    $notizblock = "'" . mysql_real_escape_string($notizblock) . "'";
} else {
    $notizblock = "NULL";        // Nö: Also wird der Notizblock auf NULL gesetzt :)
}

$sql_abfrage = "UPDATE
    mitglieder
SET
    Notizblock=" . $notizblock . "
WHERE
    ID='" . intval($_SESSION['blm_user']) . "';";
mysql_query($sql_abfrage);        // Den neuen Notizblock speichern
$_SESSION['blm_queries']++;


// Erledigt :)
DisconnectDB();
header("location: ../?p=notizblock&m=213&" . intval(time()));
die();
