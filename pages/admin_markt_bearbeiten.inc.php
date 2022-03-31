<?php
requireFieldSet($_GET, 'id', '/?p=admin_markt');
$id = getOrDefault($_GET, 'id', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/marktplatz.png" alt=""/></td>
        <td>Admin - Marktplatz - Angebot bearbeiten</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<?php
$entries = Database::getInstance()->getMarktplatzEntryById($id);
requireEntryFound($entries, '/?p=admin_markt');
$entry = $entries[0];
?>
<div id="FilterForm">
    <form action="/actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="2"/>
        <input type="hidden" name="id" value="<?= sichere_ausgabe($entry['ID']); ?>"/>
        <table class="Liste">
            <tr>
                <th>Angebot bearbeiten</th>
            </tr>
            <tr>
                <td>
                    <input type="text" name="menge" size="2" value="<?= $entry['Menge']; ?>"/> kg
                    <?= createWarenDropdown($entry['Was'], 'ware', false); ?>
                    zu <input type="text" name="preis" size="3" value="<?= formatCurrency($entry['Preis'], false); ?>"/>
                    € / kg
                    <input type="submit" value="ändern"/>
                </td>
            </tr>
        </table>
    </form>
</div>
<p>
    <a href="./?p=admin_markt">Zurück...</a>
</p>
