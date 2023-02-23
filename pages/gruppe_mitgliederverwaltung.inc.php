<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Gruppe');

$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Community_Help.webp" alt=""/>
    <span>Gruppe - Mitgliederverwaltung<?= createHelpLink(1, 23); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(1, $rights['group_id']); ?>

<table class="Liste ListeMitgliederRechte">
    <tr>
        <th rowspan="2">Name:</th>
        <th colspan="11">Rechte</th>
        <th rowspan="2">Aktion:</th>
    </tr>
    <tr>
        <th><label for="message_write">Nachricht schreiben:</label></th>
        <th><label for="message_pin">Nachricht pinnen:</label></th>
        <th><label for="message_delete">Nachricht löschen:</label></th>
        <th><label for="edit_description">Beschreibung ändern:</label></th>
        <th><label for="edit_image">Bild bearbeiten</label></th>
        <th><label for="edit_password">Passwort ändern:</label></th>
        <th><label for="member_rights">Rechte bearbeiten:</label></th>
        <th><label for="member_kick">Mitglied kicken:</label></th>
        <th><label for="group_cash">Kasse verwalten:</label></th>
        <th><label for="group_diplomacy">Diplomatie ändern:</label></th>
        <th><label for="group_delete">Gruppe löschen:</label></th>
    </tr>
    <?php
    $data = Database::getInstance()->getAllGroupRightsByGroupId($rights['group_id']);
    foreach ($data as $row) {
        ?>
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="9"/>
            <input type="hidden" name="user_id" value="<?= $row['UserId']; ?>"/>
            <tr>
                <td><?= createProfileLink($row['UserId'], $row['UserName']); ?></td>
                <td><input type="checkbox" name="message_write" id="message_write"
                           value="1" <?= ($row['message_write'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="message_pin" id="message_pin"
                           value="1" <?= ($row['message_pin'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="message_delete" id="message_delete"
                           value="1" <?= ($row['message_delete'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_description" id="edit_description"
                           value="1" <?= ($row['edit_description'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_image" id="edit_image"
                           value="1" <?= ($row['edit_image'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_password" id="edit_password"
                           value="1" <?= ($row['edit_password'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="member_rights" id="member_rights"
                           value="1" <?= ($row['member_rights'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="member_kick" id="member_kick"
                           value="1" <?= ($row['member_kick'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_cash" id="group_cash"
                           value="1" <?= ($row['group_cash'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_diplomacy" id="group_diplomacy"
                           value="1" <?= ($row['group_diplomacy'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_delete" id="group_delete"
                           value="1" <?= ($row['group_delete'] == 1 ? 'checked' : ''); ?>/></td>
                <td>
                    <?php
                    if ($rights['member_rights'] && $row['user_id'] != $_SESSION['blm_user']) {
                        echo '<input type="submit" value="Speichern"/>';
                    } else {
                        echo '<input type="submit" value="Speichern" disabled />';
                    }

                    if ($rights['member_kick'] && $row['user_id'] != $_SESSION['blm_user']) {
                        printf('<a class="kick_member" data-username="%s" href="/actions/gruppe.php?a=10&amp;user_id=%s&amp;token=%s">Kicken</a>',
                            escapeForOutput($row['UserName']), $row['UserId'], $_SESSION['blm_xsrf_token']);
                    }
                    ?>
                </td>
            </tr>
        </form>
        <?php
    }
    ?>
</table>
