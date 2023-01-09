<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');
require_once('../include/captcha.class.php');

ob_start();

if (registration_closed) {
    redirectTo('/?p=anmelden', 148);
}

$name = getOrDefault($_POST, 'name');
$email = getOrDefault($_POST, 'email');
$pwd1 = getOrDefault($_POST, 'pwd1');
$pwd2 = getOrDefault($_POST, 'pwd2');
$captcha_code = getOrDefault($_POST, 'captcha_code');
$captcha_id = getOrDefault($_POST, 'captcha_id', 0);

// remove all control characters and trim spaces
// https://stackoverflow.com/a/66587087
$name = trim(preg_replace('/[^\PCc^\PCn^\PCs]/u', '', $name));

$backLink = sprintf('/?p=registrieren&name=%s&email=%s', urlencode($name), urlencode($email));
if (!is_testing && !Captcha::verifyCode($captcha_code, $captcha_id)) {
    redirectTo($backLink, 130, __LINE__);
}

if ($pwd1 != $pwd2) {
    redirectTo($backLink, 105, __LINE__);
}

if (empty($name) || empty($pwd1)) {
    redirectTo($backLink, 104, __LINE__);
}

if (strlen($name) < username_min_len || strlen($name) > username_max_len) {
    redirectTo($backLink, 146, __LINE__);
}

if (strlen($pwd1) < password_min_len) {
    redirectTo($backLink, 147, __LINE__);
}

if (strchr($name, '#') !== false) {
    redirectTo($backLink, 164, __LINE__);
}

if (Database::getInstance()->existsPlayerByNameOrEmail($name, $email)) {
    redirectTo($backLink, 106, __LINE__);
}

$email_activation_code = createRandomCode();

$id = null;
Database::getInstance()->begin();
foreach (starting_values as $table => $values) {
    if ($id !== null) $values['user_id'] = $id;
    if ($table == Database::TABLE_USERS) {
        $values['Name'] = $name;
        $values['EMail'] = $email;
        $values['EMailAct'] = $email_activation_code;
        $values['Passwort'] = hashPassword($pwd1);
    }
    if (Database::getInstance()->createTableEntry($table, $values) === null) {
        Database::getInstance()->rollBack();
        redirectTo($backLink, 141, __LINE__ . '_' . $table);
    }
    if ($table == Database::TABLE_USERS) $id = Database::getInstance()->lastInsertId();
}
Database::getInstance()->commit();

$email_activation_link = base_url . '/actions/activate.php?user=' . urlencode($name) . '&amp;code=' . $email_activation_code;
if (!sendMail($email, game_title . ': Aktivierung Ihres Accounts',
    '<html lang="de"><body><h3>Hallo ' . escapeForOutput($name) . ' und Willkommen beim Bioladenmanager 2,</h3>
    <p>Doch bevor Sie Ihr eigenes Imperium aufbauen können, müssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:</p>
    <p><a href="' . $email_activation_link . '">' . $email_activation_link . '</a></p>
    <p>Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an: ' . admin_email . '</p>
    Grüsse ' . admin_name . '</body></html>'
)) {
    redirectTo(sprintf('/?p=anmelden&name=%s', $name), 144, __LINE__);
}

redirectTo(sprintf('/?p=anmelden&name=%s', $name), 201);
