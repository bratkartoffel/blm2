<?php
$von = getOrDefault($_GET, 'von', 0);
$an = getOrDefault($_GET, 'an', 0);
$ware = getOrDefault($_GET, 'ware', 0);
$menge = getOrDefault($_GET, 'menge', 0);
$preis = getOrDefault($_GET, 'preis', .0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/admin.png" alt=""/>
    <span>Administrationsbereich - Vertrag erstellen</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/actions/admin_vertrag.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <table class="Liste">
            <tr>
                <th colspan="2">Vertrag einstellen</th>
            </tr>
            <tr>
                <td>Absender:</td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $von, 'von', false); ?></td>
            </tr>
            <tr>
                <td>Empfänger:</td>
                <td><?= createDropdown(Database::getInstance()->getAllPlayerIdsAndName(), $an, 'an', false); ?></td>
            </tr>
            <tr>
                <td>Was</td>
                <td><?= createWarenDropdown($ware, 'ware', false); ?></td>
            </tr>
            <tr>
                <td>Menge</td>
                <td><input type="number" min="1" name="menge" value="<?= formatWeight($menge, false, 0, false); ?>"
                           size="6"/> kg
                </td>
            </tr>
            <tr>
                <td>Preis</td>
                <td><input type="number" min="0.01" step="0.01" name="preis"
                           value="<?= formatCurrency($preis, false, false); ?>" size="6"/> €
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
    <a href="/?p=admin">Zurück...</a>
</p>
