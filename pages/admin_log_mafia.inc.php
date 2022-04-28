<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/admin.png" alt=""/>
    <span>Administrationsbereich - Mafia Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_mafia"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= escapeForOutput($wen); ?>"/>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Wer</th>
        <th>Wen</th>
        <th>Wann</th>
        <th>Art</th>
        <th>Wieviel</th>
        <th>Erfolgreich?</th>
        <th>Chance</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_wen = empty($wen) ? "%" : $wen;
    $entriesCount = Database::getInstance()->getAdminMafiaLogCount($filter_wer, $filter_wen);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminMafiaLogEntries($filter_wer, $filter_wen, $offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['senderId'], $row['senderName']); ?></td>
            <td><?= createProfileLink($row['receiverId'], $row['receiverName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= $row['action']; ?></td>
            <td><?= ($row['action'] == 'HEIST') ? formatWeight($row['amount']) : ($row['amount'] === null ? '-' : formatCurrency($row['amount'])); ?></td>
            <td><?= getYesOrNo($row['success']); ?></td>
            <td><?= formatPercent($row['chance']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_mafia&amp;wer=' . escapeForOutput($wer) . '&amp;wen=' . escapeForOutput($wen), $offset, $entriesCount, admin_log_page_size); ?>
<p>
    <a href="/?p=admin">Zurück...</a>
</p>
