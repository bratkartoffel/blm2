<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

ob_start();

$name = requireEntryFound(getOrDefault($_POST, 'name'), '/?p=anmelden', 108, __LINE__);
$pwd = requireEntryFound(getOrDefault($_POST, 'pwd'), '/?p=anmelden', 108, __LINE__);

// force a new session
session_reset();
session_regenerate_id(true);

// load player data
$player = Database::getInstance()->getPlayerDataByName($name);
requireEntryFound($player, sprintf('/?p=anmelden&name=%s', urlencode($name)), 108, __LINE__);

// check if game is locked
if (isGameLocked() && $player['Admin'] != 1) {
    redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 999, __LINE__);
}

if ($player['Gesperrt'] == 1) {
    redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 139, __LINE__);
}
if ($player['EMailAct'] !== null) {
    redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 135, __LINE__);
}

Database::getInstance()->begin();
if (verifyPassword($pwd, $player['user_password'])) {
    $_SESSION['blm_sitter'] = false;
    $_SESSION['blm_admin'] = ($player['Admin'] == 1);
} else if ($player['sitter_password'] !== null && verifyPassword($pwd, $player['sitter_password'])) {
    $_SESSION['blm_sitter'] = true;
    $_SESSION['blm_admin'] = false;
} else {
    Database::getInstance()->createTableEntry(Database::TABLE_LOG_LOGIN, array(
        'ip' => $_SERVER['REMOTE_ADDR'],
        'playerId' => $player['ID'],
        'playerName' => $player['Name'],
        'success' => 0,
        'sitter' => 0
    ));
    Database::getInstance()->commit();
    redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 108, __LINE__);
}

if (!$_SESSION['blm_sitter']) {
    if (passwordNeedsUpgrade($player['user_password']) && Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $player['ID'],
            array('Passwort' => hashPassword($pwd))) !== 1) {
        Database::getInstance()->rollBack();
        redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 142, __LINE__);
    }
    if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $player['ID'],
            array('LastLogin' => date('Y-m-d H:i:s'), 'LastAction' => date('Y-m-d H:i:s'))) !== 1) {
        Database::getInstance()->rollBack();
        redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 142, __LINE__);
    }
}

if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_LOGIN, array(
        'ip' => $_SERVER['REMOTE_ADDR'],
        'playerId' => $player['ID'],
        'playerName' => $player['Name'],
        'success' => 1,
        'sitter' => $_SESSION['blm_sitter'] ? 1 : 0
    )) !== 1) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=anmelden&name=%s', urlencode($name)), 141, __LINE__);
}

Database::getInstance()->commit();

$_SESSION['blm_user'] = intval($player['ID']);
$_SESSION['blm_login'] = time();
$_SESSION['blm_lastAction'] = time();
$_SESSION['blm_queries'] = 0;
$_SESSION['blm_xsrf_token'] = createRandomCode();

redirectTo('/?p=index', 202);
