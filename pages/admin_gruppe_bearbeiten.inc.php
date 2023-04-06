<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

requireFieldSet($_GET, 'id', '/?p=admin_gruppe');
$id = getOrDefault($_GET, 'id', 0);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.webp" alt=""/>
    <span>Administrationsbereich - Gruppe</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entry = Database::getInstance()->getGroupInformationById($id);
requireEntryFound($entry, '/?p=admin_gruppe');
?>
<div class="form AdminEditGroup">
    <form action="/actions/admin_gruppe.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <input type="hidden" name="id" value="<?= $id; ?>"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <header>Gruppe bearbeiten</header>
        <div>
            <label for="name">Name:</label>
            <input name="name" id="name" type="text" value="<?= escapeForOutput($entry['Name']); ?>"
                   size="20" required maxlength="<?= Config::getInt(Config::SECTION_GROUP, 'max_name_length'); ?>"/>
        </div>
        <div>
            <label for="kuerzel">Kürzel:</label>
            <input name="kuerzel" id="kuerzel" type="text" size="8" required
                   value="<?= escapeForOutput($entry['Kuerzel']); ?>"
                   maxlength="<?= Config::getInt(Config::SECTION_GROUP, 'max_tag_length'); ?>"/>
        </div>
        <div>
            <label for="password">Passwort:</label>
            <input name="password" id="password" type="password" size="20"/>
        </div>
        <div>
            <label>Erstellt am:</label>
            <span><?= formatDateTime(strtotime($entry['Erstellt'])); ?></span>
        </div>
        <div>
            <label for="beschreibung">Beschreibung:</label>
            <textarea id="beschreibung" maxlength="4096" name="beschreibung" cols="50"
                      rows="15"><?= escapeForOutput($entry['Beschreibung'], false); ?></textarea>
        </div>
        <div>
            <label for="kasse">Kasse:</label>
            <input type="number" name="kasse" id="kasse" value="<?= $entry['Kasse']; ?>" size="13" min="0" step="0.01"/> €
        </div>
        <div>
            <label>Bild geändert:</label>
            <span><?= $entry['LastImageChange'] !== null ? formatDateTime(strtotime($entry['LastImageChange'])) : '<i>- Nie -</i>'; ?></span>
        </div>
        <div>
            <input type="submit" value="Speichern" id="group_save"/>
        </div>
    </form>
</div>

<div class="form">
    <header>Gruppenkasse</header>
    <?php
    $data = Database::getInstance()->getAllGroupCashById($id);
    foreach ($data as $entry) {
        ?>
        <form action="/actions/admin_gruppe.php" method="post">
            <input type="hidden" name="a" value="2"/>
            <input type="hidden" name="id" value="<?= $id; ?>"/>
            <input type="hidden" name="user_id" value="<?= $entry['UserID']; ?>"/>
            <input type="hidden" name="o" value="<?= $offset; ?>"/>
            <div>
                <label for="amount_<?= $entry['UserID']; ?>"><?php
                    echo createProfileLink($entry['UserID'], $entry['UserName'], 'admin_benutzer_bearbeiten');
                    if ($entry['IstMitglied'] != 1 && $entry['UserID'] !== null) {
                        echo ' (ausgetreten)';
                    }
                    ?>:</label>
                <input type="number" name="amount" id="amount_<?= $entry['UserID']; ?>" size="12" min="0" step="0.01"
                       value="<?= $entry['amount']; ?>"/> €
                <input type="submit" value="Speichern"
                       id="cash_save_<?= $entry['UserID']; ?>"/>
            </div>
        </form>
        <?php
    }
    ?>
</div>

<br>

<div class="AdminEditGroup">
    <table class="Liste ListeMitgliederRechte ">
        <tr>
            <th rowspan="2">Name:</th>
            <th colspan="11">Rechte</th>
            <th rowspan="2">Aktion:</th>
        </tr>
        <tr>
            <th><label>Nachricht schreiben:</label></th>
            <th><label>Nachricht pinnen:</label></th>
            <th><label>Nachricht löschen:</label></th>
            <th><label>Beschreibung ändern:</label></th>
            <th><label>Bild bearbeiten</label></th>
            <th><label>Passwort ändern:</label></th>
            <th><label>Rechte bearbeiten:</label></th>
            <th><label>Mitglied kicken:</label></th>
            <th><label>Kasse verwalten:</label></th>
            <th><label>Diplomatie ändern:</label></th>
            <th><label>Gruppe löschen:</label></th>
        </tr>
        <?php
        $data = Database::getInstance()->getAllGroupRightsByGroupId($id);
        foreach ($data as $row) {
            ?>
            <form action="/actions/admin_gruppe.php" method="post">
                <input type="hidden" name="a" value="3"/>
                <input type="hidden" name="id" value="<?= $id; ?>"/>
                <input type="hidden" name="user_id" value="<?= escapeForOutput($row['UserId']); ?>"/>
                <input type="hidden" name="o" value="<?= $offset; ?>"/>
                <tr>
                    <td><?= createProfileLink($row['UserId'], $row['UserName'], 'admin_benutzer_bearbeiten'); ?></td>
                    <td><input type="checkbox" name="message_write" id="message_write_<?= $id; ?>"
                               value="1" <?= ($row['message_write'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="message_pin" id="message_pin_<?= $id; ?>"
                               value="1" <?= ($row['message_pin'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="message_delete" id="message_delete_<?= $id; ?>"
                               value="1" <?= ($row['message_delete'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="edit_description" id="edit_description_<?= $id; ?>"
                               value="1" <?= ($row['edit_description'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="edit_image" id="edit_image_<?= $id; ?>"
                               value="1" <?= ($row['edit_image'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="edit_password" id="edit_password_<?= $id; ?>"
                               value="1" <?= ($row['edit_password'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="member_rights" id="member_rights_<?= $id; ?>"
                               value="1" <?= ($row['member_rights'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="member_kick" id="member_kick_<?= $id; ?>"
                               value="1" <?= ($row['member_kick'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="group_cash" id="group_cash_<?= $id; ?>"
                               value="1" <?= ($row['group_cash'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="group_diplomacy" id="group_diplomacy_<?= $id; ?>"
                               value="1" <?= ($row['group_diplomacy'] == 1 ? 'checked' : ''); ?>/></td>
                    <td><input type="checkbox" name="group_delete" id="group_delete_<?= $id; ?>"
                               value="1" <?= ($row['group_delete'] == 1 ? 'checked' : ''); ?>/></td>
                    <td>
                        <input type="submit" value="Speichern"
                               id="save_rights_<?= $id; ?>"/>
                    </td>
                </tr>
            </form>
            <?php
        }
        ?>
    </table>
</div>

<div>
    <a href="/?p=admin_gruppe&amp;o=<?= $offset; ?>">&lt;&lt; Zurück</a>
</div>
