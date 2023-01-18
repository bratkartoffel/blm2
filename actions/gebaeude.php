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
restrictSitter('Gebaeude');

$was = getOrDefault($_POST, 'was', 0);
$data = Database::getInstance()->getPlayerMoneyAndBuildingLevelsAndExpenseMafia($_SESSION['blm_user']);
requireEntryFound($data, '/?p=gebaeude', 112, __LINE__);
$buildingData = calculateBuildingDataForPlayer($was, $data);

if ($data['Geld'] < $buildingData['Kosten'] || !buildingRequirementsMet($was, $data)) {
    redirectTo('/?p=gebaeude', 112, __LINE__);
}

Database::getInstance()->begin();
if (Database::getInstance()->createTableEntry(Database::TABLE_JOBS, array(
        'finished' => date("Y-m-d H:i:s", time() + $buildingData['Dauer']),
        'user_id' => $_SESSION['blm_user'],
        'item' => (job_type_factor * job_type_building) + $was,
        'cost' => $buildingData['Kosten']
    )) == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=gebaeude&was=%d', $was), 141, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], array(
        'Geld' => -$buildingData['Kosten']
    ), array(
        'Geld >= :whr0' => $buildingData['Kosten']
    )) == 0) {
    Database::getInstance()->rollBack();
    redirectTo(sprintf('/?p=gebaeude&was=%d', $was), 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
        array('AusgabenGebaeude' => $buildingData['Kosten']),
        array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=gebaeude', 142, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=gebaeude', 207, "g" . $was);
