<?php
$ware = getOrDefault($_GET, 'ware');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Marktplatz</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_markt"/>
        <label for="ware">Ware:</label>
        <?= createWarenDropdown($ware); ?>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Wer</th>
        <th>Ware</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $filter_waren = empty($ware) ? array() : array($ware);
    $entriesCount = Database::getInstance()->getMarktplatzCount($filter_waren);
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getMarktplatzEntries($filter_waren, $offset, ADMIN_LOG_OFFSET);


    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['VonId'], $row['VonName']); ?></td>
            <td><?= WarenName($row['Was']); ?></td>
            <td><?= formatWeight($row['Menge']); ?></td>
            <td><?= formatCurrency($row['Preis']); ?></td>
            <td><?= formatCurrency($row['Gesamtpreis']); ?></td>
            <td>
                <a href="/?p=admin_markt_bearbeiten&amp;id=<?= $row['ID']; ?>">
                    <img src="/pics/small/info.png" alt="Bearbeiten"/>
                </a>
                <a href="/actions/admin_markt.php?a=3&amp;id=<?= $row['ID']; ?>">
                    <img src="/pics/small/error.png" alt="Löschen"/>
                </a>
            </td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('./?p=admin_markt&amp;ware=' . sichere_ausgabe($ware), $offset, $entriesCount, ADMIN_LOG_OFFSET); ?>
<p>
    <a href="./?p=admin">Zurück...</a><br/>
    <a href="./?p=admin_markt_einstellen">Neues Angebot einstellen</a>
</p>
