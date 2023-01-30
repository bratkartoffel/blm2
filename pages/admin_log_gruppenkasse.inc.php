<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$gruppe = getOrDefault($_GET, 'gruppe', 0);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Gruppenkasse Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_gruppenkasse"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= escapeForOutput($wen); ?>"/>
        <label for="gruppe">Gruppe:</label>
        <?= createDropdown(Database::getInstance()->getAllGroupIdsAndName(), $gruppe, 'gruppe'); ?>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste AdminLog nowrap">
    <tr>
        <th>Wer</th>
        <th>Wen</th>
        <th>Gruppe</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Wohin</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_wen = empty($wen) ? "%" : $wen;
    $entriesCount = Database::getInstance()->getAdminGroupTreasuryLogCount($filter_wer, $filter_wen, $gruppe);
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAdminGroupTreasuryLogEntries($filter_wer, $filter_wen, $gruppe, $offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['senderId'], $row['senderName']); ?></td>
            <td><?= ($row['receiverId'] === null ? '' : createProfileLink($row['receiverId'], $row['receiverName'])); ?></td>
            <td><?= createGroupLink($row['groupId'], $row['groupName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= formatCurrency($row['amount']); ?></td>
            <td><?= ($row['receiverId'] === null ? 'Gruppe' : 'Spieler'); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_gruppenkasse&amp;wer=' . escapeForOutput($wer) . '&amp;wen=' . escapeForOutput($wen) . '&amp;gruppe=' . $gruppe, $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
