<?php
/**
 * Führt die Aktionen des Benutzers auf dem Marktplatz aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Nur wer angemeldet ist, darf auf dem Marktplatz werkeln ;=)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen
$ich = LoadSettings();                                // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Marktplatz && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=amrktplatz&m=112&" . time());
    die();
}

switch (intval($_REQUEST['a'])) {        // Was will der Benutzer auf dem Marktplatz?
    case 1:        // Verkaufen
        $temp = "Lager" . intval($_POST['was']);        // Temporöre Variable mit dem MySQL-Spaltenname mit der Ware

        if ($ich->$temp < intval($_POST['menge'])) {        // Will der Benutzer mehr verkaufen als er hat? Abbruch
            DisconnectDB();
            header("location: ../?p=marktplatz_verkaufen&m=116&" . time());
            die();
        }

        if ($_POST['menge'] <= 0 || number_format(str_replace(",", ".", $_POST['preis']), 2) < 1) {        // Wurde keine Menge oder ein Preis kleiner als 1 € eingegeben? Darf er nicht:
            DisconnectDB();
            header("location: ../?p=marktplatz_verkaufen&m=120");
            die();
        }

        $sql_abfrage = "INSERT INTO
    marktplatz
VALUES
(
    NULL,
    '" . $_SESSION['blm_user'] . "',
    '" . intval($_POST['was']) . "',
    '" . intval($_POST['menge']) . "',
    '" . number_format(str_replace(",", ".", $_POST['preis']), 2) . "'
);";
        mysql_query($sql_abfrage);        // Angebot auf dem Markt stellen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    lagerhaus
SET
    " . $temp . "=" . $temp . "-" . intval($_POST['menge']) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Die Waren aus dem Lager nehmen, da diese ja jetzt auf dem Marktplatz liegen
        $_SESSION['blm_queries']++;

        // Angebot drinnen, fertig
        DisconnectDB();
        header("location: ../?p=marktplatz_liste&m=218&" . time());
        die();

    case 2:        // Kaufen
        $filter = $_GET['w'];
        $url_string = implode("&w[]=", $filter);
        $url_string = "&w[]=" . $filter[0] . substr($url_string, 1);

        $sql_abfrage = "SELECT
    *
FROM
    marktplatz
WHERE
    ID='" . intval($_GET['id']) . "'
AND
    Von!='" . $_SESSION['blm_user'] . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $angebot = mysql_fetch_object($sql_ergebnis);        // Alle Infos zu dem angefragten Gebot holen

        if (intval($angebot->ID) == 0) {    // Wurde das Angebot überhaupt gefunden / War jemand schneller? Wenn ja:
            DisconnectDB();
            header("location: ../?p=marktplatz_liste&m=119" . $url_string . "&" . time());
            die();
        }

        if ($ich->Geld < ($angebot->Menge * $angebot->Preis)) {        // Kann sich der Benutzer das überhaupt leisten? Wenn nicht, dann:
            DisconnectDB();
            header("location: ../?p=marktplatz_liste&m=111" . $url_string . "&" . time());
            die();
        }

        $sql_abfrage = "UPDATE
    (lagerhaus l NATURAL JOIN mitglieder m) NATURAL JOIN statistik s
SET
    l.Lager" . $angebot->Was . "=l.Lager" . $angebot->Was . "+" . $angebot->Menge . ",
    m.Geld=m.Geld-" . ($angebot->Menge * $angebot->Preis) . ",
    s.AusgabenMarkt=s.AusgabenMarkt+" . ($angebot->Menge * $angebot->Preis) . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Die Waren ins Lager stecken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld+" . (($angebot->Menge * $angebot->Preis) * MARKT_PROVISION_FAKTOR) . ",
    s.EinnahmenMarkt=s.EinnahmenMarkt+" . (($angebot->Menge * $angebot->Preis) * MARKT_PROVISION_FAKTOR) . "
WHERE
    m.ID='" . $angebot->Von . "';";
        mysql_query($sql_abfrage);        // Der Verkäufer kriegt nun sein Geld, jedoch abzüglich 1 % Marktprovision!
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    marktplatz
WHERE
    ID='" . $angebot->ID . "';";
        mysql_query($sql_abfrage);        // Das Angebot ist schon verkauft, also vom Markt nehmen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Nachricht,
    Betreff,
    Zeit,
    Gelesen
)
VALUES
(
    NULL,
    '0',
    '" . $angebot->Von . "',
    'Soeben wurde ein Angebot von Ihnen auf dem freien Markt gekauft:\n" . $angebot->Menge . "kg " . WarenName($angebot->Was) . " zu insgesamt " . number_format(($angebot->Menge * $angebot->Preis) * MARKT_PROVISION_FAKTOR, 2, ",", ".") . " " . $CurrencyC . ".\n\n[i]- System -[/i]',
    'Freier Markt',
    '" . time() . "',
    '0'
);";
        mysql_query($sql_abfrage);        // Dem Verkäufer ne Mitteilung machen, dass sein Angebot gekauft wurde.
        $_SESSION['blm_queries']++;

        // Fertig :)
        DisconnectDB();
        header("location: ../?p=marktplatz_liste&m=217" . $url_string . "&" . time());
        die();
    case 3:            // Zurückziehen
        $filter = $_GET['w'];
        $url_string = implode("&w[]=", $filter);
        $url_string = "&w[]=" . $filter[0] . substr($url_string, 1);

        $sql_abfrage = "SELECT
    *
FROM
    marktplatz
WHERE
    ID='" . intval($_GET['id']) . "'
AND
    Von='" . $_SESSION['blm_user'] . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $angebot = mysql_fetch_object($sql_ergebnis);        // Alle Infos zu dem angefragten Gebot holen

        $sql_abfrage = "UPDATE
    lagerhaus
SET
    Lager" . $angebot->Was . "=Lager" . $angebot->Was . "+" . intval($angebot->Menge * MARKT_ZURUECKZIEH_FAKTOR) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
        mysql_query($sql_abfrage);        // Die Waren ins Lager stecken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    marktplatz
WHERE
    ID='" . $angebot->ID . "';";
        mysql_query($sql_abfrage);        // Das Angebot ist schon verkauft, also vom Markt nehmen
        $_SESSION['blm_queries']++;

        $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Nachricht,
    Betreff,
    Zeit,
    Gelesen
)
VALUES
(
    NULL,
    '0',
    '" . $angebot->Von . "',
    'Sie haben soeben folgendes Angebot vom Markt zurückgezogen:\n" . $angebot->Menge . "kg " . WarenName($angebot->Was) . " zu insgesamt " . ($angebot->Menge * $angebot->Preis) . " " . $CurrencyC . ".\nDa das Angebot schon eine Weile dort gelegen ist, sind Ihnen während des Rücktransports 10% vertrocknet. Die restlichen Waren finden Sie in Ihrem Lager.\n\n[i]- System -[/i]',
    'Freier Markt',
    '" . time() . "',
    '0'
);";
        mysql_query($sql_abfrage);        // Dem Verkäufer ne Mitteilung machen, dass sein Angebot gekauft wurde.
        $_SESSION['blm_queries']++;

        // Fertig :)
        DisconnectDB();
        header("location: ../?p=marktplatz_liste&m=221" . $url_string . "&" . time());
        die();
}
