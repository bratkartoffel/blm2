<?php
include("../include/functions.inc.php");
include("../include/captcha_class/captcha.php");
include("../include/database.class.php");

$name = getOrDefault($_POST, 'name');
$email = getOrDefault($_POST, 'email');
$pwd1 = getOrDefault($_POST, 'pwd1');
$pwd2 = getOrDefault($_POST, 'pwd2');
$captcha_code = getOrDefault($_POST, 'captcha_code');
$captcha_bild = getOrDefault($_POST, 'captcha_bild');

if (!Captcha::Ueberpruefen($captcha_code, $captcha_bild)) {
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 130);
}

if ($pwd1 != $pwd2) {
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 105);
}

if (empty($name) || empty($pwd1)) {
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 104);
}

if (Database::getInstance()->existsPlayerByNameOrEmail($name, $email)) {
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 106);
}

Database::getInstance()->begin();
$email_activation_code = createRandomCode();

$created = Database::getInstance()->createTableEntry('mitglieder', array(
    'Name' => $name,
    'EMail' => $email,
    'EMailAct' => $email_activation_code,
    'Passwort' => sha1($pwd1),
    'Geld' => $Start["geld"]
));

if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

$id = Database::getInstance()->lastInsertId();

$created = Database::getInstance()->createTableEntry('punkte', array('ID' => $id));
if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

$created = Database::getInstance()->createTableEntry('statistik', array('ID' => $id));
if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

$gebaeude_values = array('ID' => $id);
for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
    $gebaeude_values['Gebaeude' . $i] = $Start['gebaeude'][$i];
}
$created = Database::getInstance()->createTableEntry('gebaeude', $gebaeude_values);
if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

$forschung_values = array('ID' => $id);
for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
    $forschung_values['Forschung' . $i] = $Start['forschung'][$i];
}
$created = Database::getInstance()->createTableEntry('forschung', $forschung_values);
if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

$lager_values = array('ID' => $id);
for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
    $lager_values['Lager' . $i] = $Start['lager'][$i];
}
$created = Database::getInstance()->createTableEntry('lagerhaus', $lager_values);
if ($created == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('../?p=registrieren&name=%s&email=%s', $name, $email), 141);
}

// user created successfully, commit and try to send mail
Database::getInstance()->commit();

$email_activation_link = SERVER_PFAD . '/actions/activate.php?user=' . urlencode($name) . '&amp;code=' . $email_activation_code;
if (!sendMail($email, 'Bioladenmanager 2: Aktivierung Ihres Accounts',
    '<html lang="de"><body><h3>Willkommen beim Bioladenmanager 2,</h3>
    <p>Doch bevor Sie Ihr eigenes Imperium aufbauen können, müssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:</p>
    <p><a href="' . $email_activation_link . '">' . $email_activation_link . '</a></p>
    <p>Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an: ' . ADMIN_EMAIL . '</p>
    Grüsse ' . SPIEL_BETREIBER . '</body></html>'
)) {
    redirectTo(sprintf('../?p=anmelden&name=%s', $name), 144);
}

redirectTo(sprintf('../?p=anmelden&name=%s', $name), 201);
