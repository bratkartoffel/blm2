<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Bioladen');

$alles = getOrDefault($_POST, 'alles', 0);
$was = getOrDefault($_POST, 'was', 0);
$menge = getOrDefault($_POST, 'menge', 0);

$data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);
if ($data === null) {
    redirectTo('/?p=bank', 112);
}
$playerName = Database::getInstance()->getPlayerNameById($_SESSION['blm_user']);

if ($alles == 1) {
    $sumMoney = 0;
    $updateStorageValues = array();
    $updateStorageWhere = array();
    Database::getInstance()->begin();
    $idx = 0;
    for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
        $amount = $data['Lager' . $i];
        if ($amount == 0) continue;
        $price = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude3'], $data['Gebaeude6']);

        $sumMoney += $amount * $price;
        $updateStorageValues['Lager' . $i] = -$amount;
        $updateStorageWhere['Lager' . $i . ' >= :whr' . $idx++] = $amount;

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_SHOP, array(
                'playerId' => $_SESSION['blm_user'],
                'playerName' => $playerName,
                'amount' => $amount,
                'item' => $i,
                'price' => $price
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=bioladen', 141, __LINE__);
        }
    }

    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
            array('Geld' => $sumMoney)) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
            array('EinnahmenVerkauf' => $sumMoney), array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
            $updateStorageValues, $updateStorageWhere) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    Database::getInstance()->commit();
    redirectTo('/?p=bioladen', 208);
}

if ($was <= 0 || $was > Config::getInt(Config::SECTION_BASE, 'count_wares')) {
    redirectTo('/?p=bioladen', 112, __LINE__);
}

if ($menge <= 0 || $menge > $data['Lager' . $was]) {
    redirectTo('/?p=bioladen', 125, __LINE__);
}

$price = calculateSellPrice($was, $data['Forschung' . $was], $data['Gebaeude3'], $data['Gebaeude6']);

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
        array('Geld' => $price * $menge)) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
        array('EinnahmenVerkauf' => $price * $menge), array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
        array('Lager' . $was => -$menge),
        array('Lager' . $was . ' >= :whr0' => $menge)) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_SHOP, array(
        'playerId' => $_SESSION['blm_user'],
        'playerName' => $playerName,
        'amount' => $menge,
        'item' => $was,
        'price' => $price
    )) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 141, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=bioladen', 208);
