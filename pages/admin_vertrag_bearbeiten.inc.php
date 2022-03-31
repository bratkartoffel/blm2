<?php
requireFieldSet($_GET, 'id', '/?p=admin_vertrag');
$id = getOrDefault($_GET, 'id', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Verträge - Vertrag bearbeiten
        </td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entries = Database::getInstance()->getVertragEntryById($id);
requireEntryFound($entries, '/?p=admin_vertrag');
$entry = $entries[0];
?>
<div id="FilterForm">
    <form action="/actions/admin_vertrag.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= sichere_ausgabe($entry['ID']); ?>"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Vertrag bearbeiten</th>
            </tr>
            <tr>
                <td>Absender:</td>
                <td><?= createPlayerDropdown($entry['VonId'], 'von', false); ?></td>
            </tr>
            <tr>
                <td>Empfänger:</td>
                <td><?= createPlayerDropdown($entry['AnId'], 'an', false); ?></td>
            </tr>
            <tr>
                <td>Was</td>
                <td><?= createWarenDropdown($entry['Was'], 'ware'); ?></td>
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
    <a href="./?p=admin_vertrag">Zurück...</a>
</p>
