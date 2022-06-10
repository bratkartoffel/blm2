<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$angenommen = getOrDefault($_GET, 'angenommen', -1);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Vertrags Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_vertraege"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= escapeForOutput($wen); ?>"/>
        <label for="angenommen">Angenommen:</label>
        <select name="angenommen" id="angenommen">
            <option value="">- Alle -</option>
            <option value="0"<?= ($angenommen === 0 ? ' selected="selected"' : '') ?>>Nein</option>
            <option value="1"<?= ($angenommen === 1 ? ' selected="selected"' : '') ?>>Ja</option>
        </select>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste AdminLog">
    <tr>
        <th>Wer</th>
        <th>Wen</th>
        <th>Wann</th>
        <th>Ware</th>
        <th>Wieviel</th>
        <th>Einzelpreis</th>
        <th>Gesamtpreis</th>
        <th>Angenommen?</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_wen = empty($wen) ? "%" : $wen;
    $filter_angenommen = $angenommen === -1 ? null : intval($angenommen);
    $entriesCount = Database::getInstance()->getAdminVertraegeLogCount($filter_wer, $filter_wen, $filter_angenommen);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminVertraegeLogEntries($filter_wer, $filter_wen, $filter_angenommen, $offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['senderId'], $row['senderName']); ?></td>
            <td><?= createProfileLink($row['receiverId'], $row['receiverName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= getItemName($row['item']); ?></td>
            <td><?= formatWeight($row['amount']); ?></td>
            <td><?= formatCurrency($row['price']); ?></td>
            <td><?= formatCurrency($row['amount'] * $row['price']); ?></td>
            <td><?= getYesOrNo($row['accepted']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_vertraege&amp;wer=' . escapeForOutput($wer) . '&amp;wen=' . escapeForOutput($wen) . '&amp;angenommen=' . escapeForOutput($angenommen), $offset, $entriesCount, admin_log_page_size); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
