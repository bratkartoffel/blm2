<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

requireLogin();
restrictSitter('Notizblock');

$notizblock = getOrDefault($_POST, 'notizblock');

Database::getInstance()->begin();

$updated = Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
    array('Notizblock' => $notizblock)
);

if ($updated == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=notizblock&notizblock=%s', urlencode($notizblock)), 142);
}

Database::getInstance()->commit();
redirectTo('/?p=notizblock', 213);
