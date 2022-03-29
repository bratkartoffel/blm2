<?php
/**
 * Führt die Aktionen des Benutzers mit der Bank aus
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

if (!$ich->Sitter->Bank && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=bank&m=112&" . time());
    die();
}

$betrag = str_replace(",", ".", $_POST['betrag']);    //Um welchen Betrag geht es?

if ($betrag <= 0 || !is_numeric($betrag)) {            // Wenn der Benutzer negative Beträge angibt, dann abbrechen
    DisconnectDB();
    header("location: ../?p=bank&m=110&" . time());
    die();
}

switch (intval($_POST['art'])) {        // Was will der Benutzer überhaupt machen?
    case 1:                // Geld Einzahlen
        if ($betrag > $ich->Geld) {        // Will der Benutzer mehr einzahlen, als er Bar hat?
            DisconnectDB();
            header("location: ../?p=bank&m=110&" . time());
            die();
        }

        if ($ich->Punkte <= 100000) {
            if ($ich->Bank + $betrag >= 100000) {        // Will der Benutzer mehr einzahlen, als die Bank verwalten kann? (Hier fix 99.999,99 €)
                DisconnectDB();
                header("location: ../?p=bank&m=110&" . time());
                die();
            }
        } else {
            if ($ich->Bank + $betrag > $ich->Punkte) {        // Will der Benutzer mehr einzahlen, als die Bank verwalten kann? (Hier variabel nach Punkten)
                DisconnectDB();
                header("location: ../?p=bank&m=110&" . time());
                die();
            }
        }

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Geld=Geld-" . $betrag . ",
    Bank=Bank+" . $betrag . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    log_bank
(
    Wer,
    Wann,
    Wieviel,
    Einzahlen
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    NOW(),
    '" . $betrag . "',
    '1'
);";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen (Logbuch)
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=bank&m=207&" . time());
    case 2:    // Auszahlen / Kredit aufnehmen...
        if ($ich->Punkte <= 100000) {
            if ($ich->Bank - $betrag < -25000) {        // Das Kreditlimit liegt bei -25.000 €; Wird das Limit bei dem angeforderten Betrag überschritten?
                DisconnectDB();
                header("location: ../?p=bank&m=109&" . time());
                die();
            }
        } else {
            if ($ich->Bank - $betrag < -(0.25 * $ich->Punkte)) {        // Das Kreditlimit liegt bei 25% der eigenen Punkte; Wird das Limit bei dem angeforderten Betrag überschritten?
                DisconnectDB();
                header("location: ../?p=bank&m=109&" . time());
                die();
            }
        }

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Geld=Geld+" . $betrag . ",
    Bank=Bank-" . $betrag . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    log_bank
(
    Wer,
    Wann,
    Wieviel,
    Einzahlen
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    NOW(),
    '" . $betrag . "',
    '0'
);";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen (Logbuch)
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=bank&m=207&" . time());
    case 3:    // In die Gruppenkasse zahlen
        if ($betrag > $ich->Geld) {        // Will der Benutzer mehr einzahlen, als er Bar hat?
            DisconnectDB();
            header("location: ../?p=bank&m=110&" . time());
            die();
        }

        if (intval($ich->Gruppe) == 0) {
            DisconnectDB();
            header("location: ../?p=bank&" . time());
            die();
        }

        $sql_abfrage = "UPDATE
    gruppe
SET
    Kasse=Kasse+" . $betrag . "
WHERE
    ID='" . $ich->Gruppe . "';";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Geld=Geld-" . $betrag . ",
    GruppeKassenStand=GruppeKassenStand+" . $betrag . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Update auf die Datenbank ausführen
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=bank&m=235&" . time());
    default:        // Was zum Teufel will er überhaupt??? Abbrechen!!!
        DisconnectDB();
        header("location: ../?p=bank&m=112&" . time());
}
die();
