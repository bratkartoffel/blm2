<?php /** @noinspection PhpIncludeInspection */
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

ob_start();
$id = getOrDefault($_GET, 'id', 0);
if ($id === 0) {
    http_send_status(400);
    die('no id given');
}

$testClass = getOrDefault($_GET, 'class');
if ($testClass === null) {
    http_send_status(400);
    die('no class given');
}
$testMethod = getOrDefault($_GET, 'method');

Database::getInstance()->begin();

// create or reset account
if (!Database::getInstance()->existsTableEntry(Database::TABLE_USERS, array('ID' => $id))) {
    $user = Database::getInstance()->createUser(sprintf("test%d", $id), sprintf("%s_%d@localhost", $testClass, $id), null, 'changeit');
    Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $user, array('ID' => $id));
    Database::getInstance()->updateTableEntry(Database::TABLE_STATISTICS, null, array('user_id' => $id), array('user_id = :whr0' => $user));
} else {
    resetAccount($id);
    Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Passwort' => hashPassword('changeit')));
}

switch ($testClass) {
    case 'BankTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Geld' => 100000,
            'Bank' => 50000,
        ));
        if ($testMethod === 'testTextFieldPreFilled') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Geld' => 100001, 'Bank' => 123.01, 'Gruppe' => 1));
        }
        if ($testMethod === 'testDepositWithBankSafe') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Geld' => 200000, 'Bank' => 70000, 'Gebaeude9' => 1));
        }
        if ($testMethod === 'testInterestPlusLimitWithCron') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Bank' => 99900));
        }
        if ($testMethod === 'testInterestPlusWithCronAndBuilding') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Bank' => 99900, 'Gebaeude9' => 1));
        }
        if ($testMethod === 'testResetAfterDispoLimit') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Bank' => -210000));
        }
        break;

    case 'BuildingTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Gebaeude' . building_plantage => 8,
            'Gebaeude' . building_building_yard => 80,
        ));
        break;

    case 'GroupTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Gebaeude' . building_plantage => 8,
        ));
        break;

    case 'MessageTests':
        // delete all messages from and to this account
        Database::getInstance()->deleteTableEntryWhere(Database::TABLE_MESSAGES, array('Von' => $id));
        Database::getInstance()->deleteTableEntryWhere(Database::TABLE_MESSAGES, array('An' => $id));
        break;

    case 'PlantageTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Geld' => 15000,
            'Gebaeude' . building_plantage => 30,
            'Gebaeude' . building_research_lab => 3,
            'Forschung' . item_potatoes => 2,
            'Forschung' . item_carrots => 1,
            'Forschung' . item_kiwi => 20,
        ));
        break;

    case 'ResearchTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Geld' => 15000));
        if ($testMethod !== 'testNotBuilt') {
            Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
                'Gebaeude' . building_research_lab => 10,
            ));
        }
        break;

    case 'ShopTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Lager' . item_potatoes => 100,
            'Lager' . item_carrots => 50,
            'Lager' . item_apples => 27,
        ));
        break;

    case 'MafiaTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
            'Lager' . item_potatoes => 100,
            'Lager' . item_carrots => 50,
            'Lager' . item_apples => 27,
            'Bank' => 12345,
        ));
        $additional = getOrDefault($_GET, 'additional', 0);
        switch ($additional) {
            case 0:
                Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Punkte' => 3999));
                break;

            case 1:
                Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Punkte' => 5000));
                break;

            case 2:
                Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
                    'Punkte' => 7500,
                    'Gebaeude' . building_pizzeria => 50,
                ));
                break;

            case 3:
                Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array(
                    'Punkte' => 11250,
                    'Gebaeude' . building_fence => 100,
                ));
                break;
        }
        break;

    case 'AdminTests':
        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Admin' => 1));

        if (substr($testMethod, 0, 10) === 'testImport') {
            // delete all other accounts
            $players = Database::getInstance()->getAllPlayerIdsAndName();
            foreach ($players as $player) {
                if ($player['ID'] != $id && $player['Name'] !== 'admin') {
                    deleteAccount($player['ID']);
                }
            }
        }
        break;

    default:
        error_log('unknown class given: ' . $testClass);
}

Database::getInstance()->commit();
redirectTo('/actions/logout.php');
