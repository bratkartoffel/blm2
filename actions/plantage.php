<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

ob_start();
requireLogin();
restrictSitter('Produktion');

$alles = getOrDefault($_POST, 'alles', 0);
$stunden = getOrDefault($_POST, 'stunden', 0);
$menge = getOrDefault($_POST, 'menge', 0);
$was = getOrDefault($_POST, 'was', 0);

$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevel($_SESSION['blm_user']);
requireEntryFound($data, '/?p=plantage', 112, __LINE__);

if ($alles == 1) {
    if ($stunden < 1 || $stunden > Config::getInt(Config::SECTION_PLANTAGE, 'production_hours_max')) {
        redirectTo('/?p=plantage', 133, __LINE__);
    }

    Database::getInstance()->begin();
    $sum_costs = .0;
    for ($i = 1; $i <= count_wares; $i++) {
        if (!productionRequirementsMet($i, $data['Gebaeude' . building_plantage], $data['Forschung' . $i])) continue;
        $productionData = calculateProductionDataForPlayer($i, $data['Gebaeude' . building_plantage], $data['Forschung' . $i]);

        if (Database::getInstance()->createTableEntry(Database::TABLE_JOBS, array(
                'finished' => date('Y-m-d H:i:s', time() + ($stunden * 3600)),
                'user_id' => $_SESSION['blm_user'],
                'item' => (job_type_factor * job_type_production) + $i,
                'amount' => ceil($stunden * $productionData['Menge']),
                'cost' => $stunden * $productionData['Kosten']
            )) === 1) {
            $sum_costs += $stunden * $productionData['Kosten'];
        }
    }

    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
            array('Geld' => -$sum_costs),
            array('Geld >= :whr0' => $sum_costs)) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=plantage', 111, __LINE__);
    }

    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
            array('AusgabenProduktion' => $sum_costs),
            array('user_id = :whr0' => $_SESSION['blm_user'])
        ) == 0) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=plantage', 142, __LINE__);
    }

    Database::getInstance()->commit();
    redirectTo('/?p=plantage', 207);
}

$productionData = calculateProductionDataForPlayer($was, $data['Gebaeude' . building_plantage], $data['Forschung' . $was]);
$stunden = $menge / $productionData['Menge'];

if ($menge > $productionData['Menge'] * Config::getInt(Config::SECTION_PLANTAGE, 'production_hours_max') || $menge <= 0) {
    redirectTo('/?p=plantage', 125);
}

if ($was <= 0 || $was > count_wares) {
    redirectTo('/?p=plantage', 112);
}

if (!productionRequirementsMet($was, $data['Gebaeude' . building_plantage], $data['Forschung' . $was])) {
    redirectTo('/?p=plantage', 112);
}

Database::getInstance()->begin();

if (Database::getInstance()->createTableEntry(Database::TABLE_JOBS, array(
        'finished' => date('Y-m-d H:i:s', time() + $stunden * 3600),
        'user_id' => $_SESSION['blm_user'],
        'item' => (job_type_factor * job_type_production) + $was,
        'amount' => $menge,
        'cost' => $stunden * $productionData['Kosten']
    )) == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=plantage&was=%d', $was), 141, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
        array('Geld' => -$stunden * $productionData['Kosten']),
        array('Geld >= :whr0' => $stunden * $productionData['Kosten'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=plantage&was=%d', $was), 111, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
        array('AusgabenProduktion' => $stunden * $productionData['Kosten']),
        array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=plantage&was=%d', $was), 142, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=plantage', 207, sprintf('p%s', $was));
