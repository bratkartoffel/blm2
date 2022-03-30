<?php
/**
 * Dient zur Anmeldung des Benutzers
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/database.class.php");

ConnectDB();        // Verbindung mit der Datenbank aufbauen

$name = trim($_POST['name']);        // Der Name des Users
$pwd = $_POST['pwd'];            // Das Passwort

if (CheckGameLock() && !IstAdmin(true, $name) && !IstBetaTester(true, $name)) {
    DisconnectDB();
    header("location: ../?p=anmelden");
    die();
}

$sql_abfrage = "SELECT
    ID,
    Admin,
    Betatester,
    EMailAct,
    Gesperrt
FROM
    mitglieder
WHERE
    Name='" . mysql_real_escape_string($name) . "'
AND
    Passwort='" . sha1($pwd) . "';";
$sql_ergebnis = mysql_query($sql_abfrage);        // Ruft die Daten des Users ab (Vorbereitung)
$_SESSION['blm_queries']++;

$temp = mysql_fetch_object($sql_ergebnis);        // Ruft die Infos des Users ab, mir ist kein anderer Name für die Variable eingefallen ;)

if (mysql_num_rows($sql_ergebnis) == 0) {        // Der User konnte nicht gefunden werden, oder Passwort ist falsch!
    $sql_abfrage = "SELECT
    m.ID
FROM
    mitglieder m JOIN sitter s ON m.ID=s.ID
WHERE
    m.Name='" . mysql_real_escape_string($name) . "'
AND
    s.Passwort='" . sha1($pwd) . "'
AND
    m.Gesperrt = 0;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft die Daten des Users ab (Vorbereitung)  --- SITTER-LOGIN
    $_SESSION['blm_queries']++;

    $temp = mysql_fetch_object($sql_ergebnis);        // Ruft die Infos des Users ab, mir ist kein anderer Name für die Variable eingefallen ;)

    if ($temp->ID > 0) {            // Wenn ein gültiger Sitter-Login vorliegt,
        $_SESSION['blm_user'] = $temp->ID;                                //
        $_SESSION['blm_admin'] = 0;                                                // Die Sessionvariablen deklarieren und Werte zuweisen
        $_SESSION['blm_betatester'] = 0;                                    //
        $_SESSION['blm_login'] = time();                                    //
        $_SESSION['blm_sitter'] = true;                                        // Wir haben hier einen Sitter!

        $sql_abfrage = "INSERT INTO
    log_login
(
    IP,
    Wer,
    Wann,
    Sitter
)
VALUES
(
    '" . $_SERVER['REMOTE_ADDR'] . "',
    '" . $temp->ID . "',
    NOW(),
    '1'
);";
        mysql_query($sql_abfrage);            // Ab ins Logbuch damit
        $_SESSION['blm_queries']++;

        // Fertisch :)
        DisconnectDB();
        header("location: ../?p=index&m=202");
        die();
    }
    DisconnectDB();
    header("location: ../?p=anmelden&m=108");
    die();
}

if ($temp->EMailAct != "") {
    DisconnectDB();
    header("location: ../?p=anmelden&m=135");
    die();
}

if ($temp->Gesperrt == "1") {        // Der Benutzer wurde gesperrt
    DisconnectDB();
    header("location: ../?p=anmelden&m=139");
    die();
}

$_SESSION['blm_user'] = $temp->ID;                                //
$_SESSION['blm_admin'] = $temp->Admin;                        // Die Sessionvariablen deklarieren und Werte zuweisen
$_SESSION['blm_betatester'] = $temp->Betatester;    //
$_SESSION['blm_login'] = time();                                    //
$_SESSION['blm_sitter'] = false;                                    //

$sql_abfrage = "UPDATE
    mitglieder
SET
    LastAction='" . time() . "',
    LastLogin='" . time() . "'
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage);            // Die Zeit des letzten Logins und der letzten Aktion anpassen (Jetzt?)
$_SESSION['blm_queries']++;

$sql_abfrage = "INSERT INTO
    log_login
(
    IP,
    Wer,
    Wann,
    Sitter
)
VALUES
(
    '" . $_SERVER['REMOTE_ADDR'] . "',
    '" . $_SESSION['blm_user'] . "',
    NOW(),
    '0'
);";
mysql_query($sql_abfrage);            // Ab ins Logbuch damit
$_SESSION['blm_queries']++;

mysql_query("DELETE FROM nachrichten WHERE Von=An;");
$_SESSION['blm_queries']++;

// Fertisch :)
DisconnectDB();
header("location: ../?p=index&m=202");
