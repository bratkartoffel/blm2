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
restrictSitter('Nachrichten');

switch (getOrDefault($_GET, 'a', 0)) {
    // send message
    case 1:
        $receiver = getOrDefault($_POST, 'receiver');
        $subject = getOrDefault($_POST, 'subject');
        $message = getOrDefault($_POST, 'message');
        $broadcast = getOrDefault($_POST, 'broadcast', 0);
        $base_link = sprintf('/?p=nachrichten_schreiben&receiver=%s&subject=%s&broadcast=1&message=%s',
            urlencode($receiver), urlencode($subject), urlencode($message));

        if (strlen($message) < 8) {
            redirectTo($base_link, 128, __LINE__);
        }
        if (strlen($subject) < 4) {
            redirectTo($base_link, 128, __LINE__);
        }

        if ($broadcast == 1) {
            if (!isAdmin()) {
                redirectTo($base_link, 112, __LINE__);
            }
            $data = Database::getInstance()->getAllPlayerIdsAndName();

            Database::getInstance()->begin();
            foreach ($data as $player) {
                if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                        'Von' => $_SESSION['blm_user'],
                        'An' => $player['ID'],
                        'Nachricht' => $message,
                        'Betreff' => $subject
                    )) !== 1) {
                    Database::getInstance()->rollBack();
                    redirectTo($base_link, 141, __LINE__);
                }
                if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $player['ID'], array('IgmEmpfangen' => 1)) !== 1) {
                    Database::getInstance()->rollBack();
                    redirectTo($base_link, 142, __LINE__);
                }
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], array('IgmGesendet' => 1)) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 142, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MESSAGES, array(
                    'senderId' => $_SESSION['blm_user'],
                    'senderName' => Database::getInstance()->getPlayerNameById($_SESSION['blm_user']),
                    'subject' => $subject,
                    'message' => $message
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 141, __LINE__);
            }
        } else {
            $receiverID = Database::getInstance()->getPlayerIDByName($receiver);
            requireEntryFound($receiverID, $base_link, 118, __LINE__);

            if ($receiverID === $_SESSION['blm_user']) {
                redirectTo($base_link, 168, __LINE__);
            }

            Database::getInstance()->begin();
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => $_SESSION['blm_user'],
                    'An' => $receiverID,
                    'Nachricht' => $message,
                    'Betreff' => $subject
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 141, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $receiverID, array('IgmEmpfangen' => 1)) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], array('IgmGesendet' => 1)) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 142, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MESSAGES, array(
                    'senderId' => $_SESSION['blm_user'],
                    'senderName' => Database::getInstance()->getPlayerNameById($_SESSION['blm_user']),
                    'receiverId' => $receiverID,
                    'receiverName' => Database::getInstance()->getPlayerNameById($receiverID),
                    'subject' => $subject,
                    'message' => $message
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($base_link, 141, __LINE__);
            }
        }

        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste', 204);
        break;

    // delete message
    case 2:
        requireXsrfToken('/?p=nachrichten_liste');
        $id = getOrDefault($_GET, 'id', 0);
        $offset_in = getOrDefault($_GET, 'o_in', 0);
        $offset_out = getOrDefault($_GET, 'o_out', 0);
        $data = Database::getInstance()->getMessageByIdAndAnOrVonEquals($id, $_SESSION['blm_user']);
        requireEntryFound($data, '/?p=nachrichten_liste');

        if ($data['Von'] == $_SESSION['blm_user'] && $data['Gelesen'] == 1) {
            redirectTo('/?p=nachrichten_liste&o_in=' . $offset_in . '&o_out=' . $offset_out, 112);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_MESSAGES, $id) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=nachrichten_liste&o_in=' . $offset_in . '&o_out=' . $offset_out, 143, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste&o_in=' . $offset_in . '&o_out=' . $offset_out, 211);
        break;

    // delete all messages
    case 3:
        requireXsrfToken('/?p=nachrichten_liste');
        Database::getInstance()->begin();
        if (Database::getInstance()->deleteAllMessagesForUser($_SESSION['blm_user']) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=nachrichten_liste', 143, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste', 212);
        break;
}
