<?php
restrictSitter('Gruppe');

$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');

$hasOneRight = false;
?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/Community_Help.png" alt=""/>
        <span>Gruppe - Einstellungen<?= createHelpLink(1, 23); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(2, $rights['group_id']); ?>

<?php
if ($rights['edit_image']) {
    $hasOneRight = true;
    ?>
    <div class="form GroupSetting GroupImage">
        <form action="/actions/gruppe.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="a" value="11"/>
            <header>Gruppenbild ändern</header>
            <div>
                Um das aktuelle Bild zu löschen, einfach den<br/>
                Speichern-Button drücken, ohne ein Bild auszuwählen.
            </div>
            <div>
                <input type="file" name="bild" accept="image/*"/>
            </div>
            <div>
                <input type="submit" value="Speichern" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <?php
}

if ($rights['edit_description']) {
    $hasOneRight = true;
    $data = Database::getInstance()->getGroupInformationById($rights['group_id']);
    $beschreibung = getOrDefault($_GET, 'beschreibung', $data['Beschreibung']);
    ?>
    <div class="form GroupSetting GroupDescription">
        <form action="/actions/gruppe.php" method="post" name="form_beschreibung">
            <input type="hidden" name="a" value="12"/>
            <header>Beschreibung ändern</header>
            <div>
            <textarea id="beschreibung" maxlength="4096" name="beschreibung" cols="50" rows="15"
                      onkeyup="ZeichenUebrig(this, document.form_beschreibung.getElementsByTagName('span')[0]);"><?= escapeForOutput($beschreibung, false); ?></textarea>
            </div>
            <div>
                Noch <span>X</span> Zeichen übrig
            </div>
            <div>
                <input type="submit" value="Speichern" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <script>ZeichenUebrig(document.getElementById('beschreibung'), document.form_beschreibung.getElementsByTagName('span')[0]);</script>
    <?php
}

if ($rights['edit_password']) {
    $hasOneRight = true;
    ?>
    <div class="form GroupSetting GroupPassword">
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="13"/>
            <header>Passwort ändern</header>
            <div>
                <label for="new_pw1">Neues Passwort:</label>
                <input id="new_pw1" type="password" name="new_pw1" size="20" required
                       minlength="<?= password_min_len; ?>"/>
            </div>
            <div>
                <label for="new_pw2">Bestätigen:</label>
                <input id="new_pw2" type="password" name="new_pw2" size="20" required
                       minlength="<?= password_min_len; ?>"/>
            </div>
            <div>
                <input type="submit" value="Speichern" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <?php
}

if ($rights['group_delete'] || Database::getInstance()->getGroupMemberCountById($rights['group_id']) == 1) {
    $hasOneRight = true;
    ?>
    <div class="form GroupSetting GroupDelete">
        <form action="/actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="14"/>
            <input type="hidden" name="token" value="<?= $_SESSION['blm_xsrf_token']; ?>"/>
            <header>Gruppe löschen</header>
            <div class="Warning">
                DIESER SCHRITT KANN NICHT RÜCKGÄNGIG GEMACHT WERDEN!<br/>
                Bitte mit der Gruppen-Nummer (<?= $rights['group_id']; ?>) bestätigen.
            </div>
            <div>
                <label for="confirm">Bestätigen:</label>
                <input id="confirm" type="number" name="confirm"/>
            </div>
            <div>
                <input type="submit" value="Ausführen" onclick="return submit(this);"/>
            </div>
        </form>
    </div>
    <?php
}

if (!$hasOneRight) {
    echo '<h3>Sie haben keine Rechte für diese Seite</h3>';
}
