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
                <td style="font-weight: bold; height: 40px;">
                    <input type="text" name="menge" size="2" value="0"/> kg
                    <?= createWarenDropdown(null, false); ?>
                    zu <input type="text" name="preis" size="3" value="0,00"/> <?= $Currency; ?> / kg
                    <input type="submit" value="verkaufen"/>
                </td>
            </tr>
        </table>
    </form>
</div>
<p>
    <a href="./?p=admin_markt">Zurück...</a>
</p>
