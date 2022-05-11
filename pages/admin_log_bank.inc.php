<?php
$wer = getOrDefault($_GET, 'wer');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Bank Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_bank"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Wohin</th>
    </tr>
    <?php
    $filter = empty($wer) ? "%" : $wer;
    $entriesCount = Database::getInstance()->getAdminBankLogCount($filter);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminBankLogEntries($filter, $offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['playerId'], $row['playerName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= formatCurrency($row['amount']); ?></td>
            <td><?= $row['target']; ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="4" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_bank&amp;wer=' . escapeForOutput($wer), $offset, $entriesCount, admin_log_page_size); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
