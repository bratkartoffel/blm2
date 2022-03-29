<?php
/**
 * Wird per AJAX aufgerufen, und überprüft, ob der Benutzername gültig und noch frei ist.
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
include("../include/config.inc.php");                // bindet die Konfigurationsdatei ein
include("../include/functions.inc.php");            // bindet die Funktionen ein

$name = trim($_GET['uname']);

if (strlen($name) < 2) {
    die('0');
}

ConnectDB();

$sql_abfrage = "SELECT
    '1'
FROM
    mitglieder
WHERE
    Name LIKE '" . mysql_real_escape_string($name) . "';";
$sql_ergebnis = mysql_query($sql_abfrage);

if (mysql_num_rows($sql_ergebnis) == 0) {
    die('1');
} else {
    die('2');
}
?>