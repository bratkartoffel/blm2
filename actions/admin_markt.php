<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireAdmin();

$von = getOrDefault($_POST, 'von', 0);
$ware = getOrDefault($_POST, 'ware', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$preis = getOrDefault($_POST, 'preis', .0);
$id = getOrDefault($_REQUEST, 'id', 0);

switch (getOrDefault($_REQUEST, 'a', 0)) {
    // create new offer
    case 1:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('/?p=admin_markt_einstellen&von=%d&ware=%d&menge=%d&preis=%f',
                $von, $ware, $menge, $preis), 120, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_MARKET, array('Von' => $von, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis)) !== 1) {
            redirectTo(sprintf('/?p=admin_markt_einstellen&von=%d&ware=%d&menge=%d&preis=%f',
                $von, $ware, $menge, $preis), 141, __LINE__);
        } else {
            redirectTo('/?p=admin_markt', 218);
        }
        break;

    // edit existing offer
    case 2:
        if ($menge <= 0 || $preis <= 0) {
            redirectTo(sprintf('/?p=admin_markt_bearbeiten&id=%d&von=%d&ware=%d&menge=%d&preis=%f',
                $id, $von, $ware, $menge, $preis), 120, __LINE__);
        }

        if (Database::getInstance()->updateTableEntry(Database::TABLE_MARKET, $id, array('Von' => $von, 'Was' => $ware, 'Menge' => $menge, 'Preis' => $preis)) === null) {
            redirectTo(sprintf('/?p=admin_markt_bearbeiten&id=%d&von=%d&ware=%d&menge=%d&preis=%f',
                $id, $von, $ware, $menge, $preis), 142, __LINE__);
        } else {
            redirectTo('/?p=admin_markt', 234);
        }
        break;

    // delete existing offer
    case 3:
        requireXsrfToken('/?p=admin_markt');
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_MARKET, $id) !== 1) {
            redirectBack('/?p=admin_markt', 143, __LINE__);
        } else {
            redirectTo('/?p=admin_markt', 233);
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_markt', 112, __LINE__);
        break;
}
