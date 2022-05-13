<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Gruppe');

function pinMessage($id, $pinned)
{
    $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);

    if ($player['message_pin'] == 0) {
        redirectTo('/?p=gruppe', 112, __LINE__);
    }

    Database::getInstance()->begin();
    if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_MESSAGES, $id, array('Festgepinnt' => $pinned)) !== 1) {
        Database::getInstance()->rollBack();
        redirectTo('/?p=gruppe', 142, __LINE__);
    }

    Database::getInstance()->commit();
    redirectTo('/?p=gruppe', 242, __LINE__);
}

switch (getOrDefault($_REQUEST, 'a', 0)) {
    // ---------------------------------------------------------------------------------------
    // gruppe.inc.php
    // ---------------------------------------------------------------------------------------
    // create group
    case 1:
        restrictSitter('NeverAllow');

        $name = getOrDefault($_POST, 'name');
        $tag = getOrDefault($_POST, 'tag');
        $pwd = getOrDefault($_POST, 'pwd');

        // remove all control characters and trim spaces
        // https://stackoverflow.com/a/66587087
        $name = trim(preg_replace('/[^\PCc^\PCn^\PCs]/u', '', $name));
        $tag = trim(preg_replace('/[^\PCc^\PCn^\PCs]/u', '', $tag));

        if (strlen($name) == 0 || strlen($name) > group_max_name_length) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 158, __LINE__);
        }

        if (strlen($tag) == 0 || strlen($tag) > group_max_tag_length) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 159, __LINE__);
        }

        if (strlen($pwd) < password_min_len) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 147, __LINE__);
        }

        if (strchr($name, '#') !== false) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 164, __LINE__);
        }

        $player = Database::getInstance()->getPlayerNameAndPointsAndGruppeAndPlantageLevelById($_SESSION['blm_user']);
        if ($player['GruppeID'] !== null) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 157, __LINE__);
        }
        if ($player['Gebaeude1'] < min_plantage_level_create_group) {
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP, array(
                'Name' => $name,
                'Kuerzel' => $tag,
                'Passwort' => hashPassword($pwd)
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 141, __LINE__);
        }
        $gid = Database::getInstance()->lastInsertId();
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_RIGHTS, array(
                'group_id' => $gid,
                'user_id' => $_SESSION['blm_user'],
                'message_write' => 1,
                'message_pin' => 1,
                'message_delete' => 1,
                'edit_image' => 1,
                'edit_description' => 1,
                'edit_password' => 1,
                'member_kick' => 1,
                'member_rights' => 1,
                'group_diplomacy' => 1,
                'group_delete' => 1,
                'group_cash' => 1
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_CASH, array(
                'group_id' => $gid, 'user_id' => $_SESSION['blm_user'],
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 141, __LINE__);
        }
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Gruppe' => $gid), array('Gruppe IS NULL')) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $gid,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => 'Die Gruppe wurde von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' gegründet.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s&tag=%s', urlencode($name), urlencode($tag)), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe', 223, __LINE__);
        break;

    // join group
    case 2:
        restrictSitter('NeverAllow');

        $name = getOrDefault($_POST, 'name');
        $pwd = getOrDefault($_POST, 'pwd');

        $player = Database::getInstance()->getPlayerNameAndPointsAndGruppeAndPlantageLevelById($_SESSION['blm_user']);
        if ($player['GruppeID'] !== null) {
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 157, __LINE__);
        }
        if ($player['Gebaeude1'] < min_plantage_level_join_group) {
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 112, __LINE__);
        }

        $group = Database::getInstance()->getGroupIdAndPasswordByNameOrTag($name);
        requireEntryFound($group, sprintf('/?p=gruppe&name=%s', urlencode($name)), 127);

        if (!verifyPassword($pwd, $group['Passwort'])) {
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 127, __LINE__);
        }
        if (count(Database::getInstance()->getGroupMembersById($group['ID'])) >= group_max_members) {
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 140, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Gruppe' => $group['ID']), array('Gruppe IS NULL')) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_RIGHTS, array(
                'group_id' => $group['ID'],
                'user_id' => $_SESSION['blm_user'],
                'message_write' => 1,
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 141, __LINE__);
        }
        if (!Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_CASH, array(
            'group_id' => $group['ID'], 'user_id' => $_SESSION['blm_user'],
        ))) {
            if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_CASH, array(
                    'group_id' => $group['ID'],
                    'user_id' => $_SESSION['blm_user'],
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 141, __LINE__);
            }
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $group['ID'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' hat die Gruppe betreten.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&name=%s', urlencode($name)), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe', 224, __LINE__);
        break;

    // leave group
    case 3:
        restrictSitter('NeverAllow');
        requireXsrfToken('/?p=gruppe');

        Database::getInstance()->begin();
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $player['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' hat die Gruppe verlassen.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 141, __LINE__);
        }
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Gruppe' => null, 'GruppeLastMessageZeit' => null)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_RIGHTS,
                array('group_id' => $player['Gruppe'], 'user_id' => $_SESSION['blm_user'])) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_CASH,
                array('group_id' => $player['Gruppe'], 'user_id' => $_SESSION['blm_user'], 'amount' => 0)) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 142, __LINE__);
        }

        if (Database::getInstance()->getGroupMemberCountById($player['Gruppe']) == 0) {
            // player is the last member
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_einstellungen', 162, __LINE__);
        } else if (!Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_RIGHTS, array('group_id' => $player['Gruppe'], 'member_rights' => 1))) {
            // player was the last one with the right to edit the permissions
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 161, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe', 225, __LINE__);
        break;

    // write message
    case 4:
        $message = getOrDefault($_POST, 'message');
        if (strlen($message) < 4) {
            redirectTo(sprintf('/?p=gruppe&message=%s', urlencode($message)), 128, __LINE__);
        }

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['message_write'] == 0) {
            redirectTo(sprintf('/?p=gruppe&message=%s', urlencode($message)), 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_MESSAGES, array(
                'Gruppe' => $player['Gruppe'],
                'Von' => $_SESSION['blm_user'],
                'Nachricht' => $message
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe&message=%s', urlencode($message)), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe', 204, __LINE__);
        break;

    // pin message
    case 5:
        pinMessage(getOrDefault($_GET, 'id', 0), 1);
        break;

    // unpin message
    case 6:
        pinMessage(getOrDefault($_GET, 'id', 0), 0);
        break;

    // delete message
    case 7:
        $id = getOrDefault($_GET, 'id', 0);
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['message_delete'] == 0) {
            redirectTo('/?p=gruppe', 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_GROUP_MESSAGES, $id) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe', 143, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe', 242, __LINE__);
        break;

    // ---------------------------------------------------------------------------------------
    // gruppe_kasse.inc.php
    // ---------------------------------------------------------------------------------------
    // send money to member
    case 8:
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_cash'] == 0) {
            redirectTo('/?p=gruppe', 112, __LINE__);
        }

        $amount = getOrDefault($_POST, 'amount', .0);
        if ($amount <= 0) {
            redirectTo('/?p=gruppe', 110, __LINE__);
        }

        $receiver = getOrDefault($_POST, 'receiver', 0);
        $receiverName = Database::getInstance()->getPlayerNameById($receiver);
        requireEntryFound($receiverName, sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
            $receiver, $amount), 118, __LINE__);

        $group = Database::getInstance()->getGroupIdAndNameById($player['Gruppe']);
        requireEntryFound($group, sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f', $receiver, $amount), __LINE__);

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $receiver,
                array('Geld' => $amount)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
                $receiver, $amount), 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP_CASH, null,
                array('amount' => -$amount),
                array('group_id = :whr0' => $player['Gruppe'], 'user_id = :whr1' => $receiver)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
                $receiver, $amount), 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $player['Gruppe'],
                array('Kasse' => -$amount),
                array('Kasse >= :whr0' => $amount)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
                $receiver, $amount), 110, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $player['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $player['Name'])
                    . ' hat ' . formatCurrency($amount)
                    . ' an ' . ($_SESSION['blm_user'] == $receiver ? 'sich selbst' : createBBProfileLink($receiver, $receiverName)) . ' ausgezahlt.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
                $receiver, $amount), 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_GROUP_CASH, array(
                'senderId' => $_SESSION['blm_user'],
                'senderName' => $player['Name'],
                'receiverId' => $receiver,
                'receiverName' => $receiverName,
                'groupId' => $group['ID'],
                'groupName' => $group['Name'],
                'amount' => $amount
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_kasse&receiver=%d&amount=%f',
                $receiver, $amount), 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_kasse', 236, __LINE__);
        break;

    // ---------------------------------------------------------------------------------------
    // gruppe_mitgliederverwaltung.inc.php
    // ---------------------------------------------------------------------------------------
    // edit member rights
    case 9:
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['member_rights'] == 0) {
            redirectTo('/?p=gruppe', 112, __LINE__);
        }

        $user_id = getOrDefault($_POST, 'user_id', 0);
        $other_player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($user_id);
        $changes = array(
            'message_write' => getOrDefault($_POST, 'message_write', 0),
            'message_pin' => getOrDefault($_POST, 'message_pin', 0),
            'message_delete' => getOrDefault($_POST, 'message_delete', 0),
            'edit_description' => getOrDefault($_POST, 'edit_description', 0),
            'edit_password' => getOrDefault($_POST, 'edit_password', 0),
            'edit_image' => getOrDefault($_POST, 'edit_image', 0),
            'member_rights' => getOrDefault($_POST, 'member_rights', 0),
            'member_kick' => getOrDefault($_POST, 'member_kick', 0),
            'group_cash' => getOrDefault($_POST, 'group_cash', 0),
            'group_diplomacy' => getOrDefault($_POST, 'group_diplomacy', 0),
            'group_delete' => getOrDefault($_POST, 'group_delete', 0),
        );

        if ($user_id == $_SESSION['blm_user']) {
            redirectTo('/?p=gruppe_mitgliederverwaltung', 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_RIGHTS, null,
                $changes, array('group_id = :whr0' => $player['Gruppe'], 'user_id = :whr1' => $user_id)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $player['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' hat die Rechte von '
                    . createBBProfileLink($user_id, $other_player['Name']) . ' geändert.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_mitgliederverwaltung', 226, __LINE__);
        break;

    // kick member
    case 10:
        requireXsrfToken('/?p=gruppe_mitgliederverwaltung');

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['member_rights'] == 0) {
            redirectTo('/?p=gruppe_mitgliederverwaltung', 112, __LINE__);
        }

        $user_id = getOrDefault($_GET, 'user_id', 0);
        if ($user_id == $_SESSION['blm_user']) {
            redirectTo('/?p=gruppe_mitgliederverwaltung', 112, __LINE__);
        }
        $other_player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($user_id);

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $user_id,
                array('Gruppe' => null), array('Gruppe = :whr0' => $player['Gruppe'])) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_RIGHTS,
                array('user_id' => $user_id, 'group_id' => $player['Gruppe'])) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 143, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_CASH,
                array('user_id' => $user_id, 'group_id' => $player['Gruppe'], 'amount' => 0)) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 143, __LINE__);
        }
        if (!Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_RIGHTS, array('group_id' => $player['Gruppe'], 'member_rights' => 1))) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 161, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $player['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' hat '
                    . createBBProfileLink($user_id, $other_player['Name']) . ' aus der Gruppe entfernt.'
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_mitgliederverwaltung', 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_mitgliederverwaltung', 227, __LINE__);
        break;

    // ---------------------------------------------------------------------------------------
    // gruppe_einstellungen.inc.php
    // ---------------------------------------------------------------------------------------
    // edit group image
    case 11:
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['edit_image'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        if (filesize($_FILES['bild']['tmp_name']) > max_profile_image_size) {
            redirectTo('/?p=gruppe_einstellungen', 103, __LINE__);
        }

        @unlink(sprintf('../pics/uploads/g_%s.jpg', $player['Gruppe']));
        @unlink(sprintf('../pics/uploads/g_%s.png', $player['Gruppe']));
        @unlink(sprintf('../pics/uploads/g_%s.gif', $player['Gruppe']));
        @unlink(sprintf('../pics/uploads/g_%s.webp', $player['Gruppe']));
        if ($_FILES['bild']['size'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 209, __LINE__);
        }

        $typ = $_FILES['bild']['type'];
        $suffix = 'dat';
        switch ($typ) {
            case 'image/jpeg':
            case 'image/jpg':
                $suffix = 'jpg';
                break;
            case 'image/gif':
                $suffix = 'gif';
                break;
            case 'image/png':
                $suffix = 'png';
                break;
            case 'image/webp':
                $suffix = 'webp';
                break;
            default:
                redirectTo('/?p=gruppe_einstellungen', 107, __LINE__);
                break;
        }
        move_uploaded_file($_FILES['bild']['tmp_name'], sprintf('../pics/uploads/g_%s.%s', $player['Gruppe'], $suffix));
        redirectTo('/?p=gruppe_einstellungen', 210, __LINE__);
        break;

    // edit group description
    case 12:
        $beschreibung = getOrDefault($_POST, 'beschreibung');
        if (strlen($beschreibung) == 0) $beschreibung = null;
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['edit_description'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP, $player['Gruppe'], array('Beschreibung' => $beschreibung)) === null) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=gruppe_einstellungen&beschreibung=%s', urlencode($beschreibung)), 143, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_einstellungen', 206, __LINE__);
        break;

    // edit group password
    case 13:
        $new_pw1 = getOrDefault($_POST, 'new_pw1');
        $new_pw2 = getOrDefault($_POST, 'new_pw2');

        if ($new_pw1 != $new_pw2) {
            redirectTo('/?p=gruppe_einstellungen', 105, __LINE__);
        }
        if (strlen($new_pw1) < password_min_len) {
            redirectTo('/?p=gruppe_einstellungen', 147, __LINE__);
        }

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['edit_password'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP, $player['Gruppe'], array('Passwort' => hashPassword($new_pw1))) === null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_einstellungen', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_einstellungen', 219, __LINE__);
        break;

    // delete group
    case 14:
        restrictSitter('NeverAllow');
        requireXsrfToken('/?p=gruppe_einstellungen');

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_delete'] == 0 && Database::getInstance()->getGroupMemberCountById($player['Gruppe']) > 1) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        $confirm = getOrDefault($_POST, 'confirm', 0);
        if ($confirm != $player['Gruppe']) {
            redirectTo('/?p=gruppe_einstellungen', 130, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_DIPLOMACY, array('Von' => $player['Gruppe']))
            || Database::getInstance()->existsTableEntry(Database::TABLE_GROUP_DIPLOMACY, array('An' => $player['Gruppe']))) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_einstellungen', 163, __LINE__);
        }
        $status = Database::getInstance()->deleteGroup($player['Gruppe']);
        if ($status !== null) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_einstellungen', 143, __LINE__ . '_' . $status);
        }

        Database::getInstance()->commit();
        @unlink(sprintf("../pics/uploads/g_%d.jpg", $player['Gruppe']));
        @unlink(sprintf("../pics/uploads/g_%d.png", $player['Gruppe']));
        @unlink(sprintf("../pics/uploads/g_%d.gif", $player['Gruppe']));
        redirectTo('/?p=gruppe', 228, __LINE__);
        break;

    // ---------------------------------------------------------------------------------------
    // gruppe_diplomatie.inc.php
    // ---------------------------------------------------------------------------------------
    // cancel group diplomacy
    case 15:
        requireXsrfToken('/?p=gruppe_diplomatie');

        $id = getOrDefault($_GET, 'id', 0);
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        $diplomacy = Database::getInstance()->getGroupDiplomacyById($id);
        requireEntryFound($diplomacy, '/?p=gruppe_diplomatie');
        if ($diplomacy['Typ'] === group_diplomacy_war) {
            redirectTo('/?p=gruppe_diplomatie', 112, __LINE__);
        }
        $usId = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $usName = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);
        $themId = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $themName = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);

        if (strtotime($diplomacy['Seit']) + 60 * 60 * 24 * group_diplomacy_min_duration > time()) {
            redirectTo('/?p=gruppe_diplomatie', 167, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_DIPLOMACY, array('ID' => $id, 'Aktiv' => 1)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 143, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $usId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat die diplomatische Beziehung (%s) mit %s aufgekündigt',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($themId, $themName))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $themId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Unsere diplomatische Beziehung (%s) mit %s wurde von %s aufgekündigt',
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($usId, $usName),
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 230, __LINE__);
        break;

    // retract group diplomacy request
    case 16:
        requireXsrfToken('/?p=gruppe_diplomatie');

        $id = getOrDefault($_GET, 'id', 0);
        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        $diplomacy = Database::getInstance()->getGroupDiplomacyById($id);
        requireEntryFound($diplomacy, '/?p=gruppe_diplomatie');
        $usId = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $usName = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);
        $themId = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $themName = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);

        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntryWhere(Database::TABLE_GROUP_DIPLOMACY, array('ID' => $id, 'Aktiv' => 0)) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 143, __LINE__);
        }
        if ($diplomacy['Betrag'] !== null) {
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $player['Gruppe'], array('Kasse' => $diplomacy['Betrag'])) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=gruppe_diplomatie', 142, __LINE__);
            }
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $player['Gruppe'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat die diplomatische Anfrage (%s) an %s zurückgezogen',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($themId, $themName))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $themId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Die diplomatische Anfrage (%s) von %s wurde durch %s zurückgezogen',
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($usId, $usName),
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 230, __LINE__);
        break;

    // surrender war
    case 17:
        requireXsrfToken('/?p=gruppe_diplomatie');
        $id = getOrDefault($_GET, 'id', 0);

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo('/?p=gruppe_einstellungen', 112, __LINE__);
        }

        $diplomacy = Database::getInstance()->getGroupDiplomacyById($id);
        requireEntryFound($diplomacy, '/?p=gruppe_diplomatie');
        if ($diplomacy['Typ'] != group_diplomacy_war) {
            redirectTo('/?p=gruppe_diplomatie', 112, __LINE__);
        }
        $usId = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $usName = ($diplomacy['GruppeAnId'] == $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);
        $themId = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnId'] : $diplomacy['GruppeVonId']);
        $themName = ($diplomacy['GruppeAnId'] != $player['Gruppe'] ? $diplomacy['GruppeAnName'] : $diplomacy['GruppeVonName']);

        Database::getInstance()->begin();
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_GROUP_DIPLOMACY, $id) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 143, __LINE__);
        }

        // update the loser stuff
        $allMembers = Database::getInstance()->getGroupMembersById($usId);
        foreach ($allMembers as $member) {
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $member['ID'],
                    array('Gebaeude1' => -group_war_loose_plantage,
                        'Punkte' => -(group_war_loose_points * $member['Punkte'])),
                    array('Gebaeude1 >= :whr0' => group_war_loose_plantage)) === null) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=gruppe_diplomatie', 142, __LINE__ . '_g' . $member['ID']);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                    array('KriegMinus' => (group_war_loose_points * $member['Punkte'])),
                    array('user_id = :whr0' => $member['ID'])) === null) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=gruppe_diplomatie', 142, __LINE__ . '_p' . $member['ID']);
            }
        }

        // update winner cash
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $themId,
                array('Kasse' => 2 * $diplomacy['Betrag'])) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 142, __LINE__);
        }

        // create NAP
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_DIPLOMACY, array(
                'Von' => $usId,
                'An' => $themId,
                'Typ' => group_diplomacy_nap,
                'Aktiv' => 1
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $usId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat im Krieg mit %s die Kapitulation verkündet. Alle Mitglieder der Gruppe verlieren %d Level ihrer Plantage und %s ihrer Punkte. Der umkämpfte Betrag von %s geht komplett an den Gegner.',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    createBBGroupLink($themId, $themName),
                    group_war_loose_plantage,
                    formatPercent(group_war_loose_points),
                    formatCurrency(2 * $diplomacy['Betrag']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $themId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Wir waren siegreich! %s hat im Namen unser Gegner %s im Krieg gegen uns kapituliert! Wir haben %s als Beute gewonnen.',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    createBBGroupLink($usId, $usName),
                    formatCurrency(2 * $diplomacy['Betrag']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 230, __LINE__);
        break;

    // send diplomacy request
    case 18:
        $typ = getOrDefault($_POST, 'typ', 0);
        $group = getOrDefault($_POST, 'group');
        $amount = getOrDefault($_POST, 'amount', .0);
        $backLink = sprintf('/?p=gruppe_diplomatie&gruppe=%s&typ=%d&amount=%d', urlencode($group), $typ, $amount);

        if ($typ !== group_diplomacy_nap && $typ != group_diplomacy_bnd && $typ !== group_diplomacy_war) {
            redirectTo($backLink, 112, __LINE__);
        }

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo($backLink, 112, __LINE__);
        }

        $us = Database::getInstance()->getGroupIdAndNameById($player['Gruppe']);
        requireEntryFound($us, $backLink);
        $them = Database::getInstance()->getGroupIdAndNameByNameOrTag($group);
        requireEntryFound($them, $backLink);
        if (Database::getInstance()->getGroupDiplomacyTypeById($us['ID'], $them['ID']) !== null) {
            redirectTo($backLink, 129, __LINE__);
        }
        if ($us['ID'] === $them['ID']) {
            redirectTo($backLink, 165, __LINE__);
        }

        Database::getInstance()->begin();
        if ($typ == group_diplomacy_war) {
            if ($amount < group_war_min_amount) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 132, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $us['ID'],
                    array('Kasse' => -$amount), array('Kasse >= :whr0' => $amount)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 166, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_DIPLOMACY, array(
                    'Von' => $us['ID'],
                    'An' => $them['ID'],
                    'Typ' => $typ,
                    'Betrag' => $amount
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_DIPLOMACY, array(
                    'Von' => $us['ID'],
                    'An' => $them['ID'],
                    'Typ' => $typ
                )) !== 1) {
                Database::getInstance()->rollBack();
                redirectTo($backLink, 141, __LINE__);
            }
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $us['ID'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat eine neue diplomatische Anfrage (%s) an %s gesendet',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    getGroupDiplomacyTypeName($typ),
                    createBBGroupLink($them['ID'], $them['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo($backLink, 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $them['ID'],
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Eine neue diplomatische Anfrage (%s) mit %s wurde von %s empfangen',
                    getGroupDiplomacyTypeName($typ),
                    createBBGroupLink($them['ID'], $them['Name']),
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo($backLink, 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 229);
        break;

    // accept diplomacy request
    case 19:
        $id = getOrDefault($_GET, 'id', 0);

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo('/?p=gruppe_diplomatie', 112, __LINE__);
        }
        $diplomacy = Database::getInstance()->getGroupDiplomacyByIdAndAn($id, $player['Gruppe']);
        requireEntryFound($diplomacy, '/?p=gruppe_diplomatie');

        $usId = $diplomacy['GruppeAnId'];
        $usName = $diplomacy['GruppeAnName'];
        $themId = $diplomacy['GruppeVonId'];
        $themName = $diplomacy['GruppeVonName'];

        Database::getInstance()->begin();
        if ($diplomacy['Typ'] === group_diplomacy_war) {
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $usId,
                    array('Kasse' => -$diplomacy['Betrag']), array('Kasse >= :whr0' => $diplomacy['Betrag'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo('/?p=gruppe_diplomatie', 166, __LINE__);
            }
        }

        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_DIPLOMACY, $id, array('Aktiv' => 1)) !== 1) {
            Database::getInstance()->rollback();
            redirectTo('/?p=gruppe_diplomatie', 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $usId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat die diplomatische Anfrage (%s) von %s angenommen',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($themId, $themName))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $themId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Die diplomatische Anfrage (%s) an %s wurde von %s angenommen',
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($usId, $usName),
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 231);
        break;

    // reject diplomacy request
    case 20:
        $id = getOrDefault($_GET, 'id', 0);

        $player = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        if ($player['group_diplomacy'] == 0) {
            redirectTo('/?p=gruppe_diplomatie', 112, __LINE__);
        }
        $diplomacy = Database::getInstance()->getGroupDiplomacyByIdAndAn($id, $player['Gruppe']);
        requireEntryFound($diplomacy, '/?p=gruppe_diplomatie');

        $usId = $diplomacy['GruppeAnId'];
        $usName = $diplomacy['GruppeAnName'];
        $themId = $diplomacy['GruppeVonId'];
        $themName = $diplomacy['GruppeVonName'];

        Database::getInstance()->begin();
        if ($diplomacy['Typ'] == group_diplomacy_war) {
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_GROUP, $themId, array('Kasse' => $diplomacy['Betrag'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo('/?p=gruppe_diplomatie', 111, __LINE__);
            }
        }

        if (Database::getInstance()->updateTableEntry(Database::TABLE_GROUP_DIPLOMACY, $id, array('Aktiv' => 1)) !== 1) {
            Database::getInstance()->rollback();
            redirectTo('/?p=gruppe_diplomatie', 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $usId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('%s hat die diplomatische Anfrage (%s) von %s abgelehnt',
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']),
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($themId, $themName))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_GROUP_LOG, array(
                'Gruppe' => $themId,
                'Spieler' => $_SESSION['blm_user'],
                'Text' => sprintf('Die diplomatische Anfrage (%s) an %s wurde von %s abgelehnt',
                    getGroupDiplomacyTypeName($diplomacy['Typ']),
                    createBBGroupLink($usId, $usName),
                    createBBProfileLink($_SESSION['blm_user'], $player['Name']))
            )) !== 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=gruppe_diplomatie', 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=gruppe_diplomatie', 216);
        break;

    // unknown action
    default:
        redirectTo(' /?p=gruppe', 112, __LINE__);
        break;
}
