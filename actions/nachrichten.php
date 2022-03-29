<?php
/**
 * Führt die Aktionen des Benutzers in seinem Postfach aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Nur wer angemeldet ist, darf auch Nachrichten schreiben...
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Nachrichten && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=nachrichten_liste&m=112&" . time());
    die();
}

switch (intval($_REQUEST['a'])) {    // Was will der Benutzer überhaupt?
    case 1:        // Nachricht verschicken
        $an = intval($_POST['an']);        // Die ID des Empfängers abrufen
        $von = intval($_SESSION['blm_user']);    // Von wem kommt die überhaupt?

        $betreff = trim($_POST['betreff']);                // Welchen Betreff soll die Nachricht haben?
        $nachricht = trim($_POST['nachricht']);        // Was ist überhaupt der Inhalt der Nachricht?

        if (intval($_POST['admin']) > 0) {
            $an = intval($_POST['admin']);
        }

        $zeit = time();        //Wann wurde die Nachricht verschickt?

        if ($an == 1337 && (IstAdmin() || IstBetatester())) {        // Das sind die Daten für ne Rundmail, welche nur der Administrator schicken darf.
            NachrichtAnAlle($betreff, $nachricht);    // Die eingegebene Nachricht an alle schicken

            // Fertig, Aufgabe erfüllt :)
            DisconnectDB();
            header("location: ../?p=nachrichten_liste&m=204&" . intval(time()));
            die();
        }

        if ($an <= 0 || $nachricht == "" || $an == $_SESSION['blm_user']) { // Ist ein Empfänger ausgewählt? Ist ne Nachricht eingegeben worden? Will er sich selbst was schreiben? Wenn eines davon wahr ist, dann abbrechen...
            DisconnectDB();
            header("location: ../?p=nachrichten_schreiben&m=104&" . intval(time()));
            die();
        }

        $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    An,
    Von,
    Zeit,
    Betreff,
    Nachricht,
    Gelesen
)
VALUES
(
    NULL,
    '" . $an . "',
    '" . $von . "',
    '" . $zeit . "',
    '" . mysql_real_escape_string($betreff) . "',
    '" . mysql_real_escape_string($nachricht) . "',
    '0'
)
;";
        mysql_query($sql_abfrage);        // Die Nachricht wegschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    log_nachrichten
(
    Wer,
    Wen,
    Wann,
    Betreff,
    Nachricht,
    Gelesen,
    Geloescht,
    Orig_ID
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $an . "',
    NOW(),
    '" . mysql_real_escape_string($betreff) . "',
    '" . mysql_real_escape_string($nachricht) . "',
    '0',
    '0',
    '" . mysql_insert_id() . "'
)
;";
        mysql_query($sql_abfrage);        // Die Nachricht wegschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder
SET
    IGMGesendet=IGMGesendet+1
WHERE
    ID='" . intval($_SESSION['blm_user']) . "'
;";
        mysql_query($sql_abfrage);        // Ein Update für die Statistik des Absenders
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder
SET
    IGMEmpfangen=IGMEmpfangen+1
WHERE
    ID='" . $an . "'
;";
        mysql_query($sql_abfrage);        // Ein Update für die Statistik des Empfängers
        $_SESSION['blm_queries']++;

        // Fertig :)
        DisconnectDB();
        header("location: ../?p=nachrichten_liste&m=204&" . intval(time()));
        die();
        break;
    case 2:        // eine Nachricht löschen
        $sql_abfrage = "DELETE FROM
    nachrichten
WHERE
    ID='" . intval($_GET['id']) . "'
AND
(
    An='" . intval($_SESSION['blm_user']) . "'
    OR
    (
            Von='" . intval($_SESSION['blm_user']) . "'
        AND
            Gelesen=0
    )
);";
        mysql_query($sql_abfrage);        // Die Nachricht aus der DB löschen
        $_SESSION['blm_queries']++;

        if (mysql_affected_rows() == 0) {        // Wenn die Nachricht nicht gelöscht werden konnte, dann wurde eine ungültige ausgewählt
            DisconnectDB();
            header("location: ../?p=nachrichten_liste&m=124&" . intval(time()));
            die();
        }

        $sql_abfrage = "UPDATE
    log_nachrichten
SET
    Geloescht=1
WHERE
    Orig_ID='" . intval($_GET['id']) . "'
AND
(
        Wen='" . intval($_SESSION['blm_user']) . "'
    OR
        Wer='" . intval($_SESSION['blm_user']) . "'
);";
        mysql_query($sql_abfrage);        // Die Nachricht aus der DB löschen
        $_SESSION['blm_queries']++;

        // Fertig :)

        if ($_GET['ajax'] != "1") {
            DisconnectDB();
            header("location: ../?p=nachrichten_liste&m=211&" . intval(time()));
            die();
        } else {
            die("1");
        }
        break;
    case 3:        // alle löschen
        $sql_abfrage = "DELETE FROM
    nachrichten
WHERE
    An='" . intval($_SESSION['blm_user']) . "';";
        mysql_query($sql_abfrage);    // Alle Nachrichten, die der Benutzer bekommen hat löschen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    log_nachrichten
SET
    Geloescht=1
WHERE
    Wen='" . intval($_SESSION['blm_user']) . "'
;";
        mysql_query($sql_abfrage);        // Die Nachricht aus der DB löschen
        $_SESSION['blm_queries']++;


        // Feierabend :)
        DisconnectDB();
        header("location: ../?p=nachrichten_liste&m=212&" . intval(time()));
        die();
        break;
}

// TODO: So, was jetzt?
