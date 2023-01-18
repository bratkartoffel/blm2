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

$user = getOrDefault($_GET, 'user');
$email = getOrDefault($_GET, 'email');
$code = getOrDefault($_GET, 'code');
$id = Database::getInstance()->getPlayerIdByNameOrEmailAndActivationToken($user, $email, $code);
requireEntryFound($id, '/?p=index', 117, __LINE__);

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('EMailAct' => null)) !== 1) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=index', 117, __LINE__);
}
Database::getInstance()->commit();
header("location: /?p=anmelden&m=241");
