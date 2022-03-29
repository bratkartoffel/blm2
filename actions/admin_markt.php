<?php
/**
 * Führt die Aktionen des Admins auf dem Markt aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!istAdmin()) {        // Nur wer Admin ist, darf auf dem Marktplatz werkeln ;=)
    header("location: ../?p=index&m=102");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

switch (intval($_REQUEST['a'])) {        // Was will der Benutzer auf dem Marktplatz?
    case 1:        // Verkaufen
        if ($_POST['menge'] <= 0 || number_format(str_replace(",", ".", $_POST['preis']), 2) < 0) {        // Wurde keine Menge oder ein Preis kleiner als 0 € eingegeben? Darf er nicht:
            DisconnectDB();
            header("location: ../?p=admin_markt_einstellen&m=120&" . intval(time()));
            die();
        }

        $sql_abfrage = "INSERT INTO
    marktplatz
VALUES
(
    NULL,
    '0',
    '" . intval($_POST['was']) . "',
    '" . intval($_POST['menge']) . "',
    '" . number_format(str_replace(",", ".", $_POST['preis']), 2) . "'
);";
        mysql_query($sql_abfrage);        // Angebot auf dem Markt stellen
        $_SESSION['blm_queries']++;

        // Angebot drinnen, fertig
        DisconnectDB();
        header("location: ../?p=admin_markt&m=218&" . intval(time()));
        die();
        break;
    case 2:        // Bearbeiten
        if ($_POST['menge'] <= 0 || number_format(str_replace(",", ".", $_POST['preis']), 2) < 0) {        // Wurde keine Menge oder ein Preis kleiner als 0 € eingegeben? Darf er nicht:
            DisconnectDB();
            header("location: ../?p=admin_markt_bearbeiten&m=120&" . intval(time()) . "&id=" . intval($_POST['id']));
            die();
        }

        $sql_abfrage = "UPDATE
    marktplatz
SET
    Was='" . intval($_POST['was']) . "',
    Menge='" . intval($_POST['menge']) . "',
    Preis='" . number_format(str_replace(",", ".", $_POST['preis']), 2) . "'
WHERE
    ID='" . intval($_POST['id']) . "';";
        mysql_query($sql_abfrage);        // Angebot auf dem Markt stellen
        $_SESSION['blm_queries']++;

        DisconnectDB();
        header("location: ../?p=admin_markt&m=234&" . intval(time()));
        die();
        break;
    case 3:            // Löschen
        $sql_abfrage = "DELETE FROM
    marktplatz
WHERE
    ID='" . intval($_GET['id']) . "';";
        mysql_query($sql_abfrage);        // Das Angebot ist schon verkauft, also vom Markt nehmen
        $_SESSION['blm_queries']++;

        // Fertig :)
        DisconnectDB();
        header("location: ../?p=admin_markt&m=233&" . intval(time()));
        die();
        break;
}
