<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$wer = getOrDefault($_GET, 'wer');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Bioladen Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_bioladen"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste AdminLog">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Was</th>
        <th>Wieviel</th>
        <th>Einzelpreis</th>
        <th>Gesamtpreis</th>
    </tr>
    <?php
    $filter = empty($wer) ? "%" : $wer;
    $entriesCount = Database::getInstance()->getAdminBioladenLogCount($filter);
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAdminBioladenLogEntries($filter, $offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['playerId'], $row['playerName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= getItemName($row['item']); ?></td>
            <td><?= formatWeight($row['amount']); ?></td>
            <td><?= formatCurrency($row['price']); ?></td>
            <td><?= formatCurrency($row['amount'] * $row['price']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_bioladen&amp;wer=' . escapeForOutput($wer), $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
