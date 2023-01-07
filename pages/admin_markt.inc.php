<?php
$ware = getOrDefault($_GET, 'ware', 0);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Marktplatz</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_markt"/>
        <label for="ware">Ware:</label>
        <?= createWarenDropdown($ware, 'ware'); ?>
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
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getMarktplatzEntries($filter_waren, $offset, admin_log_page_size);


    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['VonId'], $row['VonName']); ?></td>
            <td><?= getItemName($row['Was']); ?></td>
            <td><?= formatWeight($row['Menge']); ?></td>
            <td><?= formatCurrency($row['Preis']); ?></td>
            <td><?= formatCurrency($row['Gesamtpreis']); ?></td>
            <td>
                <a href="/?p=admin_markt_bearbeiten&amp;id=<?= $row['ID']; ?>"> Bearbeiten </a> |
                <a href="/actions/admin_markt.php?a=3&amp;id=<?= $row['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"> Löschen </a>
            </td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_markt&amp;ware=' . escapeForOutput($ware), $offset, $entriesCount, admin_log_page_size); ?>

<div>
    <a href="/?p=admin_markt_einstellen">Neues Angebot erstellen</a><br/>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
