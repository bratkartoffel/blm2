<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/admin.png" alt=""/>
    <span>Administrationsbereich - Verträge</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_vertrag"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= escapeForOutput($wen); ?>"/>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Von</th>
        <th>An</th>
        <th>Ware</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_wen = empty($wen) ? "%" : $wen;

    $entriesCount = Database::getInstance()->getVertragCount($filter_wer, $filter_wen);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getVertragEntries($filter_wer, $filter_wen, $offset, admin_log_page_size);


    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
    ?>
    <tr>
        <td><?= createProfileLink($row['VonId'], $row['VonName']); ?></td>
        <td><?= createProfileLink($row['AnId'], $row['AnName']); ?></td>
        <td><?= getItemName($row['Was']); ?></td>
        <td><?= formatWeight($row['Menge']); ?></td>
        <td><?= formatCurrency($row['Preis']); ?></td>
        <td><?= formatCurrency($row['Gesamtpreis']); ?></td>
        <td>
            <a href="/?p=admin_vertrag_bearbeiten&amp;id=<?= $row['ID']; ?>">
                <img src="/pics/small/info.png" alt="Bearbeiten"/>
            </a>
            <a href="/actions/admin_vertrag.php?a=3&amp;id=<?= $row['ID']; ?>">
                <img src="/pics/small/error.png" alt="Löschen"/>
            </a>
        </td>
    </tr>
    <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="7" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_vertrag&amp;wer=' . escapeForOutput($wer) . '&amp;wen=' . escapeForOutput($wen), $offset, $entriesCount, admin_log_page_size); ?>
<p>
    <a href="/?p=admin_vertrag_einstellen">Neuen Vertrag erstellen</a><br/>
    <a href="/?p=admin">Zurück...</a>
</p>
