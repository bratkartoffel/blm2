<?php
$ware = getOrDefault($_GET, 'ware');
$menge = getOrDefault($_GET, 'menge', 0);
$preis = getOrDefault($_GET, 'preis', .0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Marktplatz - Neues Angebot
        </td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/actions/admin_markt.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <table class="Liste">
            <tr>
                <th>Angebot einstellen</th>
            </tr>
            <tr>
                <td>
                    <input type="text" name="menge" size="2" value="<?= $menge; ?>"/> kg
                    <?= createWarenDropdown($ware, 'ware', false); ?>
                    zu <input type="text" name="preis" size="3" value="<?= formatCurrency($preis, false); ?>"/> € / kg
                    <input type="submit" value="verkaufen"/>
                </td>
            </tr>
        </table>
    </form>
</div>
<p>
    <a href="./?p=admin_markt">Zurück...</a>
</p>
