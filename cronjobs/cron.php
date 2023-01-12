<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
error_reporting(E_ALL);
const IS_CRON = true;
require_once(dirname(__FILE__) . '/../include/functions.inc.php');
require_once(dirname(__FILE__) . '/../include/database.class.php');

if (!IS_CRON) {
    die('cannot happen, just to please phpstrom nagging about unused variable');
}

if (isGameLocked()) {
    die(sprintf("Game is currently locked (%d < %d)\n", time(), Config::getInt(Config::SECTION_BASE, 'roundstart')));
}

if (isRoundOver()) {
    handleRoundEnd();
    die("Game reset completed!\n");
}

function handleInterestRates(): void
{
    $interestRates = calculateInterestRates();
    $entries = Database::getInstance()->getAllPlayerIdAndBankAndBioladenAndDoenerstand();
    foreach ($entries as $entry) {
        Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'],
            array('Geld' => getIncome($entry['Gebaeude3'], $entry['Gebaeude4'])));
        Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
            array('EinnahmenGebaeude' => getIncome($entry['Gebaeude3'], $entry['Gebaeude4'])),
            array('user_id = :whr0' => $entry['ID']));

        if ($entry['Bank'] > Config::getInt(Config::SECTION_BANK, 'deposit_limit')) continue;

        if ($entry['Bank'] >= 0) {
            $amount = $entry['Bank'] * $interestRates['Debit'];
            $amount = min(Config::getInt(Config::SECTION_BANK, 'deposit_limit'), $entry['Bank'] + $amount) - $entry['Bank'];
        } else {
            $amount = $entry['Bank'] * $interestRates['Credit'];
        }
        $amount = round($amount, 2);
        if ($amount != 0) {
            Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'],
                array('Bank' => $amount));
            Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array($amount > 0 ? 'EinnahmenZinsen' : 'AusgabenZinsen' => abs($amount)),
                array('user_id = :whr0' => $entry['ID']));
        }
    }
}

function handleResetDueToDispo(): void
{
    $entries = Database::getInstance()->getAllPlayerIdAndNameBankSmallerEquals(Config::getInt(Config::SECTION_BANK, 'dispo_limit'));
    foreach ($entries as $entry) {
        trigger_error(sprintf("Resetting player %s/%s", $entry['ID'], $entries['Name']));
        Database::getInstance()->begin();
        $status = resetAccount($entry['ID']);
        if ($status !== null) {
            Database::getInstance()->rollBack();
            trigger_error("Could not reset player " . $entry['ID'] . ' with status ' . $status, E_USER_WARNING);
            continue;
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $entry['ID'],
                'Betreff' => 'Account zurückgesetzt',
                'Nachricht' => "Nachdem Ihr Kontostand unter " . formatCurrency(Config::getInt(Config::SECTION_BANK, 'dispo_limit')) . " gefallen ist wurden Sie gezwungen, Insolvenz anzumelden. Sie haben sich an der Grenze zu Absurdistan einen neuen Pass geholt und versuchen Ihr Glück mit einer neuen Identität nochmal neu"
            )) != 1) {
            Database::getInstance()->rollBack();
            trigger_error("Could create message after resetting player " . $entry['ID'], E_USER_WARNING);
            continue;
        }
        Database::getInstance()->commit();
    }
}

function handleItemBaseProduction(): void
{
    $entries = Database::getInstance()->getAllPlayerIdAndResearchLevels();
    foreach ($entries as $entry) {
        $updates = array();
        for ($i = 1; $i < Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
            $researchLevel = $entry['Forschung' . $i];
            $updates['Lager' . $i] = $researchLevel * Config::getInt(Config::SECTION_PLANTAGE, 'production_cron_base');
        }
        Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['ID'], $updates);
    }
}

Database::getInstance()->begin();
CheckAllAuftraege();
handleInterestRates();
handleItemBaseProduction();
Database::getInstance()->updatePlayerOnlineTimes();
Database::getInstance()->commit();

// separate transaction for each player to reset
handleResetDueToDispo();
