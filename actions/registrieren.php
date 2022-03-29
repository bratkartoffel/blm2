<?php
/**
 * Registriert einen neuen Benutzer
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/captcha_class/captcha.php");            // bindet die Funktionen ein

ConnectDB();        // Verbindung mit der Datenbank aufbauen

$name = $_POST['name'];    // Den gewünschten Namen abrufen
$email = $_POST['email'];    // Den gewünschten Namen abrufen
$pwd1 = $_POST['pwd1'];        //
$pwd2 = $_POST['pwd2'];        // Beinhaltet die gewünschten Passwörter / Das Passwort und die Bestätigung
$captcha = $_POST['captcha_code'];

if (!Captcha::Ueberpruefen($captcha, $_POST['bild'])) {    // Sobald ein falscher Sicherheitscode eingegeben wurde, abbrehcen!
    DisconnectDB();
    header("location: ../?p=registrieren&m=130");
    die();
}

if ($name == "" || $pwd1 == "" || $pwd2 == "") {    // Sobald ein Feld nicht angegeben wurde, wird abgebrochen!
    DisconnectDB();
    header("location: ../?p=registrieren&m=104");
    die();
}

if ($pwd1 != $pwd2) {        // Der Benutzer hat 2 verschiedene Passwörter eingegeben, abbrechen
    DisconnectDB();
    header("location: ../?p=registrieren&m=105");
    die();
}

if (!CheckEMail($email)) {
    DisconnectDB();
    header("location: ../?p=registrieren&m=134");
    die();
}

if (!Registrieren($Start, $name, $pwd1, $email)) {        // Ist die Registrierung fehlgeschlagen? Wenn ja, dann zurück zur Registrierung und Meldung anzeigen
    DisconnectDB();
    header("location: ../?p=registrieren&m=106");
    die();
}

// Fertig :)
DisconnectDB();
header("location: ../?p=anmelden&m=201");
die();
