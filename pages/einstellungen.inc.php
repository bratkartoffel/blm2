<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('NeverAllow');

$data = Database::getInstance()->getPlayerEmailAndBeschreibungAndSitterSettingsById($_SESSION['blm_user']);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/package_settings.webp" alt=""/>
    <span>Büro<?= createHelpLink(1, 15); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie verschiedene Einstellungen für Ihren Account ändern.<br/>
</p>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post">
        <input type="hidden" name="a" value="6"/>
        <header>EMail Adresse ändern</header>
        <div>
            Aktuell hinterlegte Adresse: <?= escapeForOutput($data['EMail']); ?>
        </div>
        <div>
            <label for="email">Adresse:</label>
            <input name="email" id="email" type="email" size="20" required
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'email_max_len'); ?>"/>
        </div>
        <div>
            <label for="confirm">Bestätigen:</label>
            <input name="confirm" id="confirm" type="email" size="20" required
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'email_max_len'); ?>"/>
        </div>
        <div>
            Mit der Änderung der EMail-Adresse werden Sie <b>sofort</b> ausgeloggt. Ein erneuter Login ist erst wieder
            möglich, sobald die neue Adresse bestätigt wurde!
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <header>Passwort ändern</header>
        <div>
            <label for="pwd_chg_alt">Altes Passwort:</label>
            <input id="pwd_chg_alt" type="password" name="pwd_alt" size="20" required/>
        </div>
        <div>
            <label for="new_pw1">Neues Passwort:</label>
            <input id="new_pw1" type="password" name="new_pw1" size="20" required
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'password_min_len'); ?>"/>
        </div>
        <div>
            <label for="new_pw2">Bestätigen:</label>
            <input id="new_pw2" type="password" name="new_pw2" size="20" required
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'password_min_len'); ?>"/>
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post" name="form_beschreibung">
        <input type="hidden" name="a" value="4"/>
        <header><label for="beschreibung">Beschreibung ändern</label></header>
        <div>
            <textarea id="beschreibung" maxlength="4096" name="beschreibung" cols="50" rows="15"
                      onkeyup="ZeichenUebrig(this, document.form_beschreibung.getElementsByTagName('span')[0]);"><?= escapeForOutput($data['Beschreibung'], false); ?></textarea>
        </div>
        <div>
            Noch <span>X</span> Zeichen übrig
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>
<script>ZeichenUebrig(document.getElementById('beschreibung'), document.form_beschreibung.getElementsByTagName('span')[0]);</script>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="a" value="5"/>
        <header>Bild ändern</header>
        <div>
            Um das aktuelle Bild zu löschen, einfach den
            Speichern-Button drücken, ohne ein Bild auszuwählen.
        </div>
        <div>
            <input type="file" name="bild" accept="image/*"/>
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post" id="sitterSettings">
        <input type="hidden" name="a" value="7"/>
        <header>Sitting konfigurieren</header>
        <div>
            <label for="aktiviert">Sitting erlauben?</label>
            <input type="checkbox" name="aktiviert" value="1"
                   id="aktiviert"<?= ($data['Gebaeude'] !== null ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="pw_sitter">Sitting Passwort:</label>
            <input type="password" name="pw_sitter" id="pw_sitter"/>
        </div>
        <div>
            <label for="gebaeude">Gebäudebau:</label>
            <input type="checkbox" name="gebaeude" value="1"
                   id="gebaeude"<?= ($data['Gebaeude'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="forschung">Forschungen:</label>
            <input type="checkbox" name="forschung" value="1"
                   id="forschung"<?= ($data['Forschung'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="produktion">Produktion:</label>
            <input type="checkbox" name="produktion" value="1"
                   id="produktion"<?= ($data['Produktion'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="nachrichten">Nachrichten:</label>
            <input type="checkbox" name="nachrichten" value="1"
                   id="nachrichten"<?= ($data['Nachrichten'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="gruppe">Gruppe:</label>
            <input type="checkbox" name="gruppe" value="1"
                   id="gruppe"<?= ($data['Gruppe'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="vertraege">Verträge:</label>
            <input type="checkbox" name="vertraege" value="1"
                   id="vertraege"<?= ($data['Vertraege'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="marktplatz">Marktplatz:</label>
            <input type="checkbox" name="marktplatz" value="1"
                   id="marktplatz"<?= ($data['Marktplatz'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="bioladen">Bioladen:</label>
            <input type="checkbox" name="bioladen" value="1"
                   id="bioladen"<?= ($data['Bioladen'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <label for="bank">Bank:</label>
            <input type="checkbox" name="bank" value="1"
                   id="bank"<?= ($data['Bank'] == 1 ? ' checked' : ''); ?>/>
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<script>
    let enableSitting = document.getElementById('aktiviert');

    function enableSitterOptions(enabled) {
        Array.prototype.forEach.call(document.getElementById('sitterSettings').getElementsByTagName('input'), (field) => {
            if (field === enableSitting || field.type === 'submit' || field.type === 'hidden') return;
            field.disabled = !enabled;
        });
    }

    enableSitting.addEventListener('change', (event) => enableSitterOptions(event.currentTarget.checked));
    enableSitterOptions(enableSitting.checked);
</script>
<h2>Danger-Zone</h2>

<p>
    Die nachfolgenden Einstellungen sind final und können nicht rückgängig gemacht werden. Es gibt keine weitere
    Nachfrage oder Bestätigung, die Aktion wird sofort ausgeführt. Zur Sicherheit wird hier das aktuelle Passwort
    benötigt.
</p>
<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <header>Account zurücksetzen</header>
        <div>
            <label for="pwd_rst_alt">Passwort:</label>
            <input id="pwd_rst_alt" type="password" name="pwd_alt" size="20" required/>
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>

<div class="form Einstellungen">
    <form action="/actions/einstellungen.php" method="post">
        <input type="hidden" name="a" value="3"/>
        <header>Account löschen</header>
        <div>
            <label for="pwd_del_alt">Passwort:</label>
            <input id="pwd_del_alt" type="password" name="pwd_alt" size="20" required/>
        </div>
        <div>
            <input type="submit" value="Speichern"/>
        </div>
    </form>
</div>
