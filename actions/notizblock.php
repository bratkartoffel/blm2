<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Notizblock');

$notizblock = getOrDefault($_POST, 'notizblock');

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'], array('Notizblock' => $notizblock)) === null) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=notizblock&notizblock=%s', urlencode($notizblock)), 142, __LINE__);
}
Database::getInstance()->commit();
redirectTo('/?p=notizblock', 213);
