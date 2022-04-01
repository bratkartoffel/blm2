<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

requireAdmin();

$action = getOrDefault($_REQUEST, 'a', 0);
$von = getOrDefault($_POST, 'von', 0);
$ware = getOrDefault($_POST, 'ware', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$preis = getOrDefault($_POST, 'preis', .0);
$id = getOrDefault($_REQUEST, 'id', 0);

switch ($action) {
    // create new offer
    case 1:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('/?p=admin_markt_einstellen&von=%d&ware=%d&menge=%d&preis=%f', urlencode($von), urlencode($ware), urlencode($menge), urlencode($preis)), 120);
        }

        $inserted = Database::getInstance()->createTableEntry('marktplatz', array('Von' => $von, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($inserted == 0) {
            redirectTo(sprintf('/?p=admin_markt_einstellen&von=%d&ware=%d&menge=%d&preis=%f', urlencode($von), urlencode($ware), urlencode($menge), urlencode($preis)), 141);
        } else {
            redirectTo('/?p=admin_markt', 218);
        }
        break;

    // edit existing offer
    case 2:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('/?p=admin_markt_bearbeiten&id=%d&von=%d&ware=%d&menge=%d&preis=%f', urlencode($id), urlencode($von), urlencode($ware), urlencode($menge), urlencode($preis)), 120);
            die();
        }

        $updated = Database::getInstance()->updateTableEntry('marktplatz', $id, array('Von' => $von, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($updated === null) {
            redirectTo(sprintf('/?p=admin_markt_bearbeiten&id=%d&von=%d&ware=%d&menge=%d&preis=%f', urlencode($id), urlencode($von), urlencode($ware), urlencode($menge), urlencode($preis)), 142);
            die();
        } else {
            redirectTo('/?p=admin_markt', 234);
            die();
        }
        break;

    // delete existing offer
    case 3:
        $updated = Database::getInstance()->deleteTableEntry('marktplatz', $id);

        if ($updated == 0) {
            redirectBack('/?p=admin_markt', 143);
        } else {
            redirectTo('/?p=admin_markt', 233);
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_markt', 112);
        break;
}
