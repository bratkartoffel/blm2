<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

$name = getOrDefault($_POST, 'name');
$pwd = sha1(getOrDefault($_POST, 'pwd'));

// force a new session
session_reset();
session_regenerate_id();

// check if game is locked
if (CheckGameLock()) {
    redirectTo('/?p=anmelden', 112);
}

// load player data
$player = Database::getInstance()->getPlayerDataByNameAndPassword($name, $pwd);

// check if account found
if ($player == null) {
    redirectTo('/?p=anmelden', 108);
}

// check if account is locked
if ($player['Gesperrt'] == 1) {
    redirectTo('/?p=anmelden', 139);
}

// check if account is activated
if ($player['EMailAct'] != null) {
    redirectTo('/?p=anmelden', 135);
}

$_SESSION['blm_user'] = intval($player['ID']);
$_SESSION['blm_login'] = time();
$_SESSION['blm_queries'] = 0;
// check if it's a sitter login
if ($player['IstSitter'] == 1) {
    $_SESSION['blm_sitter'] = true;
    $_SESSION['blm_admin'] = false;
} else {
    $_SESSION['blm_sitter'] = false;
    $_SESSION['blm_admin'] = ($player['Admin'] == 1);
    Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'], array('LastLogin' => time(), 'LastAction' => time()));
}

$inserted = Database::getInstance()->createTableEntry('log_login',
    array(
        'IP' => $_SERVER['REMOTE_ADDR'],
        'Wer' => $_SESSION['blm_user'],
        'Sitter' => $_SESSION['blm_sitter'] ? 1 : 0
    ));

if ($inserted == 0) {
    session_destroy();
    redirectTo('/?p=index', 141);
} else {
    redirectTo('/?p=index', 202);
}
