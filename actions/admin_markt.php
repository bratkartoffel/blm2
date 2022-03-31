<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

requireAdmin();

$action = getOrDefault($_POST, 'a', 0);
$ware = getOrDefault($_POST, 'ware', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$preis = getOrDefault($_POST, 'preis', .0);
$id = getOrDefault($_POST, 'id', .0);

switch ($action) {
    // create new offer
    case 1:
        if ($menge <= 0 || $preis <= 0) {
            redirectBack('/?p=admin_markt_einstellen', 120);
        }

        $inserted = Database::getInstance()->createTableEntry('marktplatz', array('Ware' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($inserted == 0) {
            redirectBack('/?p=admin_markt_einstellen', 141);
        } else {
            header("location: /?p=admin_markt&m=218");
        }
        break;

    // edit existing offer
    case 2:
        if ($menge <= 0 || $preis <= 0) {
            redirectBack('/?p=admin_markt_bearbeiten&id=' . $id, 120);
        }

        $updated = Database::getInstance()->updateTableEntry('marktplatz', $id, array('Ware' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($updated == 0) {
            redirectBack('/?p=admin_markt_bearbeiten', 142);
        } else {
            header("location: /?p=admin_markt&m=234");
        }
        break;

    // delete existing offer
    case 3:

        $updated = Database::getInstance()->deleteTableEntry('marktplatz', $id);

        if ($updated == 0) {
            redirectBack('/?p=admin_markt', 143);
        } else {
            header("location: /?p=admin_markt&m=233");
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_markt', 112);
        break;
}
