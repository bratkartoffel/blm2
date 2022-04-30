<?php
require_once('../include/config.inc.php');
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
    $updateStorageWhere = array('user_id = :whr0' => $_SESSION['blm_user']);
    Database::getInstance()->begin();
    $idx = 1;
    for ($i = 1; $i <= count_wares; $i++) {
        $amount = $data['Lager' . $i];
        if ($amount == 0) continue;
        $price = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude3'], $data['Gebaeude6']);

        $sumMoney += $amount * $price;
        $updateStorageValues['Lager' . $i] = -$amount;
        $updateStorageWhere['Lager' . $i . ' >= :whr' . $idx++] = $amount;

        if (Database::getInstance()->createTableEntry('log_bioladen', array(
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

    if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
            array('Geld' => $sumMoney)) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    if (Database::getInstance()->updateTableEntryCalculate('statistik', null,
            array('EinnahmenVerkauf' => $sumMoney), array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null,
            $updateStorageValues, $updateStorageWhere) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=bioladen', 142, __LINE__);
    }

    Database::getInstance()->commit();
    redirectTo('/?p=bioladen', 208);
}

if ($was <= 0 || $was > count_wares) {
    redirectTo('/?p=bioladen', 112, __LINE__);
}

if ($menge <= 0 || $menge > $data['Lager' . $was]) {
    redirectTo('/?p=bioladen', 125, __LINE__);
}

$price = calculateSellPrice($was, $data['Forschung' . $was], $data['Gebaeude3'], $data['Gebaeude6']);

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
        array('Geld' => $price * $menge)) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate('statistik', null,
        array('EinnahmenVerkauf' => $price * $menge), array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null,
        array('Lager' . $was => -$menge),
        array('user_id = :whr0' => $_SESSION['blm_user'], 'Lager' . $was . ' >= :whr1' => $menge)) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=bioladen', 142, __LINE__);
}

if (Database::getInstance()->createTableEntry('log_bioladen', array(
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
