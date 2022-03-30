<?php
/**
 * Führt die Aktionen des Benutzers bei Verträgen aus
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/preise.inc.php");

if (!IstAngemeldet()) {        // Wer nicht angemeldet ist, kann auch keine Verträge ausmachen!
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();                                // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)
$ajax = intval($_GET['ajax']);

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Vertraege && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=vertraege&m=112");
    die();
}

switch (intval($_REQUEST['a'])) {    // Was will der überhaupt?
    case 1:        // Neuen Vertrag verschicken
        $an = intval($_POST['an']);                // Empfänger des Vertrags
        $ware = intval($_POST['ware']);        // Was will er verschicken?
        $menge = intval($_POST['menge']);    // Wieviel solls sein?
        $_preis = str_replace(",", ".", $_POST['preis']);    // Was verlangt er pro Kilo?
        $temp = "Lager" . $ware;    // In welchem Feld steht der Lagerstand der Ware?

        if ($an <= 0 || $menge <= 0 || $_preis < $Preis[$ware] || $_preis > 3 * $Preis[$ware]) {        // Es wurden ungültige Werte angegeben
            DisconnectDB();
            header("location: ../?p=vertrag_neu&m=117&an=$an&ware=$ware&menge=$menge&preis=$_preis");
            die();
        }

        if ($ich->$temp < $menge) {        // Will der Benutzer mehr verschicken als er auf Lager hat? Abbruch!
            DisconnectDB();
            header("location: ../?p=vertrag_neu&m=116&an=$an&ware=$ware&menge=$menge&preis=$_preis");
            die();
        }

        $datetime = date("Y-m-d H:i:s");

        $sql_abfrage = "INSERT INTO
    vertraege
(
    ID,
    Von,
    An,
    Was,
    Menge,
    Preis,
    Wann
)
VALUES
(
    NULL,
    '" . $_SESSION['blm_user'] . "',
    '" . $an . "',
    '" . $ware . "',
    '" . $menge . "',
    '" . $_preis . "',
    '" . $datetime . "'
);";
        mysql_query($sql_abfrage);        // Den Vertrag in die DB schreiben
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    lagerhaus
SET
    " . $temp . "=" . $temp . "-" . $menge . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Die Waren schon mal aus dem Lager nehmen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    log_vertraege
(
    Wer,
    Wen,
    Wann,
    Was,
    Wieviel,
    Preis,
    Angenommen
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $an . "',
    '" . $datetime . "',
    '" . $ware . "',
    '" . $menge . "',
    '" . $_preis . "',
    '0'
);";
        mysql_query($sql_abfrage);        // Logbuch
        $_SESSION['blm_queries']++;

        // Vertrag ist verschickt, Fertig!
        DisconnectDB();
        header("location: ../?p=vertraege_liste&m=214");
        break;
    case 2:        // Vertrag annehmen
        $id = intval($_GET['vid']);        // Welchen Vertrag will er annehmen?
        $sql_abfrage = "SELECT
    *
FROM
    vertraege
WHERE
    An='" . $_SESSION['blm_user'] . "'
AND
    ID='" . $id . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $vertrag = mysql_fetch_object($sql_ergebnis);    // Die Infos zum angeforderten Vertrag abholen

        if ($ich->Geld < $vertrag->Preis * $vertrag->Menge) {    // Der Benutzer kann sich das gar nicht leisten!
            DisconnectDB();
            if ($ajax == 1) {
                die("111");
            } else {
                header("location: ../?p=vertraege_liste&m=111");
            }
            die();
        }

        $sql_abfrage = "UPDATE
    (mitglieder m NATURAL JOIN lagerhaus l) NATURAL JOIN statistik s
SET
    l.Lager" . $vertrag->Was . "=l.Lager" . $vertrag->Was . "+" . $vertrag->Menge . ",
    m.Geld=m.Geld-" . ($vertrag->Preis * $vertrag->Menge) . ",
    s.AusgabenVertraege=s.AusgabenVertraege+" . ($vertrag->Preis * $vertrag->Menge) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // So, zuerst wird bezahlt!
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld+" . ($vertrag->Preis * $vertrag->Menge * VERTRAEGE_PROVISION_FAKTOR) . ",
    s.EinnahmenVertraege=s.EinnahmenVertraege+" . ($vertrag->Preis * $vertrag->Menge * VERTRAEGE_PROVISION_FAKTOR) . "
WHERE
    m.ID='" . $vertrag->Von . "';";
        mysql_query($sql_abfrage);        // Dann beommt der andere sein Geld
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    vertraege
WHERE
    ID='" . $id . "';";
        mysql_query($sql_abfrage);        // Der Vertrag ist angenommen, also raus damit aus der Datenbank
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Zeit,
    Betreff,
    Nachricht,
    Gelesen
)
VALUES
(
    NULL,
    '0',
    '" . $vertrag->Von . "',
    '" . time() . "',
    'Vertrag angenommen',
    'Hallo,\n\nIhr Vertrag über " . $vertrag->Menge . ' kg ' . WarenName($vertrag->Was) . ' zu insgesamt ' . number_format(VERTRAEGE_PROVISION_FAKTOR * $vertrag->Menge * $vertrag->Preis, 2, ",", ".") . " " . $Currency . " wurde angenommen.\n\n[i]-System-[/i]',
    '0'
);";
        mysql_query($sql_abfrage);        // Noch eine kleine Meldung an den Verkäufer, dass sein Vertrag angenommen wurde.
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    log_vertraege
WHERE
    Wer='" . $vertrag->Von . "'
AND
    Wen='" . $_SESSION['blm_user'] . "'
AND
    Wann='" . $vertrag->Wann . "'
;";
        mysql_query($sql_abfrage);        // Logbuch
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    log_vertraege
(
    Wer,
    Wen,
    Wann,
    Was,
    Wieviel,
    Preis,
    Angenommen
)
VALUES
(
    '" . $vertrag->Von . "',
    '" . $_SESSION['blm_user'] . "',
    '" . $vertrag->Wann . "',
    '" . $vertrag->Was . "',
    '" . $vertrag->Menge . "',
    '" . $vertrag->Preis . "',
    '1'
);";
        mysql_query($sql_abfrage);        // Logbuch
        $_SESSION['blm_queries']++;

        // Fertig :)
        DisconnectDB();
        if ($ajax == 1) {
            die("1");
        } else {
            header("location: ../?p=vertraege_liste&m=215");
        }
        break;
    case 3:        // Vertrag ablehnen
        $id = intval($_GET['vid']);        // Welchen Vertrag will er ablehnen?

        $sql_abfrage = "SELECT
    *
FROM
    vertraege
WHERE
(
    An='" . $_SESSION['blm_user'] . "'
OR
    Von='" . $_SESSION['blm_user'] . "'
)
AND
    ID='" . $id . "'";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $v = mysql_fetch_object($sql_ergebnis);        // da war ich mal wieder Tipfaul... $v steht für $vertraege ;)

        if (intval($v->ID) == 0) {        // Gibts den Vertrag überhaupt? Wenn nicht, dann abbrechen!
            DisconnectDB();
            if ($ajax == 1) {
                die("112");
            } else {
                header("location: ../?p=vertraege_liste&m=112");
            }
            die();
        }

        $sql_abfrage = "UPDATE
    lagerhaus
SET
    Lager" . $v->Was . "=Lager" . $v->Was . "+" . $v->Menge . "
WHERE
    ID='" . $v->Von . "';";
        mysql_query($sql_abfrage);        // Der Verkäufer bekommt seine Waren wieder zurück
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    vertraege
WHERE
    ID='" . $id . "';";
        mysql_query($sql_abfrage);        // Und der Vertrag wird aus der DB gelöscht
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Zeit,
    Betreff,
    Nachricht,
    Gelesen
)
VALUES
(
    NULL,
    '0',
    '" . $v->Von . "',
    '" . time() . "',
    'Vertrag abgelehnt',
    'Hallo,\n\nIhr Vertrag über " . $v->Menge . ' kg ' . WarenName($v->Was) . ' zu insgesamt ' . number_format($v->Menge * $v->Preis, 2, ",", ".") . " " . $Currency . " wurde abgelehnt.\n\n[i]-System-[/i]',
    '0'
);";
        mysql_query($sql_abfrage);        // Dann wird der Absender noch informiert, dass sein Angebot Schrott war, und abgelehnt wurde :)
        $_SESSION['blm_queries']++;


        // Fertig :)
        DisconnectDB();
        if ($ajax == 1) {
            die("1");
        } else {
            header("location: ../?p=vertraege_liste&m=216");
        }
        break;
    default:
        DisconnectDB();
        header("location: ../?p=vertraege_liste");
}
