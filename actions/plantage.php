<?php
/**
 * Führt die Aktionen des Benutzers auf seiner Plantage aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wer nicht angemeldet ist, kann auch nichts anbauen...
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();                                // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)
$ich->Sitter = LoadSitterSettings();

if (!$ich->Sitter->Produktion && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=plantage&m=112&" . time());
    die();
}

if ($_POST['alles'] == "1") {
    // Hier wird alles produziert
    $dauer = intval($_POST['stunden']);        // Wie lange soll produziert werden? (Stunden, nur ganzzahlig)

    if ($dauer < 1 || $dauer > 12) {        // Ungültige Dauer angegeben
        DisconnectDB();
        header("location: ../?p=plantage&m=133&" . intval(time()));
        die();
    }

    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Alle Waren durchgehen
        $temp = "Forschung" . $i;
        if ($ich->$temp == 0 || $ich->Gebaeude1 < intval($i * 1.5)) {        // Darf der Benutzer die Ware noch nciht anbauen,
            continue;        // Dann wird weitergesprungen (i um 1 erhöht, Schleife erneut)
        }

        $produktion_menge = $dauer * (($ich->Gebaeude1 * PRODUKTIONS_PLANTAGE_FAKTOR_MENGE) + PRODUKTIONS_WAREN_FAKTOR_MENGE * $i + $Produktion->BasisMenge + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE));            // Wieviel wird produziert?
        $produktion_kosten = $dauer * ($Produktion->BasisKosten + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN));        // Was kostet die Produktion?
        $kosten_gesamt += $produktion_kosten;        // Wieviel kostet der Anbau gesamt?

        $sql[] = "(
								NULL, 
								'" . (200 + $i) . "', 
								'" . $_SESSION['blm_user'] . "', 
								'" . time() . "', 
								'" . (3600 * $dauer) . "', 
								'" . $produktion_menge . "',
								'" . $produktion_kosten . "',
								NULL
							)";        // SQL-Teil für diesen Auftrag, wird dann alles in einer Query an die Datenbank geschickt
    }

    if ($ich->Geld < $kosten_gesamt) {        // Kann sich der Benutzer das überhaupt leisten? Wenn nicht, dann abbrechen
        DisconnectDB();
        header("location: ../?p=plantage&m=111&" . intval(time()));
        die();
    }

    $sql_abfrage = "INSERT INTO
    auftrag
(
    ID,
    Was,
    Von,
    Start,
    Dauer,
    Menge,
    Kosten,
    Punkte
)
VALUES
" . implode(",", $sql) . ";";

    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    if (mysql_errno() > 0) {        // Mindestens 1 Auftrag wurde bereits in die Datenbank übernommen
        DisconnectDB();
        header("location: ../?p=plantage&m=113&" . intval(time()));
        die();
    }

    $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld-" . $kosten_gesamt . ",
    s.AusgabenProduktion=s.AusgabenProduktion+" . $kosten_gesamt . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
    mysql_query($sql_abfrage);        // Die Produktion bezahlen und die Ausgaben für Produktion anpassen
    $_SESSION['blm_queries']++;

    // Fertig, weiter machen
    DisconnectDB();
    header("location: ../?p=plantage&m=207&" . intval(time()));
    die();
}

$temp = "Forschung" . intval($_POST['was']);

$produktion_dauer = $Produktion->BasisDauer;        // Wielange dauert die Produktion?
$produktion_menge = ($ich->Gebaeude1 * PRODUKTIONS_PLANTAGE_FAKTOR_MENGE) + PRODUKTIONS_WAREN_FAKTOR_MENGE * intval($_POST['was']) + $Produktion->BasisMenge + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE);            // Wieviel wird produziert?
$produktion_kosten = $Produktion->BasisKosten + ($ich->Forschung1 * PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN);        // Was kostet die Produktion?

$produktion_pro_stunde = intval($produktion_menge / date("H", $produktion_dauer - 3600));
$kosten_pro_kg = round($produktion_kosten / $produktion_menge, 4);

$menge = intval($_POST['menge']);

$produktion_dauer = ($menge / $produktion_pro_stunde * 3600);
$produktion_menge = $menge;
$produktion_kosten = $menge * $kosten_pro_kg;

if ($produktion_menge > $produktion_pro_stunde * 12 || $produktion_menge <= 0) {        // Wurde eine falsche Menge eingegeben, entweder unter 0 oder wenn die Produktion länger als 12 Stunden dauern würde, dann brich ab.
    DisconnectDB();
    header("location: ../?p=plantage&m=125&" . intval(time()));
    die();
}
if ($_POST['was'] <= 0 || $_POST['was'] > ANZAHL_WAREN) {        // der User will was anbauen, was es nicht gibt!
    DisconnectDB();
    header("location: ../?p=plantage&m=112&" . intval(time()));
    die();
}

if ($ich->Geld < $produktion_kosten) {        // Kann sich der Benutzer das überhaupt leisten? Wenn nicht, dann abbrechen
    DisconnectDB();
    header("location: ../?p=plantage&m=111&" . intval(time()));
    die();
}

if ($ich->$temp == 0 || $ich->Gebaeude1 < intval($_POST['was'] * 1.5)) {    // Hat der Benutzer die Pflanze überhaupt schon erforscht? Wenn nicht, dann Abbruch!
    DisconnectDB();
    header("location: ../?p=plantage&m=112&" . intval(time()));
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
    '" . (200 + intval($_POST['was'])) . "',
    '" . $_SESSION['blm_user'] . "',
    '" . $produktion_kosten . "',
    '" . $produktion_dauer . "',
    '" . time() . "',
    '" . $produktion_menge . "',
    NULL
);";
mysql_query($sql_abfrage);        // Den Auftrag in die Datenbank schreiben
$_SESSION['blm_queries']++;

if (mysql_errno() > 0) {        // Der Auftrag war bereits vorhanden! Doppelauftrag? Ohne uns! Zurück und Meldung ausgeben!
    DisconnectDB();
    header("location: ../?p=plantage&m=113&" . intval(time()));
    die();
}

$sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld-" . $produktion_kosten . ",
    s.AusgabenProduktion=s.AusgabenProduktion+" . $produktion_kosten . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);        // Die Produktion bezahlen und die Ausgaben für Produktion anpassen
$_SESSION['blm_queries']++;

// Alles erledigt :)
DisconnectDB();
header("location: ../?p=plantage&m=207&" . intval(time()) . "#p" . intval($_POST['was']));
die();
