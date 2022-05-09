<?php
requireFieldSet($_GET, 'id', '/?p=admin_vertrag');
$id = getOrDefault($_GET, 'id', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Vertrag bearbeiten</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entry = Database::getInstance()->getVertragEntryById($id);
requireEntryFound($entry, '/?p=admin_vertrag');

if (isset($_GET['von'])) $entry['Von'] = $_GET['von'];
if (isset($_GET['an'])) $entry['An'] = $_GET['an'];
if (isset($_GET['ware'])) $entry['Was'] = $_GET['ware'];
if (isset($_GET['menge'])) $entry['Menge'] = $_GET['menge'];
if (isset($_GET['preis'])) $entry['Preis'] = $_GET['preis'];
?>
<div id="FilterForm">
    <form action="/actions/admin_vertrag.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Vertrag bearbeiten</th>
            </tr>
            <tr>
                <td>Absender:</td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $entry['VonId'], 'von', false); ?></td>
            </tr>
            <tr>
                <td>Empfänger:</td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $entry['AnId'], 'an', false); ?></td>
            </tr>
            <tr>
                <td>Was</td>
                <td><?= createWarenDropdown($entry['Was'], 'ware', false); ?></td>
            </tr>
            <tr>
                <td>Menge</td>
                <td><input type="number" min="1" name="menge"
                           value="<?= formatWeight($entry['Menge'], false, 0, false); ?>"
                           size="6"/> kg
                </td>
            </tr>
            <tr>
                <td>Preis</td>
                <td><input type="number" min="0.01" step="0.01" name="preis"
                           value="<?= formatCurrency($entry['Preis'], false, false); ?>" size="6"/> €
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Speichern"/>
                </td>
            </tr>
        </table>
    </form>
</div>
<p>
    <a href="/?p=admin_vertrag">Zurück...</a>
</p>
