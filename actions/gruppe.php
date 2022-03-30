<?php
/**
 * Führt die Aktionen des Benutzers in seiner Gruppe aus
 *
 * @version 1.0.1
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

$ich = LoadSettings();
$ich->Rechte = RechteGruppe(0, false, $ich->GruppeRechte);

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Gruppe && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=gruppe&m=112");
    die();
}

$Anfrage[1] = "NAP";
$Anfrage[2] = "BND";
$Anfrage[3] = "Krieg";

switch (intval($_REQUEST['a'])) {
    case 1:    // neue Gruppe gründen
        if ($ich->Gebaeude1 < 8 || intval($ich->Gruppe) > 0) {        // Darf der Benutzer überhaupt eine neue Gruppe gründen? Wenn nicht, dann...
            DisconnectDB();
            header("location: ../?p=gruppe&m=112");
            die();
        }

        $name = mysql_real_escape_string(trim($_POST['name']));                        // Zuerst einmal holen wir
        $kuerzel = mysql_real_escape_string(trim($_POST['kuerzel']));            // alle wichtigen Daten ab
        $passwort = sha1($_POST['passwort']);                                                            // und überprüfen dann erst

        if ($name == "" || $kuerzel == "" || $passwort == sha1("")) {        // Alle Felder sind Pflichtfelder, das heisst,
            DisconnectDB();                                                                                                // wenn er eines leer gelassen hat, dann brechen wir ab
            header("location: ../?p=gruppe&m=104");
            die();
        }

        $sql_abfrage = "INSERT INTO
    gruppe
(
    ID,
    Name,
    Kuerzel,
    Beschreibung,
    Passwort
)
VALUES
(
    NULL,
    '" . $name . "',
    '" . $kuerzel . "',
    NULL,
    '" . $passwort . "'
);";
        $sql_ergebnis = mysql_query($sql_abfrage);            // Dann fügen wir die Gruppe in die Datenbank ein
        $_SESSION['blm_queries']++;

        if (mysql_errno() > 0) {            // Falls ein Fehler auftritt, dann existiert die Gruppe bereits
            DisconnectDB();
            header("location: ../?p=gruppe&m=126");
            die();
        }

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Gruppe='" . mysql_insert_id() . "',
    GruppeRechte='2047',
    GruppeKassenStand=0,
    GruppeLastMessageZeit=0
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);            // Dann treten wir der Gruppe bei
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . mysql_insert_id() . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Die Gruppe wird von <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> gegründet.'
);";
        mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=gruppe&m=223");
        // Fertig! Zurück zum Gruppenbildschirm, wo jetzt das Gruppenboard stehen sollte :)
        break;
    case 2:        // bestehender Gruppe beitreten
        if ($ich->Gebaeude1 < 5 || intval($ich->Gruppe) > 0) {        // Darf der USer überhaupt einer Gruppe beitreten?
            DisconnectDB();                                                                                // Wenn nicht, dann brechen wir mal wieder ab
            header("location: ../?p=gruppe&m=112");
            die();
        }

        $name = mysql_real_escape_string(trim($_POST['name']));        // Dann holen wir uns die für den Beitritt
        $passwort = sha1($_POST['pwd_beitritt']);                                    // wichtigen Daten ab

        $sql_abfrage = "SELECT
    ID,
    ID AS gID,
    (SELECT COUNT(*) FROM mitglieder WHERE Gruppe=gID) AS anzMitglieder
FROM
    gruppe
WHERE
    Name='" . $name . "'
AND
    Passwort='" . $passwort . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);            // Schauen wir mal, ob es eine Gruppe mit den angegebenen Daten gibt.
        $_SESSION['blm_queries']++;

        $gruppe = mysql_fetch_object($sql_ergebnis);        // Hier holen wir uns die ID der Gruppe

        if (!property_exists($gruppe, 'ID')) {            // Wenn die ID nicht gesetzt ist, dann gibts die Gruppe nicht, also brechen wir ab
            DisconnectDB();
            header("location: ../?p=gruppe&m=127");
            die();
        }

        if ($gruppe->anzMitglieder >= MAX_ANZAHL_GRUPPENMITGLIEDER) {    // Die Gruppe ist voll
            DisconnectDB();
            header("location: ../?p=gruppe&m=140");
            die();
        }

        $sql_abfrage = "UPDATE
    mitglieder
SET
    Gruppe='" . $gruppe->ID . "',
    GruppeRechte='1',
    GruppeKassenStand=0,
    GruppeLastMessageZeit=0
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Dann treten wir der Gruppe bei, mit den niedrigsten Rechten
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $gruppe->ID . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> tritt der Gruppe bei.'
);";
        mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=gruppe&m=224");
        // Fertig. Der User ist in der Gruppe, und sollte nun zum Gruppenboard weitergeleitet werden.
        break;
    case 3:        // Gruppe verlassen
        $sql_abfrage = "UPDATE
    mitglieder
SET
    Gruppe=NULL,
    GruppeRechte=NULL,
    GruppeKassenStand=NULL,
    GruppeLastMessageZeit=NULL
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);            // Der User will die Gruppe verlassen, also setzen wir seine Gruppe auf NULL
        $_SESSION['blm_queries']++;            // ... und sicherheitshalber auch gleich noch die Rechte in der Gruppe

        $sql_abfrage = "SELECT
    COUNT(*) AS Anzahl
FROM
    mitglieder
WHERE
    Gruppe='" . $ich->Gruppe . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);        // Dann rufen wir ab, wieviele Mitglieder noch in der Gruppe sind
        $_SESSION['blm_queries']++;

        $temp = mysql_fetch_object($sql_ergebnis);

        if ($temp->Anzahl == 0) {            // Falls die Gruppe leer ist, dann löschen wir auch gleich noch die Gruppe
            $sql_abfrage = "DELETE FROM
    gruppe
WHERE
    ID='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Zuerst löschen wir gleich mal die Gruppe
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe_nachrichten
WHERE
    Gruppe='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Dann die Gruppennachrichten
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe_diplomatie
WHERE
    Von='" . $ich->Gruppe . "'
OR
    An='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // und die Gruppenbeziehungen
            $_SESSION['blm_queries']++;

            @unlink("../pics/gruppe/" . $ich->Gruppe . ".jpg");        //
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".png");        // und das Gruppenbild, falls vorhanden
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".gif");        //
        }

        $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $ich->Gruppe . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> verlässt freiwillig die Gruppe.'
);";
        mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
        $_SESSION['blm_queries']++;

        DisconnectDB();        // Verbindung mit der DB trennen
        header("location: ../?p=gruppe&m=225");        // Und Weiterleitung machen

        // Fertig! Benutzer ist nicht mehr in der Gruppe
        break;
    case 4:        // Gruppennachricht schreiben
        $nachricht = mysql_real_escape_string(trim($_POST['nachricht']));    // Zuerst holen wir uns alle
        $zeit = time();                                                                                                        // relevanten Daten zusammen,
        $gruppe = intval($ich->Gruppe);                                                                        // die wir für die Nachricht
        $von = $_SESSION['blm_user'];                                                                            // brauchen werden.

        if ($gruppe == 0 || $nachricht == "") {            // Wenn der Benutzer gar keine Gruppe hat, oder die Nachricht leer ist, dann abbrechen!
            DisconnectDB();
            header("location: ../?p=gruppe&m=128");
            die();
        }

        if ($ich->Rechte->NachrichtSchreiben) {        // Reichen die Rechte des Benutzers aus, um eine Gruppennachricht zu schreiben?
            $sql_abfrage = "INSERT INTO
    gruppe_nachrichten
(
    ID,
    Von,
    Gruppe,
    Zeit,
    Nachricht
)
VALUES
(
    NULL,
    '" . $von . "',
    '" . $gruppe . "',
    '" . $zeit . "',
    '" . $nachricht . "'
);";
            mysql_query($sql_abfrage);        // Wenn ja, dann schreib die Nachricht in die DB

            DisconnectDB();
            header("location: ../?p=gruppe&m=204");
            die(); // Fertig. STIRB!!! Aber erst nach der Weiterleitung :)
        }

        DisconnectDB();                                                        // Wenn es der User bis hierher
        header("location: ../?p=gruppe&m=112");        // schafft, dann darf er keine
        // Nachrichten schreiben...
        break;
    case 5:            // Gruppennachricht löschen
        $id = intval($_GET['id']);            // Zuerst holen wir uns wieder
        $gruppe = intval($ich->Gruppe);    // alle wichtigen Daten zusammen

        if ($ich->Rechte->NachrichtLoeschen) {        // Reichen die Rechte des Users aus?
            $sql_abfrage = "DELETE FROM
    gruppe_nachrichten
WHERE
    ID='" . $id . "'
AND
    Gruppe='" . $gruppe . "';";
            mysql_query($sql_abfrage);        // Wenn ja, dann lösche die Nachricht

            if ($_GET['ajax'] != "1") {
                DisconnectDB();                                                        // Verbindung trennen,
                header("location: ../?p=gruppe&m=211");        // Weiterleitung,
                die();                                                                        // Skript stoppen
            } else {
                die("1");
            }
        }

        DisconnectDB();                                                        // Wenn es der User bis hierher
        header("location: ../?p=gruppe&m=112");        // schafft, dann darf er keine
        // Nachrichten löschen...
        break;
    case 6:    // Rechte ändern
        $id = intval($_GET['id']);
        $recht = intval($_GET['recht']);

        if ($ich->Rechte->MitgliederRechte) {        // Nur wenn der User die Rechte überhaupt ändern darf
            $sql_abfrage = "UPDATE
    mitglieder
SET
    GruppeRechte=(GruppeRechte ^ " . $recht . ")
WHERE
    ID='" . $id . "'
AND
    Gruppe='" . $ich->Gruppe . "'
AND
    ID !='" . $_SESSION['blm_user'] . "'
AND
    GruppeRechte<2047;";
            mysql_query($sql_abfrage);    // Ändert die Rechte eines Users per EXKLUSIVEN ODER in der Datenbank

            $sql_abfrage = "SELECT
    ID,
    Name,
    GruppeRechte
FROM
    mitglieder
WHERE
    ID='" . $id . "'
AND
    Gruppe='" . $ich->Gruppe . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);

            $mitglied = mysql_fetch_object($sql_ergebnis);

            $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $ich->Gruppe . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> hat die Rechte für <a href=\"./?p=profil&amp;uid=" . $mitglied->ID . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($mitglied->ID)) . "</a> auf " . $mitglied->GruppeRechte . " geändert.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            if ($_GET['ajax'] != "1") {
                DisconnectDB();        // Verbindung trennen,
                header("location: ../?p=gruppe_mitgliederverwaltung&m=226"); // und zurück zur Mitgliederverwaltung,
                die();                            // wir sind hier fertig.
            } else {
                die("1");
            }
        }

        DisconnectDB();                                                        // Wenn es der User bis hierher
        header("location: ../?p=gruppe&m=112");        // schafft, dann darf er keine
        // Rechte ändern...
        break;
    case 7:        // Mitglied kicken
        $id = intval($_GET['id']);

        if ($ich->Rechte->MitgliedKicken) {        // Wenn der User einen anderen kicken darf
            $sql_abfrage = "UPDATE
    mitglieder
SET
    Gruppe=NULL,
    GruppeRechte=NULL,
    GruppeKassenStand=NULL,
    GruppeLastMessageZeit=NULL
WHERE
    ID='" . $id . "'
AND
    Gruppe='" . $ich->Gruppe . "'
AND
    ID !='" . $_SESSION['blm_user'] . "';";
            mysql_query($sql_abfrage);        // Dann setzte bei diesem die Gruppe auf NULL

            $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $ich->Gruppe . "',
    '" . $id . "',
    '" . time() . "',
    'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $id . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($id)) . "</a> wird von <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> aus der Gruppe verwiesen.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            DisconnectDB();        // Verbindung trennen,
            header("location: ../?p=gruppe_mitgliederverwaltung&m=227");    // und weiterleitung
            die();        // und abbrechen
        }

        DisconnectDB();        // Wenn er es bis hierher schafft, dann darf er keine Mitglieder kicken, also
        header("location: ../?p=gruppe&m=112");    // Verbindung mit DB trennen,
        // Weiterleitung & Meldung ausgeben, abbrechen
        break;
    case 8:    // Gruppenbild hochladen
        DisconnectDB();        // Erst mal die Verbindung mit der Datenbank trennen, brauchen wir nicht mehr

        if ($ich->Rechte->GruppeBild) {
            if ($_FILES['bild']['size'] == 0) {    // Hat er überhaupt was hochgeladen? Wenn nicht, dann...
                @unlink("../pics/gruppe/" . $ich->Gruppe . ".jpg");        //...
                @unlink("../pics/gruppe/" . $ich->Gruppe . ".png");        // lösche alle bisherigen Bilder, welche er haben könnte.
                @unlink("../pics/gruppe/" . $ich->Gruppe . ".gif");        //
                header("location: ../?p=gruppe_einstellungen&m=209");        // Somit wären wir fertig...
                die();
            }

            if (filesize($_FILES['bild']['tmp_name']) > BILD_GROESE_MAXIMAL) {        // Wenn das Bild größer als 64K ist, dann abbrechen!
                header("location: ../?p=gruppe_einstellungen&m=103");
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
                    header("location: ../?p=gruppe_einstellungen&m=107");
                    die();
            }

            @unlink("../pics/gruppe/" . $ich->Gruppe . ".jpg");        // Wenn er hier ankommt, dann will er wirklich ein Bild hochladen
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".png");        // und da jeder Benutzer nur ein Bild haben darf,
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".gif");        // braucht er das alte eh nicht mehr!

            move_uploaded_file($_FILES['bild']['tmp_name'], "../pics/gruppe/" . $ich->Gruppe . "." . $suffix);    // Die hochgeladene Datei in das Profilbilderverzeichnis schieben
            chmod("../pics/gruppe/" . $ich->Gruppe . "." . $suffix, 0766);        // Die Rechte des Bildes ändern (TODO: Muss das sein?)

            header("location: ../?p=gruppe_einstellungen&m=210"); // Alles erledigt :)
            die();
        }

        /*
            Wenn der User es bis hierher schafft, darf er das Bild nicht ändern,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 9:    // Beschreibung bearbeiten
        $beschreibung = mysql_real_escape_string(trim($_POST['beschreibung']));        // Holt sioch die Beschreibung erst mal aus den POST-Daten raus

        if ($beschreibung == "") {        // Wenn keine Beschreibung eingegeben wurde, dann setze diese auf NULL
            $beschreibung = "NULL";
        } else {        // Ansonsten brauchen wir die Hochkommas, wir wollen ja nen Text in die DB einfügen
            $beschreibung = "'" . $beschreibung . "'";
        }

        if ($ich->Rechte->GruppeBeschreibung) {        // Reichen die Rechte des Users aus, die Beschreibung zu ändern?
            $sql_abfrage = "UPDATE
    gruppe
SET
    Beschreibung=" . $beschreibung . "
WHERE
    ID='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Ändert die Beschreibung der Gruppe

            // Alles erfolgreich, also Standard, Verbindung trennen, Weiterleitung, Abbrechen
            DisconnectDB();
            header("location: ../?p=gruppe_einstellungen&m=206");
            die();
        }

        /*
            Wenn der User es bis hierher schafft, darf er die Beschreibung nicht ändern,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 10:    // Beitrittskennwort ändern
        $pwd1 = $_POST['pwd_1'];        // Das erste Textfeld
        $pwd2 = $_POST['pwd_2'];        // Die Bestätigung des Passworts

        if ($pwd1 == "" || $pwd1 != $pwd2) {        // Falls beide Kennwörter nicht übereinstimmen, oder leer sind
            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_einstellungen&m=105");    // Weiterleitung
            die();    // Abbrechen
        }

        if ($ich->Rechte->GruppePasswort) {    // Darf der User das Kennwort überhaupt ändern?
            $sql_abfrage = "UPDATE
    gruppe
SET
    Passwort='" . sha1($pwd1) . "'
WHERE
    ID='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Ändert das Passwort

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_einstellungen&m=219");    // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er das Passwort nicht ändern,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */

        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 11:    // Gruppe löschen
        if ($ich->Rechte->GruppeLoeschen) {        // Hat der Benutzer ausreichende Rechte zum Löschen der Gruppe?
            $sql_abfrage = "UPDATE
    mitglieder
SET
    Gruppe=NULL,
    GruppeRechte=NULL,
    GruppeKassenStand=NULL,
    GruppeLastMessageZeit=NULL
WHERE
    Gruppe='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Wenn ja, dann schmeiß ihn erst mal aus der Gruppe
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe
WHERE
    ID='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Lösche die Gruppe
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe_nachrichten
WHERE
    Gruppe='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);            // Nachrichten löschen
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe_diplomatie
WHERE
    An='" . $ich->Gruppe . "'
OR
    Von='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);            // Diplomatische Beziehungen löschen
            $_SESSION['blm_queries']++;

            $sql_abfrage = "DELETE FROM
    gruppe_logbuch
WHERE
    Gruppe='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);            // Logbuch löschen
            $_SESSION['blm_queries']++;

            @unlink("../pics/gruppe/" . $ich->Gruppe . ".jpg");        //
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".png");        // Gruppenbild löschen, falls vorhanden
            @unlink("../pics/gruppe/" . $ich->Gruppe . ".gif");        //

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe&m=228");        // Weiterleitung
            die();        // Abbrechen
        }


        /*
            Wenn der User es bis hierher schafft, darf er die Gruppe nicht löschen,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */

        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 12:        // Diplomatische Beziehung eintragen
        $partner = intval($_POST['partner']);        // Mit wem soll der Vertrag geschlossen werden?
        $typ = intval($_POST['typ']);        // Welchen Typ von Vertrag soll es werden? (NAP, BND, Krieg)
        $betrag = "";

        if ($ich->Rechte->Diplomatie) {        // Hat der Benutzer ausreichende Rechte?
            if ($typ == 3) {
                $krieg_felder = ", PunktePlus, PunkteMinus, Betrag";
                $betrag = "'" . intval(str_replace(".", "", $_POST['betrag'])) . "'";
                $krieg_werte = ", 0, 0, " . $betrag;

                if (intval(str_replace(".", "", $_POST['betrag'])) < 100000) {
                    header("location: ../?p=gruppe_diplomatie&m=132");
                    die();
                }
            }

            $sql_abfrage = "INSERT INTO
	gruppe_diplomatie
(
	ID,
	Von,
	An,
	Typ,
	Seit,
	Bis
	" . $krieg_felder . "
)
VALUES
(
	NULL,
	'" . $ich->Gruppe . "',
	'" . $partner . "',
	'" . $typ . "',
	NULL,
	NULL
	" . $krieg_werte . "
);";
            mysql_query($sql_abfrage);            // Diplomatische Beziehungen eintragen
            $_SESSION['blm_queries']++;            // erst mal nur von uns aus

            if (mysql_errno() == 1062 || mysql_errno() == 1452) {        // Gibts schon eine Beziehung mit dem Partner, wenn ja
                DisconnectDB();        // Dann Verbindung trennen
                header("location: ../?p=gruppe_diplomatie&m=129");    // Weiterleitung
                // Abbrechen
            } else {            // Beziehung erfolgreich eingetragen
                $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $ich->Gruppe . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> hat eine diplomatische Anfrage (" . $Anfrage[$typ] . ") an <a href=\"./?p=gruppe&amp;id=" . $partner . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($partner)) . "</a> gestellt.'
);";
                mysql_query($sql_abfrage) or die(mysql_error());            // Dann wird ein Eintrag ins Logbuch gemacht.
                $_SESSION['blm_queries']++;

                $sql_abfrage = "INSERT INTO
    gruppe_logbuch
(
    ID,
    Gruppe,
    Spieler,
    Datum,
    Text
)
VALUES
(
    NULL,
    '" . $partner . "',
    '" . $_SESSION['blm_user'] . "',
    '" . time() . "',
    'Die Gruppe hat eine diplomatische Anfrage (" . $Anfrage[$typ] . ") von <a href=\"./?p=gruppe&amp;id=" . $ich->Gruppe . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($ich->Gruppe)) . "</a> erhalten.'
);";
                mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
                $_SESSION['blm_queries']++;

                DisconnectDB();            // Verbindung trennen
                header("location: ../?p=gruppe_diplomatie&m=229");        // Weiterleitung
                // Abbrechen
            }
            die();
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Beziehungen eintragen,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 13:        // Diplomatisches Angebot zurückziehen
        $id = intval($_GET['id']);        // Welches Angebot soll zurückgezogen werden?

        if ($ich->Rechte->Diplomatie) {        // Hat der Benutzer ausreichende Rechte?
            $sql_abfrage = "SELECT
	*
FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	Von='" . $ich->Gruppe . "'
AND
	Seit IS NULL;";
            $sql_ergebnis = mysql_query($sql_abfrage);

            $vertrag = mysql_fetch_object($sql_ergebnis);

            $sql_abfrage = "DELETE FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	Von='" . $ich->Gruppe . "'
AND
	Seit IS NULL;";
            mysql_query($sql_abfrage);            // Diplomatische Beziehungen eintragen
            $_SESSION['blm_queries']++;            // erst mal nur von uns aus

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $ich->Gruppe . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> hat die diplomatische Anfrage (" . $Anfrage[$vertrag->Typ] . ") an <a href=\"./?p=gruppe&amp;id=" . $vertrag->An . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($vertrag->An)) . "</a> zurückgezogen.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $vertrag->ID . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das diplomatische Angebot (" . $Anfrage[$vertrag->Typ] . ") von <a href=\"./?p=gruppe&amp;id=" . $ich->Gruppe . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($ich->Gruppe)) . "</a> wurde zurückgezogen.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            // Beziehung erfolgreich gelöscht
            DisconnectDB();            // Verbindung trennen
            header("location: ../?p=gruppe_diplomatie&m=230");        // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Beziehungen managen,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 14:            // Diplomatische Anfrage annehmen
        $id = intval($_GET['id']);        // Ruft die ID des Vertrag ab

        if ($ich->Rechte->Diplomatie) {        // Darf der Benutzer überhaupt diplomatische Sachen regeln?
            $sql_abfrage = "SELECT
	*
FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	An='" . $ich->Gruppe . "'";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Wenn ja, dann hol die Infos zum Vertrag aus der DB
            $_SESSION['blm_queries']++;

            $anfrage = mysql_fetch_object($sql_ergebnis);

            if ($anfrage->Typ == 3) {
                $krieg_felder = ", PunktePlus, PunkteMinus, Betrag";
                $betrag = "'" . $anfrage->Betrag . "'";
                $krieg_werte = ", 0, 0, " . $betrag;
            }

            $sql_abfrage = "UPDATE
	gruppe_diplomatie
SET
	Seit='" . time() . "',
	Bis='" . (time() + 604800) . "'
WHERE
	ID='" . $id . "'
AND
	An='" . $ich->Gruppe . "';";
            mysql_query($sql_abfrage);        // Setze die Zeit des Vertrags auf den aktuellen Zeitpunkt, also ist er ab sofort gültig
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_diplomatie
(
	ID,
	Von,
	An,
	Typ,
	Seit,
	Bis
	" . $krieg_felder . "
)
VALUES
(
	NULL,
	'" . $anfrage->An . "',
	'" . $anfrage->Von . "',
	'" . $anfrage->Typ . "',
	'" . time() . "',
	'" . (time() + 604800) . "'
	" . $krieg_werte . "
);";
            mysql_query($sql_abfrage);            // Der Vertrag ist beidseitig, also für die anderen auch einfügen
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $ich->Gruppe . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> hat eine diplomatische Anfrage (" . $Anfrage[$anfrage->Typ] . ") von <a href=\"./?p=gruppe&amp;id=" . $anfrage->Von . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($anfrage->Von)) . "</a> angenommen.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $anfrage->Von . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Die Gruppe <a href=\"./?p=gruppe&amp;id=" . $anfrage->Von . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($anfrage->Von)) . "</a> hat die diplomatische Anfrage (" . $Anfrage[$anfrage->Typ] . ") angenommen.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            if ($anfrage->Typ == 3) {
                $sql_abfrage = "UPDATE
    gruppe
SET
    Kasse=Kasse-" . $anfrage->Betrag . "
WHERE
    ID = " . $anfrage->Von . "
OR
    ID = " . $anfrage->An . ";";
                mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
                $_SESSION['blm_queries']++;
            }

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_diplomatie&m=231");        // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Diplomatischen Sachen regeln,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();
        header("location: ../?p=gruppe&m=112");
        break;
    case 15:            // Diplomatische Anfrage ablehnen
        $id = intval($_GET['id']);

        if ($ich->Rechte->Diplomatie) {        // Darf der Benutzer überhaupt diplomatische Sachen regeln?
            $sql_abfrage = "SELECT
	*
FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	An='" . $ich->Gruppe . "'";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Wenn ja, dann hol die Infos zum Vertrag aus der DB
            $_SESSION['blm_queries']++;

            $vertrag = mysql_fetch_object($sql_ergebnis);

            $sql_abfrage = "DELETE FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	An='" . $ich->Gruppe . "'
And
	Seit IS NULL";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Beziehung löschen
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $ich->Gruppe . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das Mitglied <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> hat eine diplomatische Anfrage (" . $Anfrage[$vertrag->Typ] . ") von <a href=\"./?p=gruppe&amp;id=" . $vertrag->Von . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($vertrag->Von)) . "</a> abgelehnt.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $vertrag->ID . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Die diplomatische Anfrage (" . $Anfrage[$vertrag->Typ] . ") an <a href=\"./?p=gruppe&amp;id=" . $vertrag->ID . "\">" . htmlentities(stripslashes($vertrag->Name), ENT_QUOTES, "UTF-8") . "</a> wurde abgelehnt.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_diplomatie&m=216");        // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Diplomatischen Sachen regeln,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();        // Verbindung trennen
        header("location: ../?p=gruppe&m=112");        // Weiterleitung
        // Abbrechen
        break;
    case 16:    // Geld aus Kasse an Mitglied überweisen
        $betrag = mysql_real_escape_string(str_replace(",", ".", $_POST['betrag']));
        $an = intval($_POST['an']);
        $bank = intval($_POST['bank']);

        if ($ich->Rechte->GruppeKasse) {        // Darf der Benutzer überhaupt diplomatische Sachen regeln?
            $sql_abfrage = "SELECT
	ID,
	Kasse
FROM
	gruppe
WHERE
	ID='" . $ich->Gruppe . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;
            $gruppe = mysql_fetch_object($sql_ergebnis);

            $sql_abfrage = "SELECT
	Bank,
	Punkte
FROM
	mitglieder
WHERE
	ID='" . $an . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;
            $mitglied = mysql_fetch_object($sql_ergebnis);


            if (intval($gruppe->ID) == 0) {        // Gruppe wurde nicht gefunden
                DisconnectDB();        // Verbindung trennen
                header("location: ../?p=gruppe_kasse&m=112");        // Weiterleitung
                die();        // Abbrechen
            }

            if ($gruppe->Kasse < $betrag || $betrag <= 0) {        // Soviel haben die gar nicht in der Kasse
                DisconnectDB();        // Verbindung trennen
                header("location: ../?p=gruppe_kasse&m=110");        // Weiterleitung
                die();        // Abbrechen
            }

            if ($bank) {
                if ($mitglied->Punkte <= 100000) {
                    if ($mitglied->Bank + $betrag > 100000) {        // Die Bank des Mitglieds ist VOLL! (Hier fix 99.999,99 €)
                        DisconnectDB();        // Verbindung trennen
                        header("location: ../?p=gruppe_kasse&m=131");        // Weiterleitung
                        die();        // Abbrechen
                    }
                } else {
                    if ($mitglied->Bank + $betrag > $mitglied->Punkte) {        // Die Bank des Mitglieds ist VOLL! (Hier variabel seine Punkte)
                        DisconnectDB();        // Verbindung trennen
                        header("location: ../?p=gruppe_kasse&m=131");        // Weiterleitung
                        die();        // Abbrechen
                    }
                }

                $sql_abfrage = "UPDATE
    mitglieder
SET
    Bank=Bank+" . $betrag . ",
    GruppeKassenStand=GruppeKassenStand-" . $betrag . "
WHERE
    ID='" . $an . "';";
                $sql_ergebnis = mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                $sql_abfrage = "INSERT INTO
    log_gruppenkasse
(
    Wer,
    Wen,
    Wann,
    Wieviel,
    Bank
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $an . "',
    NOW(),
    '" . $betrag . "',
    '1'
);";
            } else {
                $sql_abfrage = "UPDATE
    mitglieder
SET
    Geld=Geld+" . $betrag . ",
    GruppeKassenStand=GruppeKassenStand-" . $betrag . "
WHERE
    ID='" . $an . "';";
                $sql_ergebnis = mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                $sql_abfrage = "INSERT INTO
    log_gruppenkasse
(
    Wer,
    Wen,
    Wann,
    Wieviel,
    Bank
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $an . "',
    NOW(),
    '" . $betrag . "',
    '0'
);";
            }
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            $sql_abfrage = "UPDATE
	gruppe
SET
	Kasse=Kasse-" . $betrag . "
WHERE
	ID='" . $ich->Gruppe . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_kasse&m=236");        // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Überweisungen machen,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();        // Verbindung trennen
        header("location: ../?p=gruppe&m=112");        // Weiterleitung
        // Abbrechen
        break;
    case 17:            // Diplomatische Beziehung kündigen
        $id = intval($_GET['id']);

        if ($ich->Rechte->Diplomatie) {        // Darf der Benutzer überhaupt diplomatische Sachen regeln?
            $sql_abfrage = "SELECT
	*
FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	Von='" . $ich->Gruppe . "'
AND
	Bis<" . time() . "
AND
	Typ < 3;";
            $sql_ergebnis = mysql_query($sql_abfrage);

            $vertrag = mysql_fetch_object($sql_ergebnis);

            if (intval($vertrag->ID) == 0) {
                DisconnectDB();        // Verbindung trennen
                header("location: ../?p=gruppe_diplomatie&m=112");        // Weiterleitung
                die();        // Abbrechen
            }

            $sql_abfrage = "DELETE FROM
	gruppe_diplomatie
WHERE
(
	Von='" . $ich->Gruppe . "'
OR
	An='" . $ich->Gruppe . "'
)
AND
(
	Von='" . $vertrag->An . "'
OR
	An='" . $vertrag->An . "'
)
AND
	Bis<" . time() . ";";
            mysql_query($sql_abfrage);        // Beziehung löschen
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $ich->Gruppe . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das diplomatische Verhältnis (" . $Anfrage[$vertrag->Typ] . ") mit <a href=\"./?p=gruppe&amp;id=" . $vertrag->An . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($vertrag->An)) . "</a> wurde von  <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> aufgelöst.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            $sql_abfrage = "INSERT INTO
	gruppe_logbuch
(
	ID,
	Gruppe,
	Spieler,
	Datum,
	Text
)
VALUES
(
	NULL,
	'" . $vertrag->An . "',
	'" . $_SESSION['blm_user'] . "',
	'" . time() . "',
	'Das diplomatische Verhältnis (" . $Anfrage[$vertrag->Typ] . ") mit <a href=\"./?p=gruppe&amp;id=" . $ich->Gruppe . "\">" . sichere_ausgabe(Database::getInstance()->getGroupNameById($ich->Gruppe)) . "</a> wurde von  <a href=\"./?p=profil&amp;uid=" . $_SESSION['blm_user'] . "\">" . sichere_ausgabe(Database::getInstance()->getPlayerNameById($_SESSION['blm_user'])) . "</a> aufgelöst.'
);";
            mysql_query($sql_abfrage);            // Dann wird ein Eintrag ins Logbuch gemacht.
            $_SESSION['blm_queries']++;

            DisconnectDB();        // Verbindung trennen
            header("location: ../?p=gruppe_diplomatie&m=230");        // Weiterleitung
            die();        // Abbrechen
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Diplomatischen Sachen regeln,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();        // Verbindung trennen
        header("location: ../?p=gruppe&m=112");        // Weiterleitung
        // Abbrechen
        break;
    case 18:
        $id = intval($_GET['id']);

        if ($ich->Rechte->Diplomatie) {        // Darf der Benutzer überhaupt diplomatische Sachen regeln?
            $sql_abfrage = "SELECT
	Betrag,
	An
FROM
	gruppe_diplomatie
WHERE
	ID='" . $id . "'
AND
	Von='" . $ich->Gruppe . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            $krieg = mysql_fetch_object($sql_ergebnis);

            if ($krieg->Betrag) {
                // Als erstes wird der Gewinn verbucht

                $sql_abfrage = "UPDATE
    gruppe
SET
    Kasse=Kasse+" . $krieg->Betrag . "
WHERE
    ID='" . $krieg->An . "';";
                mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                // Dann werden den Verlierern 5% ihrer Puntke abgezogen

                $sql_abfrage = "UPDATE
    mitglieder m
SET
    Punkte=m.Punkte*0.95,
WHERE
    Gruppe='" . $krieg->An . "';";
                mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                // Zum Schluss werden die Plantagen der Verlierer noch um 1 Stufe gesenkt.

                $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN gebaeude g
SET
    g.Gebaeude1=g.Gebaeude1-1
WHERE
    m.Gruppe='" . $krieg->An . "'
AND
    g.Gebaeude1>2;";
                mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                // Zum Schluss wird der Krieg noch beendet

                $sql_abfrage = "DELETE FROM
    gruppe_diplomatie
WHERE
    (
        Von='" . $ich->Gruppe . "'
    AND
        An='" . $krieg->An . "'
    AND
        Typ=3
    )
    OR
    (
        An='" . $ich->Gruppe . "'
    AND
        Von='" . $krieg->An . "'
    AND
        Typ=3
    );";
                mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                // Dann wird noch der abschließende NAP eingetragen

                $sql_abfrage = "INSERT INTO
    gruppe_diplomatie
(
    Von,
    An,
    Typ,
    Seit,
    Bis
)
VALUES
(
    '" . $ich->Gruppe . "',
    '" . $krieg->An . "',
    '1',
    '" . time() . "',
    '" . (time() + 604800) . "'
),
(
    '" . $krieg->An . "',
    '" . $ich->Gruppe . "',
    '1',
    '" . time() . "',
    '" . (time() + 604800) . "'
);";
                mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                DisconnectDB();        // Verbindung trennen
                header("location: ../?p=gruppe&m=237");        // Weiterleitung
                // Abbrechen
            } else {
                DisconnectDB();        // Verbindung trennen
                header("location: ../?p=gruppe&m=112");        // Weiterleitung
                // Abbrechen
            }
            die();
        }

        /*
            Wenn der User es bis hierher schafft, darf er keine Diplomatischen Sachen regeln,
            also Verbindung mit DB trennen, Weiterleitung und abbrechen
        */
        DisconnectDB();        // Verbindung trennen
        header("location: ../?p=gruppe&m=112");        // Weiterleitung
        // Abbrechen
        break;
    default:    // Was will der User überhaupt? Ham'ma nicht :D
        DisconnectDB();            // Verbindung trennen
        header("location: ../?p=gruppe&m=112");        // Weiterleitung        // Abbrechen
}
