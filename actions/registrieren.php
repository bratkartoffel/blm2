<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');
require_once('../include/captcha.class.php');

ob_start();

if (Config::getBoolean(Config::SECTION_BASE, 'registration_closed')) {
    redirectTo('/?p=anmelden', 148);
}

$name = trimAndRemoveControlChars(getOrDefault($_POST, 'name'));
$email = getOrDefault($_POST, 'email');
$pwd1 = getOrDefault($_POST, 'pwd1');
$pwd2 = getOrDefault($_POST, 'pwd2');
$captcha_code = getOrDefault($_POST, 'captcha_code');
$captcha_id = getOrDefault($_POST, 'captcha_id', 0);

$backLink = sprintf('/?p=registrieren&name=%s&email=%s', urlencode($name), urlencode($email));
if (!Config::getBoolean(Config::SECTION_BASE, 'testing') && !Captcha::verifyCode($captcha_code, $captcha_id)) {
    redirectTo($backLink, 130, __LINE__);
}

if ($pwd1 != $pwd2) {
    redirectTo($backLink, 105, __LINE__);
}

if (empty($name) || empty($pwd1)) {
    redirectTo($backLink, 104, __LINE__);
}

if (strlen($name) < Config::getInt(Config::SECTION_BASE, 'username_min_len') || strlen($name) > Config::getInt(Config::SECTION_BASE, 'username_max_len')) {
    redirectTo($backLink, 146, __LINE__);
}

if (strlen($pwd1) < Config::getInt(Config::SECTION_BASE, 'password_min_len')) {
    redirectTo($backLink, 147, __LINE__);
}

if (strchr($name, '#') !== false) {
    redirectTo($backLink, 164, __LINE__);
}

if (Database::getInstance()->existsPlayerByNameOrEmail($name, $email)) {
    redirectTo($backLink, 106, __LINE__);
}

$email_activation_code = createRandomCode();

Database::getInstance()->begin();
if (!Database::getInstance()->createUser($name, $email, $email_activation_code, $pwd1)) {
    Database::getInstance()->rollBack();
    redirectTo($backLink, 141, __LINE__);
}
Database::getInstance()->commit();

$email_activation_link = Config::get(Config::SECTION_BASE, 'base_url') . '/actions/activate.php?user=' . urlencode($name) . '&amp;code=' . $email_activation_code;
if (!sendMail($email, Config::get(Config::SECTION_BASE, 'game_title') . ': Aktivierung Ihres Accounts',
    '<html lang="de"><body><h3>Hallo ' . escapeForOutput($name) . ' und Willkommen beim Bioladenmanager 2,</h3>
    <p>Doch bevor Sie Ihr eigenes Imperium aufbauen können, müssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:</p>
    <p><a href="' . $email_activation_link . '">' . $email_activation_link . '</a></p>
    <p>Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an: ' . Config::get(Config::SECTION_BASE, 'admin_email') . '</p>
    Grüsse ' . Config::get(Config::SECTION_BASE, 'admin_name') . '</body></html>'
)) {
    redirectTo(sprintf('/?p=anmelden&name=%s', $name), 144, __LINE__);
}

redirectTo(sprintf('/?p=anmelden&name=%s', $name), 201);
