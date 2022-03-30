<?php
/**
 * Wird in die index.php eingebunden; Seite mit Formularen zum Bearbeiten des Accounts
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/einstellungen.png" alt="Einstellungen"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die Einstellungen
            <a href="./?p=hilfe&amp;mod=1&amp;cat=15"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier können Sie verschiedene Einstellungen für Ihren Account ändern.<br/>
</b>
<br/>
<form action="./actions/einstellungen.php" method="post">
    <input type="hidden" name="a" value="6"/>
    <table class="Liste" style="width: 300px;" cellspacing="0" id="EMail">
        <tr>
            <th colspan="2">EMail Adresse ändern</th>
        </tr>
        <tr>
            <td>EMail:</td>
            <td><input type="text" name="email"
                       value="<?= htmlentities(stripslashes($ich->EMail), ENT_QUOTES, "utf-8"); ?>"
                       style="width: 95%;"/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Absenden"/></td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post">
    <input type="hidden" name="a" value="1"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0" id="Passwort">
        <tr>
            <th colspan="2">Passwort ändern</th>
        </tr>
        <tr>
            <td>Altes Passwort:</td>
            <td><input type="password" name="pwd_alt" value=""/></td>
        </tr>
        <tr>
            <td>Neues Passwort:</td>
            <td><input type="password" name="new_pw1" value=""/></td>
        </tr>
        <tr>
            <td>Bestätigen:</td>
            <td><input type="password" name="new_pw2" value=""/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Absenden"/></td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post" name="form_beschreibung">
    <input type="hidden" name="a" value="4"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0" id="Beschreibung">
        <tr>
            <th>Beschreibung ändern</th>
        </tr>
        <tr>
            <td><textarea maxlength="4096" name="beschreibung" cols="33" rows="10"
                          onkeyup="ZeichenUebrig(this, document.form_beschreibung.getElementsByTagName('span')[0]);"><?= htmlentities(stripslashes($ich->Beschreibung), ENT_QUOTES, "UTF-8"); ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">Noch <span>4096</span> Zeichen übrig.<input type="submit"
                                                                                                    value="Absenden"/>
            </td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="a" value="5"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0" id="Bild">
        <tr>
            <th colspan="2">Bild hochladen</th>
        </tr>
        <tr>
            <td>Bildpfad:</td>
            <td><input type="file" name="bild"/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Absenden"/></td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post">
    <input type="hidden" name="a" value="2"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0" id="Reset">
        <tr>
            <th colspan="2">Account resetten</th>
        </tr>
        <tr>
            <td>Passwort:</td>
            <td><input type="password" name="pwd_reset" value=""/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Absenden"/></td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post">
    <input type="hidden" name="a" value="3"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0">
        <tr>
            <th colspan="2">Account löschen</th>
        </tr>
        <tr>
            <td>Passwort:</td>
            <td><input type="password" name="pwd_delete" value=""/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" value="Absenden"/></td>
        </tr>
    </table>
</form>
<form action="./actions/einstellungen.php" method="post">
    <input type="hidden" name="a" value="7"/>
    <table class="Liste" style="width: 300px; margin-top: 30px;" cellspacing="0" id="Sitter">
        <tr>
            <th colspan="2">Sitterrechte</th>
        </tr>
        <tr>
            <td>Sitting erlauben?</td>
            <td><input type="checkbox" name="aktiviert" <?php
                if (isset($ich->Sitter->ID)) {
                    echo 'checked="checked"';
                }
                ?> value="1"/></td>
        </tr>
        <tr>
            <td>Passwort:</td>
            <td><input type="text" name="pw_sitter"
                       value="<?= htmlentities(stripslashes($ich->Sitter->Passwort), ENT_QUOTES, "UTF-8"); ?>"
                       size="50"/> <?php
                if (isset($ich->Sitter->Passwort)) {
                    echo "<i>(Das ist das verschlüsselte Passwort, wenn nur die Rechte geändert werden sollen, dann einfach stehen lassen.)</i>";
                }
                ?></td>
        </tr>
        <tr>
            <td>Gebäudebau:</td>
            <td><input type="checkbox" name="gebaeude" value="1" <?php
                if ($ich->Sitter->Gebaeude) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Forschungen:</td>
            <td><input type="checkbox" name="forschung" value="1" <?php
                if ($ich->Sitter->Forschung) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Produktion:</td>
            <td><input type="checkbox" name="produktion" value="1"<?php
                if ($ich->Sitter->Produktion) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Nachrichten:</td>
            <td><input type="checkbox" name="nachrichten" value="1" <?php
                if ($ich->Sitter->Nachrichten) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Gruppe:</td>
            <td><input type="checkbox" name="gruppe" value="1" <?php
                if ($ich->Sitter->Gruppe) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Verträge:</td>
            <td><input type="checkbox" name="vertraege" value="1" <?php
                if ($ich->Sitter->Vertraege) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Marktplatz:</td>
            <td><input type="checkbox" name="marktplatz" value="1" <?php
                if ($ich->Sitter->Marktplatz) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Bioladen:</td>
            <td><input type="checkbox" name="bioladen" value="1" <?php
                if ($ich->Sitter->Bioladen) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td>Bank:</td>
            <td><input type="checkbox" name="bank" value="1" <?php
                if ($ich->Sitter->Bank) {
                    echo 'checked="checked"';
                }
                ?> /></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="Speichern"/>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    const z = document.getElementsByTagName("input");

    for (let i = 0; i < z.length; i++) {
        if (z[i].type == "password") {
            z[i].value = "";
        }
    }
</script>
