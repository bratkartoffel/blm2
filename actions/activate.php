<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

$user = getOrDefault($_GET, 'user');
$code = getOrDefault($_GET, 'code');
$id = Database::getInstance()->getPlayerIdByNameAndActivationToken($user, $code);

if ($id == null) {
    redirectBack('/?p=index', 117);
}
$updated = Database::getInstance()->updateTableEntry('mitglieder', $id, array('EMailAct' => null));

if ($updated == 0) {
    redirectBack('/?p=index', 117);
} else {
    header("location: /?p=anmelden&m=241");
}
