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
requireAdmin();

$id = getOrDefault($_REQUEST, 'id', 0);
$offset = getOrDefault($_REQUEST, 'o', 0);

$backlink = sprintf('/?p=admin_gruppe_bearbeiten&id=%d&o=%d', $id, $offset);
switch (getOrDefault($_REQUEST, 'a', 0)) {
    // update basic information
    case 1:
        $name = getOrDefault($_POST, 'name');
        $kuerzel = getOrDefault($_POST, 'kuerzel');
        $password = getOrDefault($_POST, 'password');
        $beschreibung = getOrDefault($_POST, 'beschreibung');
        $kasse = getOrDefault($_POST, 'kasse', 0.0);
        $backlink .= sprintf('&name=%s&kuerzel=%s&beschreibung=%d&kasse=%f', urlencode($name), urlencode($kuerzel), urlencode($beschreibung), $kasse);

        Database::getInstance()->begin();
        $fields = array(
            'Name' => $name,
            'Kuerzel' => $kuerzel,
            'Beschreibung' => $beschreibung,
            'Kasse' => $kasse,
        );
        if ($password !== null && strlen($password) > 0) {
            $fields['Passwort'] = hashPassword($password);
        }

        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP, $id, $fields) === null) {
            Database::getInstance()->rollBack();
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_gruppe_bearbeiten&id=' . $id . '&o=' . $offset, 248);
        }
        break;

    // edit group cash for user
    case 2:
        $user_id = getOrDefault($_POST, 'user_id', 0);
        $amount = getOrDefault($_POST, 'amount', 0.0);
        Database::getInstance()->begin();

        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_CASH, null,
                array('amount' => $amount), array('group_id = :whr0' => $id, 'user_id = :whr1' => $user_id)) === null) {
            Database::getInstance()->rollBack();
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_gruppe_bearbeiten&id=' . $id . '&o=' . $offset, 248);
        }
        break;

    // edit group rights for user
    case 3:
        $user_id = getOrDefault($_POST, 'user_id', 0);
        $message_write = getOrDefault($_POST, 'message_write', 0);
        $message_pin = getOrDefault($_POST, 'message_pin', 0);
        $message_delete = getOrDefault($_POST, 'message_delete', 0);
        $edit_description = getOrDefault($_POST, 'edit_description', 0);
        $edit_image = getOrDefault($_POST, 'edit_image', 0);
        $edit_password = getOrDefault($_POST, 'edit_password', 0);
        $member_rights = getOrDefault($_POST, 'member_rights', 0);
        $member_kick = getOrDefault($_POST, 'member_kick', 0);
        $group_cash = getOrDefault($_POST, 'group_cash', 0);
        $group_diplomacy = getOrDefault($_POST, 'group_diplomacy', 0);
        $group_delete = getOrDefault($_POST, 'group_delete', 0);
        Database::getInstance()->begin();
        $fields = array(
            'message_write' => $message_write,
            'message_pin' => $message_pin,
            'message_delete' => $message_delete,
            'edit_description' => $edit_description,
            'edit_image' => $edit_image,
            'edit_password' => $edit_password,
            'member_rights' => $member_rights,
            'member_kick' => $member_kick,
            'group_cash' => $group_cash,
            'group_diplomacy' => $group_diplomacy,
            'group_delete' => $group_delete,
        );

        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_RIGHTS, null,
                $fields, array('group_id = :whr0' => $id, 'user_id = :whr1' => $user_id)) === null) {
            Database::getInstance()->rollBack();
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_gruppe_bearbeiten&id=' . $id . '&o=' . $offset, 248);
        }
        break;

    // delete group
    case 4:
        requireXsrfToken($backlink);
        Database::getInstance()->begin();
        $status = Database::getInstance()->deleteGroup($id);
        if ($status !== null) {
            Database::getInstance()->rollBack();
            redirectTo($backlink, 143, __LINE__ . '_' . $status);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_gruppe&o=' . $offset, 228);
        }
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_gruppe_bearbeiten&id=' . $id . '&o=' . $offset, 112, __LINE__);
        break;
}
