<?php
requireFieldSet($_GET, 'id', '/?p=admin_markt');
$id = getOrDefault($_GET, 'id', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Marktplatz - Angebot bearbeiten</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entries = Database::getInstance()->getMarktplatzEntryById($id);
requireEntryFound($entries, '/?p=admin_markt');
$entry = $entries[0];

if (isset($_GET['von'])) $entry['Von'] = $_GET['von'];
if (isset($_GET['ware'])) $entry['Was'] = $_GET['ware'];
if (isset($_GET['menge'])) $entry['Menge'] = $_GET['menge'];
if (isset($_GET['preis'])) $entry['Preis'] = $_GET['preis'];
?>
<div id="FilterForm">
    <form action="/actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= sichere_ausgabe($entry['ID']); ?>"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Angebot bearbeiten</th>
            </tr>
            <tr>
                <td>Von:</td>
                <td><?= createPlayerDropdown($entry['Von'], 'von', false); ?></td>
            </tr>
            <tr>
                <td>Was</td>
                <td><?= createWarenDropdown($entry['Was'], 'ware', false); ?></td>
            </tr>
            <tr>
                <td>Menge</td>
                <td><input type="text" name="menge" value="<?= formatWeight($entry['Menge'], false); ?>" size="6"/> kg
                </td>
            </tr>
            <tr>
                <td>Preis</td>
                <td><input type="text" name="preis" value="<?= formatCurrency($entry['Preis'], false); ?>" size="6"/> €
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
    <a href="./?p=admin_markt">Zurück...</a>
</p>
