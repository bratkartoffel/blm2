<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();

$name = getOrDefault($_POST, 'name');
$pwd = getOrDefault($_POST, 'pwd');

// force a new session
session_reset();
session_regenerate_id();

// check if game is locked
if (isGameLocked()) {
    redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 999, __LINE__);
}

// load player data
$player = Database::getInstance()->getPlayerDataByName($name);
requireEntryFound($player, sprintf("/?p=anmelden&name=%s", urlencode($name)), 108, __LINE__);

if ($player['Gesperrt'] == 1) {
    redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 139, __LINE__);
}
if ($player['EMailAct'] !== null) {
    redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 135, __LINE__);
}

if (verifyPassword($pwd, $player['user_password'])) {
    $_SESSION['blm_sitter'] = false;
    $_SESSION['blm_admin'] = ($player['Admin'] == 1);
} else if ($player['sitter_password'] !== null && verifyPassword($pwd, $player['sitter_password'])) {
    $_SESSION['blm_sitter'] = true;
    $_SESSION['blm_admin'] = false;
} else if (sha1($pwd) == $player['user_password']) {
    $_SESSION['blm_sitter'] = false;
    $_SESSION['blm_admin'] = ($player['Admin'] == 1);
} else {
    redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 108, __LINE__);
}

Database::getInstance()->begin();
if (!$_SESSION['blm_sitter']) {
    if (passwordNeedsUpgrade($player['user_password']) && Database::getInstance()->updateTableEntry('mitglieder', $player['ID'],
            array('Passwort' => hashPassword($pwd))) !== 1) {
        Database::getInstance()->rollBack();
        redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 142, __LINE__);
    }
    if (Database::getInstance()->updateTableEntry('mitglieder', $player['ID'],
            array('LastLogin' => date('Y-m-d H:i:s'), 'LastAction' => date('Y-m-d H:i:s'))) !== 1) {
        Database::getInstance()->rollBack();
        redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 142, __LINE__);
    }
}

if (Database::getInstance()->createTableEntry('log_login', array(
        'ip' => $_SERVER['REMOTE_ADDR'],
        'playerId' => $player['ID'],
        'playerName' => $player['Name'],
        'success' => 1,
        'sitter' => $_SESSION['blm_sitter'] ? 1 : 0
    )) !== 1) {
    Database::getInstance()->rollBack();
    session_destroy();
    redirectTo(sprintf("/?p=anmelden&name=%s", urlencode($name)), 141, __LINE__);
}

Database::getInstance()->commit();

$_SESSION['blm_user'] = intval($player['ID']);
$_SESSION['blm_login'] = time();
$_SESSION['blm_lastAction'] = time();
$_SESSION['blm_queries'] = 0;
$_SESSION['blm_xsrf_token'] = createRandomCode();

redirectTo('/?p=index', 202);
