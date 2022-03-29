<?php
/**
 * Führt die Aktionen des Benutzers mit dem Bioladen aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wenn der Client nicht angemeldet ist, darf er auch nichts mit der Bank machen :)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();    // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Bioladen && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=bioladen&m=112&" . time());
    die();
}

include("../include/preise.inc.php");        // Wir müssen wissen, was sein Zeug wert ist...

if (intval($_POST['was']) == 1337) {        // Er will alles auf einen Schlag verkaufen
    $erloese = 0;
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $Lager = "Lager" . $i;

        $erloese += $ich->$Lager * $Preis[$i];
    }

    $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld+" . $erloese . ",
    s.EinnahmenVerkauf=s.EinnahmenVerkauf+" . $erloese . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
    mysql_query($sql_abfrage);        // das Geld in die Geldbörse...

    $sql_abfrage = "UPDATE lagerhaus SET ";
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $sql_abfrage .= "Lager" . $i . "=0, ";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -2);
    $sql_abfrage .= " WHERE ID='" . $_SESSION['blm_user'] . "';";

    mysql_query($sql_abfrage);        // und die Waren aus dem Lager raus.

    DisconnectDB();
    header("location: ../?p=bioladen&m=208&" . time());
    die();
}

$verkaufs_preis = $Preis[intval($_POST['was'])];        // Der Verkaufspreis kommt aus dem Include der "preise.inc.php"
$verkaufs_menge = intval($_POST['menge']);                    // Die Menge, die er verkaufen will
$temp = "Lager" . intval($_POST['was']);        // Termporäre Variable mit dem MySQL-Spalten-Namen für die Ware bilden

if ($verkaufs_menge <= 0) {        // Er will ne negative Menge verkaufen? Geht nicht, einfach aus dem Minus ein Plus machen :)
    $verkaufs_menge *= -1;
}

if (intval($_POST['was']) <= 0 || intval($_POST['was']) > ANZAHL_WAREN) {        // Er will was verkaufen, was es nicht gibt ;)
    DisconnectDB();        // Verbindung trennen und abbrechen
    header("location: ../?p=bioladen&m=112&" . time());
    die();
}

if (intval($ich->$temp) < $verkaufs_menge) {        // Will der Spieler mehr verkaufen, als er auf Lager hat? - Abbruch
    DisconnectDB();
    header("location: ../?p=bioladen&m=116&" . time());
    die();
}

$sql_abfrage = "UPDATE
    (mitglieder m NATURAL JOIN lagerhaus l) NATURAL JOIN statistik s
SET
    m.Geld=m.Geld+" . ($verkaufs_menge * $verkaufs_preis) . ",
    s.EinnahmenVerkauf=s.EinnahmenVerkauf+" . ($verkaufs_menge * $verkaufs_preis) . ",
    l." . $temp . "=l." . $temp . "-" . $verkaufs_menge . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);        // Die Waren aus dem Lager raus, das Geld in die Geldbörse...
$_SESSION['blm_queries']++;

$sql_abfrage = "INSERT INTO
    log_bioladen
(
    Wer,
    Was,
    Wann,
    Wieviel,
    Preis
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . intval($_POST['was']) . "',
    NOW(),
    '" . $verkaufs_menge . "',
    '" . $verkaufs_preis . "'
);";
mysql_query($sql_abfrage);        // Logbuch
$_SESSION['blm_queries']++;

// Verbindung mit DB trennen, zurück zum Laden...
DisconnectDB();
header("location: ../?p=bioladen&m=208&" . time());
