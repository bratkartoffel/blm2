<?php
requireFieldSet($_GET, 'id', '/?p=admin_markt');
$id = getOrDefault($_GET, 'id', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Angebot bearbeiten</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entry = Database::getInstance()->getMarktplatzEntryById($id);
requireEntryFound($entry, '/?p=admin_markt');

if (isset($_GET['von'])) $entry['Von'] = $_GET['von'];
if (isset($_GET['ware'])) $entry['Was'] = $_GET['ware'];
if (isset($_GET['menge'])) $entry['Menge'] = $_GET['menge'];
if (isset($_GET['preis'])) $entry['Preis'] = $_GET['preis'];
?>
<div id="FilterForm">
    <form action="/actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= escapeForOutput($entry['ID']); ?>"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Angebot bearbeiten</th>
            </tr>
            <tr>
                <td>Von:</td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $entry['Von'], 'von', false); ?></td>
            </tr>
            <tr>
                <td>Was</td>
                <td><?= createWarenDropdown($entry['Was'], 'ware', false); ?></td>
            </tr>
            <tr>
                <td>Menge</td>
                <td><input type="number" min="1" name="menge"
                           value="<?= formatWeight($entry['Menge'], false, 0, false); ?>" size="6"/> kg
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
    <a href="/?p=admin_markt">Zurück...</a>
</p>
