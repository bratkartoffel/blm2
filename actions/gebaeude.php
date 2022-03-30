<?php
/**
 * Führt die Aktionen des Benutzers zum Bauen von Gebäuden aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wenn der Client nicht angemeldet ist, darf er auch keine Gebäude bauen :)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();    // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Gebaeude && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}
include("../include/kosten_dauer.inc.php");        // Dann die Gebäude-Ausbau-kosten und -dauern abrufen...

switch (intval($_POST['was']))        // Was will er denn bauen?
{
    case 1:
        $gebaeude_kosten = $Plantage->Kosten;        // Ja nachdem, was er bauen will, setzen wir die
        $gebaeude_dauer = $Plantage->Dauer;            // entprechenden Variablen mit den Kosten, der Dauer
        $gebaeude_punkte = $Plantage->Punkte;        // und den Punkten für den Ausbau
        break;
    case 2:
        $gebaeude_kosten = $Forschungszentrum->Kosten;
        $gebaeude_dauer = $Forschungszentrum->Dauer;
        $gebaeude_punkte = $Forschungszentrum->Punkte;
        break;
    case 3:
        $gebaeude_kosten = $Bioladen->Kosten;
        $gebaeude_dauer = $Bioladen->Dauer;
        $gebaeude_punkte = $Bioladen->Punkte;
        break;
    case 4:
        $gebaeude_kosten = $Doenerstand->Kosten;
        $gebaeude_dauer = $Doenerstand->Dauer;
        $gebaeude_punkte = $Doenerstand->Punkte;
        break;
    case 5:
        $gebaeude_kosten = $Bauhof->Kosten;
        $gebaeude_dauer = $Bauhof->Dauer;
        $gebaeude_punkte = $Bauhof->Punkte;
        break;
    case 6:
        $gebaeude_kosten = $Schule->Kosten;
        $gebaeude_dauer = $Schule->Dauer;
        $gebaeude_punkte = $Schule->Punkte;
        break;
    case 7:
        $gebaeude_kosten = $Zaun->Kosten;
        $gebaeude_dauer = $Zaun->Dauer;
        $gebaeude_punkte = $Zaun->Punkte;
        break;
    case 8:
        $gebaeude_kosten = $Pizzeria->Kosten;
        $gebaeude_dauer = $Pizzeria->Dauer;
        $gebaeude_punkte = $Pizzeria->Punkte;
        break;
    default:        // Haben wir nicht, gibts nicht, also abbrechen
        DisconnectDB();
        header("location: ../?p=gebaeude&m=112");
        die();
}

if ($ich->Geld < $gebaeude_kosten) {        // Kann sich der Benutzer das überhaupt leisten? Wenn nicht, dann Abbruch!
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}

if ($ich->Gebaeude3 < 5 && intval($_POST['was']) == 4) {        // Darf er überhaupt den Dönerstand bauen? (Vorraussetzungen)
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}

if ($ich->Gebaeude1 < 5 && intval($_POST['was']) == 6) {        // Darf er überhaupt eine Schule bauen? (Vorraussetzungen)
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}

if (($ich->Gebaeude1 < 8 || $ich->Gebaeude2 < 9) && intval($_POST['was']) == 5) {        // Darf er überhaupt den Bauhof bauen? (Vorraussetzungen)
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}

if (($ich->AusgabenMafia < 10000 || $ich->Gebaeude1 < 10) && intval($_POST['was'] == 7)) { // Darf er überhaupt schon nen Zaun bauen?
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
    die();
}

if (($ich->AusgabenMafia < 25000 || $ich->Gebaeude1 < 12) && intval($_POST['was'] == 8)) { // Darf er überhaupt schon nen Zaun bauen?
    DisconnectDB();
    header("location: ../?p=gebaeude&m=112");
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
    '" . (100 + intval($_POST['was'])) . "',
    '" . $_SESSION['blm_user'] . "',
    '" . $gebaeude_kosten . "',
    '" . $gebaeude_dauer . "',
    '" . time() . "',
    NULL,
    '" . $gebaeude_punkte . "'
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
    m.Geld=m.Geld-" . $gebaeude_kosten . ",
    s.AusgabenGebaeude=s.AusgabenGebaeude+" . $gebaeude_kosten . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);    // Falls der Auftrag noch nicht erteilt wurde, das Geld abziehen, und die Ausgaben für Gebäude anpassen
$_SESSION['blm_queries']++;

// Alles erledigt :)
header("location: ../?p=gebaeude&m=207#g" . intval($_POST['was']));
