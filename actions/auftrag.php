<?php
/**
 * Allgemeine Auftragsverwaltung (Abbrechen der Aufräge)
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wer nicht angemeldet ist, kann auch nichts abbrechen...
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if ($_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=index&m=112&" . time());
    die();
}

$sql_abfrage = "SELECT
    *
FROM
    auftrag
WHERE
    ID='" . intval($_GET['id']) . "'
AND
    Von='" . $_SESSION['blm_user'] . "';";
$sql_ergebnis = mysql_query($sql_abfrage);        // Zuerst mal alle Daten
$_SESSION['blm_queries']++;

$auftrag = mysql_fetch_object($sql_ergebnis);        // des Auftrags abrufen

if (!$auftrag->ID) {        // Der Auftrag konnte nicht gefunden werden
    DisconnectDB();
    header("location: ../?p=" . $_GET['back'] . "&m=112&" . time());
    die();
}

if (intval($auftrag->Menge) > 0) {        // Der Auftrag ist ein Produktionsauftrag
    $ProzentFertig = 1 - (($auftrag->Start + $auftrag->Dauer) - time()) / $auftrag->Dauer;        // Zu wieviel % ist der Auftrag schon abgeschlossen?

    $sql_abfrage = "UPDATE
    lagerhaus
SET
    Lager" . ($auftrag->Was - 200) . "=Lager" . ($auftrag->Was - 200) . "+" . intval($auftrag->Menge * $ProzentFertig) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";        // Wir müssen die Teilmenge ins Lager einbuchen
} else {
    $sql_abfrage = "UPDATE
    mitglieder
SET
    Geld=Geld+" . ($auftrag->Kosten * AUFTRAG_RUECKZIEH_RETURN) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";        // Wir müssen einen Teil der Kosten zurückbuchen
}
mysql_query($sql_abfrage);        // Führt die oben vorbereitete Query aus

$_SESSION['blm_queries']++;

$sql_abfrage = "DELETE FROM
    auftrag
WHERE
    ID='" . intval($_GET['id']) . "'
AND
    Von='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);        // Dann löschen wir den Auftrag
$_SESSION['blm_queries']++;

$affected = mysql_affected_rows();
DisconnectDB();
if ($affected == 0) {        // Wenn wir keinen Auftrag löschen konnten...
    // dann stimmt was nicht
    header("location: ../?p=" . $_GET['back'] . "&m=112&" . time());
} else {
    header("location: ../?p=" . $_GET['back'] . "&m=222&" . time() . "#" . substr($_GET['back'], 0, 1) . intval($_GET['was']));
}
