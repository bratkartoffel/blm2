<?php
restrictSitter('Gruppe');

$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Community_Help.png" alt=""/>
    <span>Gruppe - Mitgliederverwaltung<?= createHelpLink(1, 23); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(1, $rights['group_id']); ?>

<table class="Liste ListeMitgliederRechte">
    <tr>
        <th rowspan="2">Name:</th>
        <th colspan="11">Rechte</th>
        <th>Aktion:</th>
    </tr>
    <tr>
        <th>Nachricht schreiben:</th>
        <th>Nachricht pinnen:</th>
        <th>Nachricht löschen:</th>
        <th>Beschreibung ändern:</th>
        <th>Bild bearbeiten</th>
        <th>Passwort ändern:</th>
        <th>Rechte bearbeiten:</th>
        <th>Mitglied kicken:</th>
        <th>Kasse verwalten:</th>
        <th>Diplomatie ändern:</th>
        <th>Gruppe löschen:</th>
        <th></th>
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
                <td><input type="checkbox" name="message_write"
                           value="1" <?= ($row['message_write'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="message_pin"
                           value="1" <?= ($row['message_pin'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="message_delete"
                           value="1" <?= ($row['message_delete'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_description"
                           value="1" <?= ($row['edit_description'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_image"
                           value="1" <?= ($row['edit_image'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="edit_password"
                           value="1" <?= ($row['edit_password'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="member_rights"
                           value="1" <?= ($row['member_rights'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="member_kick"
                           value="1" <?= ($row['member_kick'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_cash"
                           value="1" <?= ($row['group_cash'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_diplomacy"
                           value="1" <?= ($row['group_diplomacy'] == 1 ? 'checked' : ''); ?>/></td>
                <td><input type="checkbox" name="group_delete"
                           value="1" <?= ($row['group_delete'] == 1 ? 'checked' : ''); ?>/></td>
                <td>
                    <?php
                    if ($rights['member_rights'] && $row['user_id'] != $_SESSION['blm_user']) {
                        echo '<input type="submit" value="Speichern" onclick="return submit(this);"/>';
                    } else {
                        echo '<input type="submit" value="Speichern" disabled />';
                    }

                    if ($rights['member_kick'] && $row['user_id'] != $_SESSION['blm_user']) {
                        echo '<a href="/actions/gruppe.php?a=10&amp;user_id=' . $row['UserId'] . '&amp;token=' . $_SESSION['blm_xsrf_token'] . '"
                            onclick="return confirm(\'Wollen Sie das Mitglied ' . escapeForOutput($row['UserName']) . ' wirklich aus der Gruppe entfernen?\');">Kicken</a>';
                    }
                    ?>
                </td>
            </tr>
        </form>
        <?php
    }
    ?>
</table>
