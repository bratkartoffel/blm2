<?php
/**
 * Seite für den Admin, alle Acccounts manuell zu resetten
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/database.class.php");

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if (!IstAdmin()) {        // Wenn der Benutzer kein Administrator ist, dann abbrechen
    DisconnectDB();
    header("location: ../?p=index&m=102");
    die();
}

ResetAll(false, $Start);        // Alle Accounts zurücksetzen

DisconnectDB();                            // Die Verbindung mit der Datenbank wieder trennen
header("location: ../?p=login");        // Auf die Einloggenseite weiterleiten
