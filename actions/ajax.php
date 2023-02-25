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

$db = Database::getInstance();
switch (getOrDefault($_POST, 'a', 0)) {
    // delete message
    case 1:
        requireLogin();
        restrictSitter('Nachrichten');
        requireXsrfToken('/?p=nachrichten_liste');
        $id = getOrDefault($_POST, 'id', 0);
        $data = $db->getMessageByIdAndAnOrVonEquals($id, $_SESSION['blm_user']);

        // verify that the message exists
        if ($data === null || (is_array($data) && count($data) == 0)) {
            http_response_code(404);
            die();
        }

        // only allow deletion of outgoing messages if the receiver hasn't read the message yet
        if ($data['Von'] == $_SESSION['blm_user'] && $data['Gelesen'] == 1) {
            http_response_code(401);
        }

        // delete the message
        $db->begin();
        if ($db->deleteTableEntry(Database::TABLE_MESSAGES, $id) === null) {
            $db->rollBack();
            http_response_code(500);
        }
        $db->commit();
        break;
}
