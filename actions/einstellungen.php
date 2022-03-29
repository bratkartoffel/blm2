<?php
/**
 * Führt die Aktionen des Benutzers mit seinem Account aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wenn der Client nicht angemeldet ist, darf er auch nichts mit den Einstellungen machen :)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();    // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=einstellungen&m=112&" . time());
    die();
}

switch (intval($_REQUEST['a'])) {            // Was will der Benutzer überhaupt einstellen?
    case 1:        // Passwort ändern
        if ($ich->Passwort != sha1($_POST['pwd_alt'])) {        // Stimmt das alte Passwort welches er eingegeben hat? Wenn nicht, dann Abbruch
            DisconnectDB();
            header("location: ../?p=einstellungen&m=121&" . intval(time()));
            die();
        }

        if (sha1($_POST['new_pw1']) != sha1($_POST['new_pw2']) || $_POST['new_pw1'] == "") {        // Hat der Benutzer 2x das gleiche Kennwort eingegeben? Wenn nicht, dann Abbruch
            DisconnectDB();
            header("location: ../?p=einstellungen&m=105&" . intval(time()));
            die();
        }

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Passwort='" . sha1($_POST['new_pw1']) . "'
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Das neue Kennwort in die DB schreiben.
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=einstellungen&m=219&" . intval(time()) . "#Passwort");
        die();
        break;
    case 2:        // Account resetten
        if ($ich->Passwort != sha1($_POST['pwd_reset'])) {    // Stimmt das alte Passwort welches er eingegeben hat? Wenn nicht, dann Abbruch
            DisconnectDB();
            header("location: ../?p=einstellungen&m=121&" . intval(time()));
            die();
        }

        ResetAccount($_SESSION['blm_user'], $Start);        // Account resetten

        DisconnectDB();
        header("location: ../?p=einstellungen&m=220&" . intval(time()) . "#Reset");
        die();
        break;
    case 3:        // Account löschen
        if ($ich->Passwort != sha1($_POST['pwd_delete'])) {    // Stimmt das alte Passwort welches er eingegeben hat? Wenn nicht, dann Abbruch
            DisconnectDB();
            header("location: ../?p=einstellungen&m=121&" . intval(time()));
            die();
        }

        DeleteAccount($_SESSION['blm_user']);        // Account löschen. Schade...

        DisconnectDB();
        header("location: ./logout.php?del=1");
        die();
        break;
    case 4:        // Beschreibung ändern
        $beschreibung = $_POST['beschreibung'];        // Beschreibungstext abrufen

        if ($beschreibung == "")        // Hat er überhaupt eine Beschreibung eingegeben?
            $beschreibung = "NULL";        // NEIN: Datenbankfeld auf NULL setzen
        else
            $beschreibung = "'" . mysql_real_escape_string($beschreibung) . "'";    // Ja: Beschreibung escapen (Schutz vor SQL-Injection)

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Beschreibung=" . $beschreibung . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // neue Beschreibung in die Datenbank hauen
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=einstellungen&m=206&" . intval(time()) . "#Beschreibung");
        die();
        break;
    case 5:        // Profilbild hochladen
        DisconnectDB();        // Erst mal die Verbindung mit der Datenbank trennen, brauchen wir nicht mehr

        if ($_FILES['bild']['size'] == 0) {    // Hat er überhaupt was hochgeladen? Wenn nicht, dann...
            @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".jpg");        //...
            @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".png");        // lösche alle bisherigen Bilder, welche er haben könnte.
            @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".gif");        //
            header("location: ../?p=einstellungen&m=209&" . intval(time()));        // Somit wären wir fertig...
            die();
            break;
        }

        if (filesize($_FILES['bild']['tmp_name']) > BILD_GROESE_MAXIMAL) {        // Wenn das Bild größer als 64K ist, dann abbrechen!
            header("location: ../?p=einstellungen&m=103&" . intval(time()));
            die();
        }

        $typ = $_FILES['bild']['type'];        // Welchen Typ hat das (vielleicht)-Bild?

        switch ($typ) {
            case "image/jpeg":    //
            case "image/jpg":        // Das Bild ist ein JPG, ist OK
            case "image/pjpeg":    //
                $suffix = "jpg";
                break;
            case "image/gif":        // Ein GIF ist auch erlaubt
                $suffix = "gif";
                break;
            case "image/png":        // PNG darf er auch
                $suffix = "png";
                break;
            default:                        // Alles andere ist verboten!
                header("location: ../?p=einstellungen&m=107&" . intval(time()));
                die();
                break;
        }

        @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".jpg");        // Wenn er hier ankommt, dann will er wirklich ein Bild hochladen
        @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".png");        // und da jeder Benutzer nur ein Bild haben darf,
        @unlink("../pics/spieler/" . $_SESSION['blm_user'] . ".gif");        // braucht er das alte eh nicht mehr!

        move_uploaded_file($_FILES['bild']['tmp_name'], "../pics/spieler/" . $_SESSION['blm_user'] . "." . $suffix);    // Die hochgeladene Datei in das Profilbilderverzeichnis schieben
        chmod("../pics/spieler/" . $_SESSION['blm_user'] . "." . $suffix, 0766);        // Die Rechte des Bildes ändern (TODO: Muss das sein?)

        header("location: ../?p=einstellungen&m=210&" . intval(time()) . "#Bild"); // Alles erledigt :)
        die();
    case 6:        // EMail-Adresse ändern
        $email = $_POST['email'];
        if (!CheckEMail($email)) {
            header("location: ../?p=einstellungen&m=134");
            die();
        }

        $sql_abfrage = "UPDATE mitglieder SET EMail='" . $email . "' WHERE ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        header("location: ../?p=einstellungen&m=238&" . intval(time()) . "#EMail");
        die();
    case 7:        // Sitterrechte bearbeiten
        $aktiviert = intval($_POST['aktiviert']);

        $sql_abfrage = "SELECT
    Passwort
FROM
    sitter
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);

        $alt = mysql_fetch_object($sql_ergebnis);


        $sql_abfrage = "DELETE FROM
    sitter
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);
        if ($aktiviert == 0) {
            $_SESSION['blm_queries']++;

            DisconnectDB();
            header("location: ../?p=einstellungen&m=239");
            die();
        }

        $pw = $_POST['pw_sitter'];

        if ($pw != $alt->Passwort) {
            if ($pw != "") {
                $pw = sha1($pw);
            } else {
                DisconnectDB();
                header("location: ../?p=einstellungen&m=239");
                die();
            }
        }
        $gebaeude = intval($_POST['gebaeude']);
        $forschung = intval($_POST['forschung']);
        $produktion = intval($_POST['produktion']);
        $mafia = intval($_POST['mafia']);
        $nachrichten = intval($_POST['nachrichten']);
        $gruppe = intval($_POST['gruppe']);
        $vertraege = intval($_POST['vertraege']);
        $marktplatz = intval($_POST['marktplatz']);
        $bioladen = intval($_POST['bioladen']);
        $bank = intval($_POST['bank']);

        $sql_abfrage = "INSERT INTO
    sitter
(
    ID,
    Passwort,
    Gebaeude,
    Forschung,
    Produktion,
    Mafia,
    Nachrichten,
    Gruppe,
    Vertraege,
    Marktplatz,
    Bioladen,
    Bank
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $pw . "',
    '" . $gebaeude . "',
    '" . $forschung . "',
    '" . $produktion . "',
    '" . $mafia . "',
    '" . $nachrichten . "',
    '" . $gruppe . "',
    '" . $vertraege . "',
    '" . $marktplatz . "',
    '" . $bioladen . "',
    '" . $bank . "'
);";
        mysql_query($sql_abfrage) or die(mysql_error());
        $_SESSION['blm_queries']++;

        header("location: ../?p=einstellungen&m=240&" . intval(time()) . "#Sitter");
        die();
}
