<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$verkaeufer = getOrDefault($_GET, 'verkaeufer');
$kaeufer = getOrDefault($_GET, 'kaeufer');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Marktplatz Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_marktplatz"/>
        <label for="verkaeufer">Verkäufer:</label>
        <input type="text" name="verkaeufer" id="verkaeufer" value="<?= escapeForOutput($verkaeufer); ?>"/>
        <label for="kaeufer">Käufer:</label>
        <input type="text" name="kaeufer" id="kaeufer" value="<?= escapeForOutput($kaeufer); ?>"/>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste AdminLog">
    <tr>
        <th>Verkäufer</th>
        <th>Käufer</th>
        <th>Wann</th>
        <th>Ware</th>
        <th>Wieviel</th>
        <th>Einzelpreis</th>
        <th>Gesamtpreis</th>
    </tr>
    <?php
    $filter_verkaeufer = empty($verkaeufer) ? "%" : $verkaeufer;
    $filter_kaeufer = empty($kaeufer) ? "%" : $kaeufer;
    $entriesCount = Database::getInstance()->getAdminMarketLogCount($filter_verkaeufer, $filter_kaeufer);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminMarketLogEntries($filter_verkaeufer, $filter_kaeufer, $offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['sellerId'], $row['sellerName']); ?></td>
            <td><?php
                if ($row['buyerId'] === null) {
                    echo '<i>Zurückgezogen</i>';
                } else {
                    echo createProfileLink($row['buyerId'], $row['buyerName']);
                }
                ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= getItemName($row['item']); ?></td>
            <td><?= formatWeight($row['amount']); ?></td>
            <td><?= formatCurrency($row['price']); ?></td>
            <td><?= formatCurrency($row['amount'] * $row['price']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_marktplatz&amp;verkaeufer=' . escapeForOutput($verkaeufer) . '&amp;kaeufer=' . escapeForOutput($kaeufer), $offset, $entriesCount, admin_log_page_size); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
