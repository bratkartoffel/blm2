<?php
/**
 * Führt die Aktionen des Benutzers mit dem Forschungszentrum aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wenn der Client nicht angemeldet ist, darf er auch nichts mit dem Forschungszentrum machen :)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();    // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Forschung && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=forschungszentrum&m=112");
    die();
}

include("../include/kosten_dauer.inc.php");        // Dann die Forschungskosten und -dauern abrufen...

if ($_POST['was'] <= 0 || $_POST['was'] > ANZAHL_WAREN) {    // der user will was forschen, was es nicht gibt...
    DisconnectDB();
    header("location: ../?p=forschungszentrum&m=112");
    die();
}

$temp = "Forschung" . $_POST['was'];            // Temporäre Variable mit dem MySQL-Spaltennamen der Forschung
$forschungs_kosten = $$temp->Kosten;            // Die Forschungskosten des Auftrags
$forschungs_dauer = $$temp->Dauer;                // Die Dauer des Auftrags
$forschungs_punkte = $$temp->Punkte;            // Die Dauer des Auftrags

if ($ich->Gebaeude2 < intval($_POST['was'] * 1.5) || $ich->Gebaeude1 < intval($_POST['was'] * 1.5)) {        // Darf der Benutzer das Gemüse überhaupt forschen?
    DisconnectDB();
    header("location: ../?p=forschungszentrum&m=112");
    die();
}

if ($ich->Geld < $forschungs_kosten) {        // Hat der Benutzer überhaupt genug Geld für so was? Wenn nicht, dann abbruch
    DisconnectDB();
    header("location: ../?p=forschungszentrum&m=112");
    die();
}

$sql_abfrage = "INSERT INTO
    auftrag
(
    ID,
    Was,
    Von,
    Kosten,
    Dauer,
    Start,
    Menge,
    Punkte
)
VALUES
(
    NULL,
    '" . (300 + intval($_POST['was'])) . "',
    '" . $_SESSION['blm_user'] . "',
    '" . $forschungs_kosten . "',
    '" . $forschungs_dauer . "',
    '" . time() . "',
    NULL,
    '" . $forschungs_punkte . "'
);";
mysql_query($sql_abfrage);        // Auftrag in die DB einfügen,
$_SESSION['blm_queries']++;

if (mysql_errno() > 0) {        // Der Auftrag ist schon in der DB (Spalten `Was`+`Von` sind UNIQUE!)
    DisconnectDB();
    header("location: ../?p=gebaeude&m=113");
    die();
}

$sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld-" . $forschungs_kosten . ",
    s.AusgabenForschung=s.AusgabenForschung+" . $forschungs_kosten . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);    // Falls der Auftrag noch nicht erteilt wurde, das Geld abziehen, und die Ausgaben für Forschung anpassen
$_SESSION['blm_queries']++;

// Alles erledigt :)
DisconnectDB();
header("location: ../?p=forschungszentrum&m=207#f" . $_POST['was']);
