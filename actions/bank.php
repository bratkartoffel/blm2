<?php
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

requireAdmin();
restrictSitter('Bank');

$art = getOrDefault($_POST, 'art');
$betrag = getOrDefault($_POST, 'betrag', .0);

if ($betrag <= 0) {
    redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110);
}

$data = Database::getInstance()->getPlayerBankAndMoneyAndGroupById($_SESSION['blm_user']);

switch ($art) {
    // deposit money
    case 1:
        if ($betrag > $data['Geld'] || $data['Geld'] + $betrag > DEPOSIT_LIMIT) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110);
        }

        $updated = Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'], array(
            'Geld' => -$betrag,
            'Bank' => +$betrag
        ), array(
            'Geld >= :whr0' => $betrag
        ));

        if ($updated == 0) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142);
        }

        Database::getInstance()->createTableEntry('log_bank', array(
            'Wer' => $_SESSION['blm_user'],
            'Wieviel' => $betrag,
            'Einzahlen' => 1
        ));

        redirectTo('/?p=bank', 207);
        break;

    // withdraw money
    case 2:
        if ($betrag > $data['Bank'] || $data['Bank'] - $betrag > CREDIT_LIMIT) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 109);
        }

        $updated = Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'], array(
            'Geld' => +$betrag,
            'Bank' => -$betrag
        ), array(
            'Bank >= :whr0' => $betrag
        ));

        if ($updated == 0) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142);
        }

        Database::getInstance()->createTableEntry('log_bank', array(
            'Wer' => $_SESSION['blm_user'],
            'Wieviel' => $betrag,
            'Einzahlen' => 0
        ));

        redirectTo('/?p=bank', 207);
        break;

    // deposit group account
    case 3:
        if ($betrag > $data['Geld']) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110);
        }

        if ($data['Gruppe'] == null) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 112);
        }

        Database::getInstance()->begin();
        $updated = Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'], array(
            'Geld' => -$betrag
        ), array(
            'Bank >= :whr0' => $betrag
        ));
        if ($updated == 0) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142);
        }

        $updated = Database::getInstance()->updateTableEntryCalculate('gruppe', $data['Gruppe'], array(
            'Kasse' => +$betrag
        ));
        if ($updated == 0) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142);
        }

        redirectTo('/?p=bank', 235);
        break;

    // unknown action
    default:
        redirectBack('/?p=bank', 112);
        break;
}
