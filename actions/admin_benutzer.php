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

function loadAndVerifyFromExport(ZipArchive $zip, string $name, bool $verify): ?array
{
    $data = $zip->getFromName($name, Config::getInt(Config::SECTION_BASE, 'import_max_table_size'));
    if ($data === false) {
        redirectTo('/?p=admin_benutzer_importieren', 174, __LINE__ . '_' . $name);
    }
    if ($verify) {
        $hmac = $zip->getFromName($name . '.hmac');
        if (getHmac($data) !== $hmac) {
            redirectTo('/?p=admin_benutzer_importieren', 176, __LINE__ . '_' . $name . '.hmac');
        }
    }
    return json_decode($data, true);
}

$id = getOrDefault($_REQUEST, 'id', 0);
$offset = getOrDefault($_REQUEST, 'o', 0);

$backlink = sprintf('/?p=admin_benutzer_bearbeiten&id=%d&o=%d', $id, $offset);
$db = Database::getInstance();
switch (getOrDefault($_REQUEST, 'a', 0)) {
    // update basic information
    case 1:
        $username = getOrDefault($_POST, 'username');
        $email = getOrDefault($_POST, 'email');
        $password = getOrDefault($_POST, 'password');
        $email_aktiviert = getOrDefault($_POST, 'email_aktiviert', 1);
        $geld = getOrDefault($_POST, 'geld', .0);
        $bank = getOrDefault($_POST, 'bank', .0);
        $igm_gesendet = getOrDefault($_POST, 'igm_gesendet', 0);
        $igm_empfangen = getOrDefault($_POST, 'igm_empfangen', 0);
        $admin = getOrDefault($_POST, 'admin', 0);
        $betatester = getOrDefault($_POST, 'betatester', 0);
        $ewige_punkte = getOrDefault($_POST, 'ewige_punkte', 0);
        $onlinezeit = getOrDefault($_POST, 'onlinezeit', 0);
        $gruppe = getOrDefault($_POST, 'gruppe', -1);
        $verwarnungen = getOrDefault($_POST, 'verwarnungen', 0);
        $gesperrt = getOrDefault($_POST, 'gesperrt', 0);

        $backlink .= sprintf('&username=%s&email=%s&email_aktiviert=%d&geld=%f&bank=%f&igm_gesendet=%d&igm_empfangen=%d&admin=%d&betatester=%d&ewige_punkte=%d&onlinezeit=%d&gruppe=%d&verwarnungen=%d&gesperrt=%d',
            urlencode($username), urlencode($email), $email_aktiviert, $geld, $bank, $igm_gesendet, $igm_empfangen,
            $admin, $betatester, $ewige_punkte, $onlinezeit, $gruppe, $verwarnungen, $gesperrt);

        $db->begin();
        $fields = array(
            'Name' => $username,
            'EMail' => $email,
            'EMailAct' => $email_aktiviert === 1 ? null : createRandomCode(),
            'Geld' => $geld,
            'Bank' => $bank,
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
        $data = $db->getPlayerNameAndGroupIdAndGroupRightsById($id);
        if ($data['Gruppe'] === null || $data['Gruppe'] != $gruppe) {
            $fields['Gruppe'] = $gruppe;

            // group changed, leave old group (if present)
            if ($data['Gruppe'] !== null) {
                if ($db->deleteTableEntryWhere(Database::TABLE_GROUP_RIGHTS,
                        array('group_id' => $data['Gruppe'], 'user_id' => $id)) !== 1) {
                    $db->rollBack();
                    redirectTo($backlink, 142, __LINE__);
                }
                if ($db->deleteTableEntryWhere(Database::TABLE_GROUP_CASH,
                        array('group_id' => $data['Gruppe'], 'user_id' => $id, 'amount' => 0)) === null) {
                    $db->rollBack();
                    redirectTo($backlink, 142, __LINE__);
                }
            }

            // join new group
            if ($gruppe !== null) {
                // create group rights entry
                if ($db->createTableEntry(Database::TABLE_GROUP_RIGHTS,
                        array('group_id' => $gruppe, 'user_id' => $id, 'message_write' => 1,)) !== 1) {
                    $db->rollBack();
                    redirectTo($backlink, 141, __LINE__);
                }

                // create group cash entry
                if (!$db->existsTableEntry(Database::TABLE_GROUP_CASH,
                    array('group_id' => $gruppe, 'user_id' => $id,))) {
                    if ($db->createTableEntry(Database::TABLE_GROUP_CASH,
                            array('group_id' => $gruppe, 'user_id' => $id,)) !== 1) {
                        $db->rollBack();
                        redirectTo($backlink, 141, __LINE__);
                    }
                }
            }
        }

        if ($db->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            $db->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update building levels
    case 2:
        $gebaeude = array();
        for ($i = 1; $i <= count_buildings; $i++) {
            $gebaeude[$i] = getOrDefault($_POST, 'gebaeude_' . $i, 0);
        }
        $fields = array();
        for ($i = 1; $i <= count_buildings; $i++) {
            $backlink .= sprintf('&gebaeude_%d=%d', $i, $gebaeude[$i]);
            $fields['Gebaeude' . $i] = $gebaeude[$i];
        }
        $db->begin();
        if ($db->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            $db->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update research levels
    case 3:
        $forschung = array();
        for ($i = 1; $i <= count_wares; $i++) {
            $forschung[$i] = getOrDefault($_POST, 'forschung_' . $i, 0);
        }
        $fields = array();
        for ($i = 1; $i <= count_wares; $i++) {
            $backlink .= sprintf('&forschung_%d=%d', $i, $forschung[$i]);
            $fields['Forschung' . $i] = $forschung[$i];
        }
        $db->begin();
        if ($db->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            $db->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // update stock
    case 4:
        $lager = array();
        for ($i = 1; $i <= count_wares; $i++) {
            $lager[$i] = getOrDefault($_POST, 'lager_' . $i, 0);
        }
        $fields = array();
        for ($i = 1; $i <= count_wares; $i++) {
            $backlink .= sprintf('&lager_%d=%d', $i, $lager[$i]);
            $fields['Lager' . $i] = $lager[$i];
        }
        $db->begin();
        if ($db->updateTableEntry(Database::TABLE_USERS, $id, $fields) === null) {
            redirectTo($backlink, 142, __LINE__);
        } else {
            $db->commit();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 247);
        }
        break;

    // delete user
    case 5:
        requireXsrfToken('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset);
        $db->begin();
        $status = deleteAccount($id);
        if ($status !== null) {
            $db->rollBack();
            redirectTo('/?p=admin_benutzer_bearbeiten&id=' . $id . '&o=' . $offset, 143, __LINE__ . '_' . $status);
        }
        $db->commit();

        redirectTo('/?p=admin_benutzer&id=' . $id . '&o=' . $offset, 246);
        break;

    // import user
    case 6:
        requireXsrfToken('/?p=admin_benutzer_importieren');
        $verify = getOrDefault($_POST, 'verify', true);
        $new_id = getOrDefault($_POST, 'new_id', false);
        $ignore_round = getOrDefault($_POST, 'ignore_round', false);
        $with_logs = getOrDefault($_POST, 'with_logs', false);
        $ignore_metadata = getOrDefault($_POST, 'ignore_metadata', false);

        if ($ignore_metadata) {
            $ignore_round = true;
            $verify = false;
        }

        $zip = new ZipArchive();
        if ($zip->open($_FILES['import']['tmp_name']) !== true) {
            redirectTo('/?p=admin_benutzer_importieren', 174, __LINE__);
        }

        // load metadata
        if ($ignore_metadata) {
            $user = loadAndVerifyFromExport($zip, 'mitglieder.json', $verify);
            $metadata = array('user_id' => intval($user[0]['ID']));
        } else {
            $metadata = loadAndVerifyFromExport($zip, 'metadata.json', $verify);
        }

        // verify round
        if (!$ignore_round && $metadata['round_start'] !== formatDateTime(Config::getInt(Config::SECTION_DBCONF, 'roundstart'))) {
            redirectTo('/?p=admin_benutzer_importieren', 175, __LINE__);
        }

        // generate new id
        $user_id = $metadata['user_id'];
        $db->begin();
        if ($new_id) {
            $name = 'i' . time();
            if (($user_id = $db->createUser($name, $name . '@localhost', createRandomCode(), createRandomCode())) === null) {
                $db->rollBack();
                redirectTo('/?p=admin_benutzer_importieren', 141, __LINE__);
            }
            $status = deleteAccount($user_id);
            if ($status !== null) {
                $db->rollBack();
                redirectTo('/?p=admin_benutzer_importieren', 141, __LINE__ . '_' . $status);
            }
        }

        // import all tables
        $tables = getTablesForGdprExport();
        foreach ($tables as $table => $fields) {
            if (!$with_logs && strncmp($table, 'log_', 4) === 0) {
                // do not import log-tables
                continue;
            }
            $rows = loadAndVerifyFromExport($zip, $table . '.json', $verify);

            // update userId
            if ($new_id) {
                foreach ($rows as $i => $row) {
                    if (is_array($fields)) {
                        foreach ($fields as $field) {
                            if (intval($row[$field]) === $metadata['user_id']) {
                                $rows[$i][$field] = $user_id;
                            }
                        }
                    } else {
                        if (intval($row[$fields]) === $metadata['user_id']) {
                            $rows[$i][$fields] = $user_id;
                        }
                    }
                }
            }

            // generate new IDs
            if ($table !== Database::TABLE_USERS) {
                foreach ($rows as $i => $row) {
                    if (array_key_exists('ID', $row)) {
                        unset($rows[$i]['ID']);
                    }
                    if (array_key_exists('id', $row)) {
                        unset($rows[$i]['id']);
                    }
                }
            }

            // do the import
            foreach ($rows as $i => $row) {
                if ($db->createTableEntry($table, $row) === null) {
                    $db->rollBack();
                    if ($table === Database::TABLE_USERS) {
                        redirectTo('/?p=admin_benutzer_importieren', 106, __LINE__);
                    } else {
                        redirectTo('/?p=admin_benutzer_importieren', 141, __LINE__ . '_' . $table . '_' . $i);
                    }
                }
            }
        }

        // remove user from group if it no longer exists
        $playerData = $db->getPlayerNameAndGroupIdAndGroupRightsById($user_id);
        if ($playerData['Gruppe'] !== null && $db->getGroupInformationById($playerData['Gruppe']) === null) {
            $status = $db->deleteGroup($playerData['Gruppe']);
            if ($status !== null) {
                $db->rollBack();
                redirectTo('/?p=admin_benutzer_importieren', 143, __LINE__ . '_' . $status);
            }
        }

        $db->commit();
        redirectTo('/?p=admin_benutzer_importieren', 249);
        break;

    // unknown action
    default:
        redirectBack('/?p=admin_benutzer&id=' . $id . '&o=' . $offset, 112, __LINE__);
        break;
}
