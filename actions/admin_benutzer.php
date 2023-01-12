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
requireAdmin();

$id = getOrDefault($_REQUEST, 'id', 0);
$offset = getOrDefault($_REQUEST, 'o', 0);

// first form, basic data
$username = getOrDefault($_POST, 'username');
$email = getOrDefault($_POST, 'email');
$password = getOrDefault($_POST, 'password');
$email_aktiviert = getOrDefault($_POST, 'email_aktiviert', 1);
$geld = getOrDefault($_POST, 'geld', .0);
$bank = getOrDefault($_POST, 'bank', .0);
$punkte = getOrDefault($_POST, 'punkte', 0);
$igm_gesendet = getOrDefault($_POST, 'igm_gesendet', 0);
$igm_empfangen = getOrDefault($_POST, 'igm_empfangen', 0);
$admin = getOrDefault($_POST, 'admin', 0);
$betatester = getOrDefault($_POST, 'betatester', 0);
$ewige_punkte = getOrDefault($_POST, 'ewige_punkte', 0);
$onlinezeit = getOrDefault($_POST, 'onlinezeit', 0);
$gruppe = getOrDefault($_POST, 'gruppe', -1);
$verwarnungen = getOrDefault($_POST, 'verwarnungen', 0);
$gesperrt = getOrDefault($_POST, 'gesperrt', 0);

// second form, building levels
$gebaeude = array();
for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_buildings'); $i++) {
    $gebaeude[$i] = getOrDefault($_POST, 'gebaeude_' . $i, 0);
}

// third form, research levels
$forschung = array();
for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
    $forschung[$i] = getOrDefault($_POST, 'forschung_' . $i, 0);
}

// fourth form, stock
$lager = array();
for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
    $lager[$i] = getOrDefault($_POST, 'lager_' . $i, 0);
}

$backlink = sprintf('/?p=admin_benutzer_bearbeiten&id=' . $id . '_bearbeiten&id=%d&o=%d', $id, $offset);
switch (getOrDefault($_REQUEST, 'a', 0)) {
    // update basic information
    case 1:
        $backlink .= sprintf('&username=%s&email=%s&email_aktiviert=%d&geld=%f&bank=%f&punkte=%d&igm_gesendet=%d&igm_empfangen=%d&admin=%d&betatester=%d&ewige_punkte=%d&onlinezeit=%d&gruppe=%d&verwarnungen=%d&gesperrt=%d',
            $username, $email, $email_aktiviert, $geld, $bank, $punkte, $igm_gesendet, $igm_empfangen,
            $admin, $betatester, $ewige_punkte, $onlinezeit, $gruppe, $verwarnungen, $gesperrt);

        Database::getInstance()->begin();
        $fields = array(
            'Name' => $username,
            'EMail' => $email,
            'EMailAct' => $email_aktiviert === 1 ? null : createRandomCode(),
            'Geld' => $geld,
            'Bank' => $bank,
            'Punkte' => $punkte,
            'IgmGesendet' => $igm_gesendet,
            'IgmEmpfangen' => $igm_empfangen,
            'Admin' => $admin,
            'Betatester' => $betatester,
            'EwigePunkte' => $ewige_punkte,
            'OnlineZeit' => $onlinezeit,
            'Verwarnungen' => $verwarnungen,
            'Gesperrt' => $gesperrt
        );
        if ($password !== null && strlen($password) > 0) {
            $fields['Passwort'] = hashPassword($password);
        }

        // handle group update
        if ($gruppe === -1) $gruppe = null;
        $data = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($id);
        if ($data['Gruppe'] === null || $data['Gruppe'] != $gruppe) {
            $fields['Gruppe'] = $gruppe;

            // group changed, leave old group (if present)
            if ($data['Gruppe'] !== null) {
                if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_RIGHTS,
                        array('group_id' => $data['Gruppe'], 'user_id' => $id)) !== 1) {
                    Database::getInstance()->rollBack();
                    redirectTo($backlink, 142, __LINE__);
                }
                if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_CASH,
                        array('group_id' => $data['Gruppe'], 'user_id' => $id, 'amount' => 0)) === null) {
                    Database::getInstance()->rollBack();
                    redirectTo($backlink, 142, __LINE__);
                }
            }

            // join new group
            if ($gruppe !== null) {
                // create group rights entry
                if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_RIGHTS,
                        array('group_id' => $gruppe, 'user_id' => $id, 'message_write' => 1,)) !== 1) {
                    Database::getInstance()->rollBack();
                    redirectTo($backlink, 141, __LINE__);
                }

                // create group cash entry
                if (!Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_CASH,
                    array('group_id' => $gruppe, 'user_id' => $id,))) {
                    if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_CASH,
                            array('group_id' => $gruppe, 'user_id' => $id,)) !== 1) {
                        Database::getInstance()->rollBack();
                        redirectTo($backlink, 141, __LINE__);
                    }
                }
            }
        }

        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update building levels
    case 2:
        $fields = array();
        for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_buildings'); $i++) {
            $backlink .= sprintf('&gebaeude_%d=%d', $i, $gebaeude[$i]);
            $fields['Gebaeude' . $i] = $gebaeude[$i];
        }
        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update research levels
    case 3:
        $fields = array();
        for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
            $backlink .= sprintf('&forschung_%d=%d', $i, $forschung[$i]);
            $fields['Forschung' . $i] = $forschung[$i];
        }
        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update stock
    case 4:
        $fields = array();
        for ($i = 1; $i <= Config::getInt(Config::SECTION_BASE, 'count_wares'); $i++) {
            $backlink .= sprintf('&lager_%d=%d', $i, $lager[$i]);
            $fields['Lager' . $i] = $lager[$i];
        }
        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            Database::getInstance()->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // delete user
    case 5:
        requireXsrfToken('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset);
        Database::getInstance()->begin();
        $status = deleteAccount($id);
        if ($status !== null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 143, __LINE__ . '_' . $status);
        }
        Database::getInstance()->commit();

        redirectTo('/?p=admin_benutzer&id=' . $id . '&o=' . $offset, 246);
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_benutzer&id=' . $id . '&o=' . $offset, 112, __LINE__);
        break;
}
