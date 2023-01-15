<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

requireFieldSet($_GET, 'id', '/?p=admin_benutzer');
$id = getOrDefault($_GET, 'id', 0);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.webp" alt=""/>
    <span>Administrationsbereich - Benutzer</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entry = Database::getInstance()->getPlayerDataById($id);
requireEntryFound($entry, '/?p=admin_benutzer');

$groups = Database::getInstance()->getAllGroupIdsAndName();

// first form, general user data
if (isset($_GET['username'])) $entry['Name'] = $_GET['username'];
if (isset($_GET['email'])) $entry['EMail'] = $_GET['email'];
if (isset($_GET['email_aktiviert'])) $entry['EMailAct'] = $_GET['email_aktiviert'] == "1" ? null : 'x';
if (isset($_GET['geld'])) $entry['Geld'] = getOrDefault($_GET, 'geld', .0);
if (isset($_GET['bank'])) $entry['Bank'] = getOrDefault($_GET, 'bank', .0);
if (isset($_GET['igm_gesendet'])) $entry['IgmGesendet'] = getOrDefault($_GET, 'igm_gesendet', 0);
if (isset($_GET['igm_empfangen'])) $entry['IgmEmpfangen'] = getOrDefault($_GET, 'igm_empfangen', 0);
if (isset($_GET['admin'])) $entry['Admin'] = $_GET['admin'] == "1" ? 1 : 0;
if (isset($_GET['betatester'])) $entry['Betatester'] = $_GET['betatester'] == "1" ? 1 : 0;
if (isset($_GET['ewige_punkte'])) $entry['EwigePunkte'] = getOrDefault($_GET, 'ewige_punkte', 0);
if (isset($_GET['onlinezeit'])) $entry['OnlineZeit'] = getOrDefault($_GET, 'onlinezeit', 0);
if (isset($_GET['gruppe'])) $entry['Gruppe'] = $_GET['gruppe'];
if (isset($_GET['verwarnungen'])) $entry['Verwarnungen'] = getOrDefault($_GET, 'verwarnungen', 0);
if (isset($_GET['gesperrt'])) $entry['Gesperrt'] = $_GET['gesperrt'] == "1" ? 1 : 0;

// second form, building levels
for ($i = 1; $i <= count_buildings; $i++) {
    if (isset($_GET['gebaeude_' . $i])) $entry['Gebaeude' . $i] = getOrDefault($_GET, 'gebaeude_' . $i, 0);
}

// third form, research levels
for ($i = 1; $i <= count_buildings; $i++) {
    if (isset($_GET['forschung_' . $i])) $entry['Forschung' . $i] = getOrDefault($_GET, 'forschung_' . $i, 0);
}

// fourth form, warehouse stock
for ($i = 1; $i <= count_buildings; $i++) {
    if (isset($_GET['lager_' . $i])) $entry['Lager' . $i] = getOrDefault($_GET, 'lager_' . $i, 0);
}
?>
<div class="form AdminEditUser">
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <header>Benutzer bearbeiten</header>
        <div>
            <label for="username">Name:</label>
            <input name="username" id="username" type="text" value="<?= escapeForOutput($entry['Name']); ?>"
                   size="20" required
                   minlength="<?= Config::getInt(Config::SECTION_BASE, 'username_min_len'); ?>"
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'username_max_len'); ?>"/>
        </div>
        <div>
            <label for="email">EMail:</label>
            <input name="email" id="email" type="email" size="40" required
                   value="<?= escapeForOutput($entry['EMail']); ?>"
                   maxlength="<?= Config::getInt(Config::SECTION_BASE, 'email_max_len'); ?>"/>
        </div>
        <div>
            <label for="email_aktiviert">Aktiviert:</label>
            <input type="checkbox" id="email_aktiviert" name="email_aktiviert"
                   value="1" <?= ($entry['EMailAct'] === null ? 'checked' : ''); ?>/>
        </div>
        <div>
            <label for="password">Passwort:</label>
            <input name="password" id="password" type="password" size="20"/>
        </div>
        <div>
            <label>Registriert am:</label>
            <span><?= formatDateTime(strtotime($entry['RegistriertAm'])); ?></span>
        </div>
        <div>
            <label for="geld">Geld:</label>
            <input type="number" name="geld" id="geld"
                   value="<?= formatCurrency($entry['Geld'], false, false); ?>"
                   size="13"/> €
        </div>
        <div>
            <label for="bank">Bank:</label>
            <input type="number" name="bank" id="bank"
                   value="<?= formatCurrency($entry['Bank'], false, false); ?>"
                   size="13"/> €
        </div>
        <div>
            <label for="punkte">Punkte:</label>
            <span><?= escapeForOutput($entry['Punkte']); ?></span>
        </div>
        <div>
            <label for="igm_gesendet">IGM Gesendet:</label>
            <input type="number" name="igm_gesendet" id="igm_gesendet"
                   value="<?= escapeForOutput($entry['IgmGesendet']); ?>"
                   size="4"/>
        </div>
        <div>
            <label for="igm_empfangen">IGM Empfangen:</label>
            <input type="number" name="igm_empfangen" id="igm_empfangen"
                   value="<?= escapeForOutput($entry['IgmEmpfangen']); ?>"
                   size="4"/>
        </div>
        <div>
            <label for="admin">Admin:</label>
            <input type="checkbox" name="admin" id="admin"
                   value="1" <?= ($entry['Admin'] == 1 ? 'checked' : ''); ?>/>
        </div>
        <div>
            <label for="betatester">Betatester:</label>
            <input type="checkbox" name="betatester" id="betatester"
                   value="1" <?= ($entry['Betatester'] == 1 ? 'checked' : ''); ?>/>
        </div>
        <div>
            <label>Letzte Aktion:</label>
            <span><?= $entry['LastAction'] !== null ? formatDateTime(strtotime($entry['LastAction'])) : '<i>- Nie -</i>'; ?></span>
        </div>
        <div>
            <label>Letzte Anmeldung:</label>
            <span><?= $entry['LastLogin'] !== null ? formatDateTime(strtotime($entry['LastLogin'])) : '<i>- Nie -</i>'; ?></span>
        </div>
        <div>
            <label>Profilbild geändert:</label>
            <span><?= $entry['LastImageChange'] !== null ? formatDateTime(strtotime($entry['LastImageChange'])) : '<i>- Nie -</i>'; ?></span>
        </div>
        <div>
            <label>Nächste Mafiaktion:</label>
            <span><?= $entry['NextMafia'] !== null ? formatDateTime(strtotime($entry['NextMafia'])) : '<i>- Sofort -</i>'; ?></span>
        </div>
        <div>
            <label for="ewige_punkte">Ewige Punkte:</label>
            <input type="number" name="ewige_punkte" id="ewige_punkte"
                   value="<?= escapeForOutput($entry['EwigePunkte']); ?>"
                   size="4"/>
        </div>
        <div>
            <label for="onlinezeit">Onlinezeit (Sekunden):</label>
            <input type="number" name="onlinezeit" id="onlinezeit"
                   value="<?= escapeForOutput($entry['OnlineZeit']); ?>"
                   size="8"/>
        </div>
        <div>
            <label for="gruppe">Gruppe:</label>
            <?php
            echo createDropdown($groups, $entry['Gruppe'], 'gruppe', false, false, true);

            if ($entry['Gruppe'] !== null) {
                echo sprintf(' (<a href="/?p=admin_gruppe_bearbeiten&amp;id=%d">Rechte / Kasse bearbeiten</a>)', $entry['Gruppe']);
            }
            ?>
        </div>
        <div>
            <label for="verwarnungen">Verwarnungen:</label>
            <input type="number" name="verwarnungen" id="verwarnungen"
                   value="<?= escapeForOutput($entry['Verwarnungen']); ?>"
                   size="3"/>
        </div>
        <div>
            <label for="gesperrt">Gesperrt:</label>
            <input type="checkbox" name="gesperrt" id="gesperrt"
                   value="1" <?= ($entry['Gesperrt'] == 1 ? 'checked' : ''); ?>/>
        </div>
        <div>
            <input type="submit" value="Speichern" id="user_save"/>
        </div>
    </form>
</div>
<br>
<div class="form AdminEditUser">
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <header>Gebäude</header>
        <?php
        for ($i = 1; $i <= count_buildings; $i++) {
            ?>
            <div>
                <label for="gebaeude_<?= $i; ?>"><?= getBuildingName($i); ?></label>
                <input type="number" name="gebaeude_<?= $i; ?>" id="gebaeude_<?= $i; ?>"
                       value="<?= escapeForOutput($entry['Gebaeude' . $i]); ?>"
                       size="3" min="0"/>
            </div>
            <?php
        }
        ?>
        <div>
            <input type="submit" value="Speichern" id="buildings_save"/>
        </div>
    </form>
</div>

<div class="form AdminEditUser">
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="3"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <header>Forschungen</header>
        <?php
        for ($i = 1; $i <= count_wares; $i++) {
            ?>
            <div>
                <label for="forschung_<?= $i; ?>"><?= getItemName($i); ?></label>
                <input type="number" name="forschung_<?= $i; ?>" id="forschung_<?= $i; ?>"
                       value="<?= escapeForOutput($entry['Forschung' . $i]); ?>"
                       size="3" min="0"/>
            </div>
            <?php
        }
        ?>
        <div>
            <input type="submit" value="Speichern" id="research_save"/>
        </div>
    </form>
</div>

<div class="form AdminEditUser">
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="4"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <input type="hidden" name="o" value="<?= $offset; ?>"/>
        <header>Lagerbestand</header>
        <?php
        for ($i = 1; $i <= count_wares; $i++) {
            ?>
            <div>
                <label for="lager_<?= $i; ?>"><?= getItemName($i); ?></label>
                <input type="number" name="lager_<?= $i; ?>" id="lager_<?= $i; ?>"
                       value="<?= escapeForOutput($entry['Lager' . $i]); ?>"
                       size="7" min="0"/>
            </div>
            <?php
        }
        ?>
        <div>
            <input type="submit" value="Speichern" id="stock_save"/>
        </div>
    </form>
</div>

<div>
    <a href="/?p=admin_benutzer&amp;o=<?= $offset; ?>">&lt;&lt; Zurück</a>
</div>
