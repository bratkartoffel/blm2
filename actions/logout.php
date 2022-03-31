<?php
/**
 * Dient zum Abmelden des Benutzers
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

if (!$_SESSION['blm_sitter']) {
    $sql_abfrage = "UPDATE
    mitglieder
SET
    LastAction='" . (time() - 300) . "',
    OnlineZeit=OnlineZeit+" . (time() - $_SESSION['blm_login']) . "
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
    mysql_query($sql_abfrage);        // Die letzte Aktion in die Vergangenheit legen, damit der Benutzer auch sofort als OFFLINE angezeigt wird. (TODO: Workaround? Einfacher?)
    $_SESSION['blm_queries']++;
}

DisconnectDB();        // Die Datenbank brauchen wir jetzt nicht mehr...

unset($_SESSION['blm_user']);        // Die Sessionvariable löschen
unset($_SESSION['blm_sitter']);

if (isset($_GET['popup'])) {        // Das Script wurde vom Popup aufgerufen, das heisst, das Popup soll geschlossen werden
    echo '<script type="text/javascript">self.close();</script>';
    die();
}

if (isset($_GET['del']) && intval($_GET['del']) > 0) {        // Hat sich der Benutzer gelöscht?
    header("location: ../?p=anmelden&m=205");        // Ja: Bye bye...
} else {
    header("location: ../?p=anmelden&m=203");        // Nein: Bis bald :)
}
