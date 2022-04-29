<?php
error_reporting(E_ALL);

if (!file_exists(dirname(__FILE__) . '/../include/config.inc.php')) {
    die('include/config.inc.php not found');
}
require_once(dirname(__FILE__) . '/../include/config.inc.php');
require_once(dirname(__FILE__) . '/../include/functions.inc.php');
require_once(dirname(__FILE__) . '/../include/database.class.php');

if (isGameLocked()) {
    die(sprintf("Game is currently locked (%d < %d)\n", time(), last_reset));
}

if (isRoundOver()) {
    handleRoundEnd();
    die("Game reset completed!\n");
}

CheckAllAuftraege();

$interestRates = calculateInterestRates();
$entries = Database::getInstance()->getAllPlayerIdAndBankAndBioladenAndDoenerstand();
Database::getInstance()->begin();
foreach ($entries as $entry) {
    Database::getInstance()->updateTableEntryCalculate('mitglieder', $entry['ID'],
        array('Geld' => getIncome($entry['Gebaeude3'], $entry['Gebaeude4'])));
    Database::getInstance()->updateTableEntryCalculate('statistik', null,
        array('EinnahmenGebaeude' => getIncome($entry['Gebaeude3'], $entry['Gebaeude4'])),
        array('user_id = :whr0' => $entry['ID']));

    if ($entry['Bank'] > deposit_limit) continue;

    if ($entry['Bank'] >= 0) {
        $amount = $entry['Bank'] * $interestRates['Debit'];
        $amount = min(deposit_limit, $entry['Bank'] + $amount) - $entry['Bank'];
    } else {
        $amount = $entry['Bank'] * $interestRates['Credit'];
    }
    $amount = round($amount, 2);
    if ($amount != 0) {
        Database::getInstance()->updateTableEntryCalculate('mitglieder', $entry['ID'],
            array('Bank' => $amount));
        Database::getInstance()->updateTableEntryCalculate('statistik', null,
            array($amount > 0 ? 'EinnahmenZinsen' : 'AusgabenZinsen' => abs($amount)),
            array('user_id = :whr0' => $entry['ID']));
    }
}
Database::getInstance()->commit();

$entries = Database::getInstance()->getAllPlayerIdBankSmallerEquals(dispo_limit);
foreach ($entries as $entry) {
    Database::getInstance()->begin();
    $status = resetAccount($entry['ID']);
    if ($status !== null) {
        Database::getInstance()->rollBack();
        trigger_error("Could not reset player " . $entry['ID'] . ' with status ' . $status, E_USER_WARNING);
        continue;
    }
    if (Database::getInstance()->createTableEntry('nachrichten', array(
            'Von' => 0,
            'An' => $entry['ID'],
            'Betreff' => 'Account zurückgesetzt',
            'Nachricht' => "Nachdem Ihr Kontostand unter " . formatCurrency(dispo_limit) . " gefallen ist wurden Sie gezwungen, Insolvenz anzumelden. Sie haben sich an der Grenze zu Absurdistan einen neuen Pass geholt und versuchen Ihr Glück mit einer neuen Identität nochmal neu"
        )) != 1) {
        Database::getInstance()->rollBack();
        trigger_error("Could create message after resetting player " . $entry['ID'], E_USER_WARNING);
        continue;
    }
    Database::getInstance()->commit();
}
