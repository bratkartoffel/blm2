<?php
requireFieldSet($_GET, 'id', '/?p=admin_benutzer');
$id = getOrDefault($_GET, 'id', 0);
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
if (isset($_GET['punkte'])) $entry['Punkte'] = getOrDefault($_GET, 'punkte', 0);
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
<div id="FilterForm">
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste EditUser" id="user_general">
            <tr>
                <th colspan="2">Benutzer bearbeiten</th>
            </tr>
            <tr>
                <td>Name:</td>
                <td>
                    <input name="username" id="username" type="text" value="<?= escapeForOutput($entry['Name']); ?>"
                           size="20" required
                           minlength="<?= username_min_len; ?>" maxlength="<?= username_max_len; ?>"/>
                </td>
            </tr>
            <tr>
                <td>EMail:</td>
                <td><input name="email" id="email" type="email" size="20" required
                           value="<?= escapeForOutput($entry['EMail']); ?>"
                           maxlength="<?= email_max_len; ?>"/></td>
            </tr>
            <tr>
                <td>Aktiviert</td>
                <td><input type="checkbox" name="email_aktiviert"
                           value="1" <?= ($entry['EMailAct'] === null ? 'checked' : ''); ?>/></td>
            </tr>
            <tr>
                <td>Passwort</td>
                <td><input name="password" id="password" type="password" size="20"/></td>
            </tr>
            <tr>
                <td>Registriert am</td>
                <td><?= formatDateTime(strtotime($entry['RegistriertAm'])); ?></td>
            </tr>
            <tr>
                <td>Geld</td>
                <td><input type="number" name="geld" value="<?= formatCurrency($entry['Geld'], false, false); ?>"
                           size="13"/> €
                </td>
            </tr>
            <tr>
                <td>Bank</td>
                <td><input type="number" name="bank" value="<?= formatCurrency($entry['Bank'], false, false); ?>"
                           size="13"/> €
                </td>
            </tr>
            <tr>
                <td>Punkte</td>
                <td><input type="number" name="punkte" value="<?= escapeForOutput($entry['Punkte']); ?>" size="13"/>
                </td>
            </tr>
            <tr>
                <td>IGM Gesendet</td>
                <td><input type="number" name="igm_gesendet" value="<?= escapeForOutput($entry['IgmGesendet']); ?>"
                           size="4"/>
                </td>
            </tr>
            <tr>
                <td>IGM Empfangen</td>
                <td><input type="number" name="igm_empfangen" value="<?= escapeForOutput($entry['IgmEmpfangen']); ?>"
                           size="4"/>
                </td>
            </tr>
            <tr>
                <td>Admin</td>
                <td><input type="checkbox" name="admin" value="1" <?= ($entry['Admin'] == 1 ? 'checked' : ''); ?>/></td>
            </tr>
            <tr>
                <td>Betatester</td>
                <td><input type="checkbox" name="betatester"
                           value="1" <?= ($entry['Betatester'] == 1 ? 'checked' : ''); ?>/></td>
            </tr>
            <tr>
                <td>Letzte Aktion</td>
                <td><?= $entry['LastAction'] !== null ? formatDateTime(strtotime($entry['LastAction'])) : '<i>- Nie -</i>'; ?></td>
            </tr>
            <tr>
                <td>Letzte Anmeldung</td>
                <td><?= $entry['LastLogin'] !== null ? formatDateTime(strtotime($entry['LastLogin'])) : '<i>- Nie -</i>'; ?></td>
            </tr>
            <tr>
                <td>Letzte Änderung Profilbild</td>
                <td><?= $entry['LastImageChange'] !== null ? formatDateTime(strtotime($entry['LastImageChange'])) : '<i>- Nie -</i>'; ?></td>
            </tr>
            <tr>
                <td>Nächste Mafiaktion</td>
                <td><?= $entry['NextMafia'] !== null ? formatDateTime(strtotime($entry['NextMafia'])) : '<i>- Sofort -</i>'; ?></td>
            </tr>
            <tr>
                <td>Ewige Punkte</td>
                <td><input type="number" name="ewige_punkte" value="<?= escapeForOutput($entry['EwigePunkte']); ?>"
                           size="4"/>
                </td>
            </tr>
            <tr>
                <td>Onlinezeit (Sekunden)</td>
                <td><input type="number" name="onlinezeit" value="<?= escapeForOutput($entry['OnlineZeit']); ?>"
                           size="8"/>
                </td>
            </tr>
            <tr>
                <td>Gruppe</td>
                <td><?= createDropdown($groups, $entry['Gruppe'], 'gruppe', false, false, true); ?></td>
            </tr>
            <tr>
                <td>Verwarnungen</td>
                <td><input type="number" name="verwarnungen" value="<?= escapeForOutput($entry['Verwarnungen']); ?>"
                           size="3"/>
                </td>
            </tr>
            <tr>
                <td>Gesperrt</td>
                <td><input type="checkbox" name="gesperrt"
                           value="1" <?= ($entry['Gesperrt'] == 1 ? 'checked' : ''); ?>/></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste EditUser" id="user_buildings">
            <tr>
                <th colspan="2">Gebäude</th>
            </tr>
            <?php
            for ($i = 1; $i <= count_buildings; $i++) {
                ?>
                <tr>
                    <td><?= getBuildingName($i); ?></td>
                    <td><input type="number" name="gebaeude_<?= $i; ?>"
                               value="<?= escapeForOutput($entry['Gebaeude' . $i]); ?>"
                               size="3"/>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="3"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste EditUser" id="user_research">
            <tr>
                <th colspan="2">Forschungen</th>
            </tr>
            <?php
            for ($i = 1; $i <= count_wares; $i++) {
                ?>
                <tr>
                    <td><?= getItemName($i); ?></td>
                    <td><input type="number" name="forschung_<?= $i; ?>"
                               value="<?= escapeForOutput($entry['Forschung' . $i]); ?>"
                               size="3"/>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
    <form action="/actions/admin_benutzer.php" method="post">
        <input type="hidden" name="a" value="4"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste EditUser" id="user_stock">
            <tr>
                <th colspan="2">Lagerbestand</th>
            </tr>
            <?php
            for ($i = 1; $i <= count_wares; $i++) {
                ?>
                <tr>
                    <td><?= getItemName($i); ?></td>
                    <td><input type="number" name="lager_<?= $i; ?>"
                               value="<?= escapeForOutput($entry['Lager' . $i]); ?>"
                               size="7"/>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
</div>

<div>
    <a href="/?p=admin_benutzer">&lt;&lt; Zurück</a>
</div>
