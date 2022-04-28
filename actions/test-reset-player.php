<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
if (!is_testing) {
    redirectTo('/?p=index', 112, __LINE__);
}

$id = getOrDefault($_GET, 'id', 0);
Database::getInstance()->begin();
$status = resetAccount($id);
if ($status !== null) {
    die('could not reset account due to ' . $status);
}


switch ($id) {
    case 11:
        // just reset player, no special updates afterwards needed
        break;

    case 12:
        Database::getInstance()->updateTableEntry('gebaeude', null, array('Gebaeude1' => 3, 'Gebaeude2' => 3), array('user_id = :whr0' => $id));
        Database::getInstance()->updateTableEntry('forschung', null, array('Forschung1' => 2, 'Forschung2' => 1), array('user_id = :whr0' => $id));
        Database::getInstance()->updateTableEntry('lagerhaus', null, array('Lager1' => 100, 'Lager2' => 50), array('user_id = :whr0' => $id));
        break;

    case 13:
        Database::getInstance()->updateTableEntry('mitglieder', $id, array('Geld' => 100000, 'Bank' => 50000));
        break;

    case 14:
        Database::getInstance()->updateTableEntry('gebaeude', null, array('Gebaeude1' => 8), array('user_id = :whr0' => $id));
        break;

    default:
        die('unknown ID');
}

Database::getInstance()->commit();
redirectTo('/actions/logout.php');
