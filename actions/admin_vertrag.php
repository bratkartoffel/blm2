<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireAdmin();

$von = getOrDefault($_POST, 'von', 0);
$an = getOrDefault($_POST, 'an', 0);
$ware = getOrDefault($_POST, 'ware', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$preis = getOrDefault($_POST, 'preis', .0);
$id = getOrDefault($_REQUEST, 'id', 0);

switch (getOrDefault($_REQUEST, 'a', 0)) {
    // create new contract
    case 1:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('Location: /?p=admin_vertrag_einstellen&von=%d&an=%d&ware=%d&menge=%d&preis=%F',
                $von, $an, $ware, $menge, $preis), 120, __LINE__);
        }

        if (Database::getInstance()->createTableEntry('vertraege',
                array('Von' => $von, 'An' => $an, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis)) == 0) {
            redirectTo(sprintf('Location: /?p=admin_vertrag_einstellen&von=%d&an=%d&ware=%d&menge=%d&preis=%F',
                $von, $an, $ware, $menge, $preis), 141, __LINE__);
        } else {
            redirectTo("/?p=admin_vertrag", 218);
            die();
        }
        break;

    // edit existing contract
    case 2:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('/?p=admin_vertrag_bearbeiten&id=%d&von=%d&an=%d&ware=%d&menge=%d&preis=%F',
                $id, $von, $an, $ware, $menge, $preis), 120, __LINE__);
        }

        if (Database::getInstance()->updateTableEntry('vertraege', $id, array('Von' => $von, 'An' => $an, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis)) === null) {
            redirectTo(sprintf('Location: /?p=admin_vertrag_bearbeiten&id=%d&von=%d&an=%d&ware=%d&menge=%d&preis=%F',
                $id, $an, $von, $ware, $menge, $preis), 142, __LINE__);
        } else {
            redirectTo("/?p=admin_vertrag", 234);
        }
        break;

    // delete existing contract
    case 3:
        if (Database::getInstance()->deleteTableEntry('vertraege', $id) == 0) {
            redirectBack('/?p=admin_vertrag', 143, __LINE__);
        } else {
            redirectTo("/?p=admin_vertrag", 233);
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_vertrag', 112, __LINE__);
        break;
}
