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
requireLogin();
restrictSitter('Notizblock');

$notizblock = getOrDefault($_POST, 'notizblock');

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'], array('Notizblock' => $notizblock)) === null) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=notizblock&notizblock=%s', urlencode($notizblock)), 142, __LINE__);
}
Database::getInstance()->commit();
redirectTo('/?p=notizblock', 213);
