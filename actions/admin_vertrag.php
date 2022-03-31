<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

requireAdmin();

$action = getOrDefault($_REQUEST, 'a', 0);
$von = getOrDefault($_POST, 'von', 0);
$an = getOrDefault($_POST, 'an', 0);
$ware = getOrDefault($_POST, 'ware', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$preis = getOrDefault($_POST, 'preis', .0);
$id = getOrDefault($_REQUEST, 'id', 0);

switch ($action) {
    // create new contract
    case 1:
        if ($menge <= 0 || $preis <= 0) {
            header(sprintf('Location: /?p=admin_vertrag_einstellen&von=%d&an=%d&ware=%d&menge=%d&preis=%F&m=%d', $von, $an, $ware, $menge, $preis, 120));
            die();
        }

        $inserted = Database::getInstance()->createTableEntry('vertraege', array('Von' => $von, 'An' => $an, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($inserted == 0) {
            header(sprintf('Location: /?p=admin_vertrag_einstellen&von=%d&an=%d&ware=%d&menge=%d&preis=%F&m=%d', $von, $an, $ware, $menge, $preis, 141));
            die();
        } else {
            header("location: /?p=admin_vertrag&m=218");
            die();
        }
        break;

    // edit existing contract
    case 2:
        if ($menge <= 0 || $preis <= 0) {
            header(sprintf('Location: /?p=admin_vertrag_bearbeiten&von=%d&an=%d&ware=%d&menge=%d&preis=%F&m=%d', $von, $an, $ware, $menge, $preis, 120));
            die();
        }

        $updated = Database::getInstance()->updateTableEntry('vertraege', $id, array('Von' => $von, 'An' => $an, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis));

        if ($updated == 0) {
            header(sprintf('Location: /?p=admin_vertrag_bearbeiten&id=%d&von=%d&an=%d&ware=%d&menge=%d&preis=%F&m=%d', $id, $an, $von, $ware, $menge, $preis, 142));
            die();
        } else {
            header("location: /?p=admin_vertrag&m=234");
            die();
        }
        break;

    // delete existing contract
    case 3:
        $updated = Database::getInstance()->deleteTableEntry('vertraege', $id);

        if ($updated == 0) {
            redirectBack('/?p=admin_vertrag', 143);
        } else {
            header("location: /?p=admin_vertrag&m=233");
            die();
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_vertrag', 112);
        break;
}
