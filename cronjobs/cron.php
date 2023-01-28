<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

error_reporting(E_ALL);
ini_set('display_errors', 'false');
ob_start();

require_once __DIR__ . '/../include/config.class.php';
if (Config::getBoolean(Config::SECTION_BASE, 'testing')) {
    ini_set('display_errors', 'true');
}

const IS_CRON = true;
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

if (!IS_CRON) {
    die('cannot happen, just to please PhpStorm nagging about unused variable');
}

// also loads runtime configuration from database
$database = Database::getInstance();

if (isGameLocked()) {
    die(sprintf("Game is currently locked (%d < %d)\n", time(), Config::getInt(Config::SECTION_DBCONF, 'roundstart')));
}

if (isRoundOver()) {
    handleRoundEnd();
    die("Game reset completed!\n");
}

function CheckAllAuftraege(Database $database): void
{
    $players = $database->getAllPlayerIdsAndName();
    foreach ($players as $player) {
        CheckAuftraege($player['ID']);
    }
}

function handleInterestRates(Database $database): void
{
    $interestRates = calculateInterestRates();
    $entries = $database->getAllPlayerIdAndBankAndBioladenAndDoenerstandAndBank();
    foreach ($entries as $entry) {
        $database->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'],
            array('Geld' => getIncome($entry['Gebaeude' . building_shop], $entry['Gebaeude' . building_kebab_stand])));
        $database->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
            array('EinnahmenGebaeude' => getIncome($entry['Gebaeude' . building_shop], $entry['Gebaeude' . building_kebab_stand])),
            array('user_id = :whr0' => $entry['ID']));

        $depositLimit = pow(2, $entry['Gebaeude' . building_bank]) * Config::getInt(Config::SECTION_BANK, 'deposit_limit');
        if ($entry['Bank'] >= $depositLimit) continue;

        if ($entry['Bank'] >= 0) {
            $amount = $entry['Bank'] * $interestRates['Debit'];
            $amount = min($depositLimit, $entry['Bank'] + $amount) - $entry['Bank'];
        } else {
            $amount = $entry['Bank'] * $interestRates['Credit'];
        }
        $amount = round($amount, 2);
        if ($amount != 0) {
            $database->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'],
                array('Bank' => $amount));
            $database->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array($amount > 0 ? 'EinnahmenZinsen' : 'AusgabenZinsen' => abs($amount)),
                array('user_id = :whr0' => $entry['ID']));
        }
    }
}

function handleResetDueToDispo(Database $database): void
{
    $entries = $database->getAllPlayerIdAndNameBankSmallerEquals(Config::getInt(Config::SECTION_BANK, 'dispo_limit'));
    foreach ($entries as $entry) {
        error_log(sprintf('Resetting player %s/%s', $entry['ID'], $entries['Name']));
        $database->begin();
        $status = resetAccount($entry['ID']);
        if ($status !== null) {
            $database->rollBack();
            error_log(sprintf('Could not reset player %d with status %s', $entry['ID'], $status));
            continue;
        }
        if ($database->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $entry['ID'],
                'Betreff' => 'Account zurückgesetzt',
                'Nachricht' => "Nachdem Ihr Kontostand unter " . formatCurrency(Config::getInt(Config::SECTION_BANK, 'dispo_limit')) . " gefallen ist wurden Sie gezwungen, Insolvenz anzumelden. Sie haben sich an der Grenze zu Absurdistan einen neuen Pass geholt und versuchen Ihr Glück mit einer neuen Identität nochmal neu"
            )) != 1) {
            $database->rollBack();
            error_log(sprintf('Could create message after resetting player %d', $entry['ID']));
            continue;
        }
        $database->commit();
    }
}

function handleItemBaseProduction(Database $database): void
{
    $entries = $database->getAllPlayerIdAndResearchLevels();
    foreach ($entries as $entry) {
        $updates = array();
        for ($i = 1; $i <= count_wares; $i++) {
            $researchLevel = $entry['Forschung' . $i];
            $updates['Lager' . $i] = $researchLevel * Config::getInt(Config::SECTION_PLANTAGE, 'production_cron_base');
        }
        $database->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'], $updates);
    }
}

$database->begin();
CheckAllAuftraege($database);
handleInterestRates($database);
handleItemBaseProduction($database);
$database->updatePlayerOnlineTimes();
$points_interval = Config::getInt(Config::SECTION_BASE, 'points_interval') * 3600;
$last_points = Config::getInt(Config::SECTION_DBCONF, 'lastpoints');
if ($last_points + $points_interval - 60 <= time()) {
    $database->updatePlayerPoints();
}
$database->gdprCleanLoginLog();
$database->updateLastCron();
$database->commit();

// separate transaction for each player to reset
handleResetDueToDispo($database);
