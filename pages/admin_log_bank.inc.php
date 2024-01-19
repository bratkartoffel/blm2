<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$wer = getOrDefault($_GET, 'wer');
$wohin = getOrDefault($_GET, 'wohin');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Bank Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_bank"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wohin">Wohin:</label>
        <select name="wohin" id="wohin">
            <option value="">- Alle -</option>
            <option value="HAND"<?= ($wohin === 'HAND' ? ' selected="selected"' : '') ?>>Hand</option>
            <option value="BANK"<?= ($wohin === 'BANK' ? ' selected="selected"' : '') ?>>Bank</option>
        </select>

        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste AdminLog nowrap">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Wohin</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? null : $wer;
    $filter_wohin = empty($wohin) ? null : $wohin;
    $entriesCount = Database::getInstance()->getAdminBankLogCount($filter_wer, $filter_wohin);
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAdminBankLogEntries($filter_wer, $filter_wohin, $offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

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
        echo '<tr><td colspan="4" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('pages', '/?p=admin_log_bank&amp;wer=' . escapeForOutput($wer)
    . '&amp;wohin=' . escapeForOutput($wohin)
    , $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<div>
    <a href="./?p=admin">&lt;&lt; Zurück</a>
</div>
