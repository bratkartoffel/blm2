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
restrictSitter('Bank');

$art = getOrDefault($_POST, 'art', 0);
$betrag = getOrDefault($_POST, 'betrag', .0);

if ($betrag <= 0) {
    redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110, __LINE__);
}

$data = Database::getInstance()->getPlayerNameAndBankAndMoneyAndGroupById($_SESSION['blm_user']);
if ($data === null) {
    redirectTo('/?p=bank', 112, __LINE__);
}

switch ($art) {
    // deposit money
    case 1:
        if ($betrag > $data['Geld'] || $data['Bank'] + $betrag > Config::getInt(Config::SECTION_BANK, 'deposit_limit')) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], array(
                'Geld' => -$betrag,
                'Bank' => +$betrag
            ), array(
                'Geld >= :whr0' => $betrag
            )) == 0) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_BANK, array(
                'playerId' => $_SESSION['blm_user'],
                'playerName' => $data['Name'],
                'amount' => $betrag,
                'target' => 'BANK'
            )) == 0) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=bank', 207);
        break;

    // withdraw money
    case 2:
        if ($data['Bank'] - $betrag < Config::getInt(Config::SECTION_BANK, 'credit_limit')) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 109, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], array(
                'Geld' => +$betrag,
                'Bank' => -$betrag
            ), array(
                'Bank + ' . abs(Config::getInt(Config::SECTION_BANK, 'credit_limit')) . ' >= :whr0' => $betrag
            )) == 0) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_BANK, array(
                'playerId' => $_SESSION['blm_user'],
                'playerName' => $data['Name'],
                'amount' => $betrag,
                'target' => 'HAND'
            )) == 0) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=bank', 207);
        break;

    // deposit group account
    case 3:
        $group = Database::getInstance()->getGroupIdAndNameById($data['Gruppe']);
        requireEntryFound($group, sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), __LINE__);

        if ($betrag > $data['Geld']) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 110, __LINE__);
        }

        if ($data['Gruppe'] === null) {
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Geld' => -$betrag,), array('Geld >= :whr0' => $betrag)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP_CASH, null,
                array('amount' => +$betrag), array('user_id = :whr0' => $_SESSION['blm_user'], 'group_id = :whr1' => $data['Gruppe'])) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142, __LINE__);
        }

        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $data['Gruppe'], array('Kasse' => +$betrag)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $data['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $data['Name'])
                    . ' hat ' . formatCurrency($betrag)
                    . ' in die Gruppenkasse eingezahlt.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 141, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_GROUP_CASH, array(
                'senderId' => $_SESSION['blm_user'],
                'senderName' => $data['Name'],
                'groupId' => $group['ID'],
                'groupName' => $group['Name'],
                'amount' => $betrag
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=bank&art=%d&betrag=%f', $art, $betrag), 141, __LINE__);
        }
        Database::getInstance()->commit();

        redirectTo('/?p=bank', 235);
        break;

    // unknown action
    default:
        redirectTo('/?p=bank', 112, __LINE__);
        break;
}
