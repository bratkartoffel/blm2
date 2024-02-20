<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$art = getOrDefault($_GET, 'art');
$success = getOrDefault($_GET, 'success', -1);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Mafia Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_mafia"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= escapeForOutput($wen); ?>"/>
        <label for="art">Art:</label>
        <select name="art" id="art">
            <option value="">- Alle -</option>
            <option value="ESPIONAGE"<?= ($art === 'ESPIONAGE' ? ' selected="selected"' : '') ?>>Spionage</option>
            <option value="ROBBERY"<?= ($art === 'ROBBERY' ? ' selected="selected"' : '') ?>>Raub</option>
            <option value="HEIST"<?= ($art === 'HEIST' ? ' selected="selected"' : '') ?>>Diebstahl</option>
            <option value="ATTACK"<?= ($art === 'ATTACK' ? ' selected="selected"' : '') ?>>Angriff</option>
        </select>
        <label for="success">Erfolgreich:</label>
        <select name="success" id="success">
            <option value="-1">- Alle -</option>
            <option value="0"<?= ($success === 0 ? ' selected="selected"' : '') ?>>Nein</option>
            <option value="1"<?= ($success === 1 ? ' selected="selected"' : '') ?>>Ja</option>
        </select>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste AdminLog nowrap">
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
    $filter_wer = empty($wer) ? null : $wer;
    $filter_wen = empty($wen) ? null : $wen;
    $filter_art = empty($art) ? null : $art;
    $filter_success = $success === -1 ? null : $success;
    $entriesCount = Database::getInstance()->getAdminMafiaLogCount($filter_wer, $filter_wen, $filter_art, $filter_success);
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAdminMafiaLogEntries($filter_wer, $filter_wen, $filter_art, $filter_success, $offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['senderId'], $row['senderName']); ?></td>
            <td><?= createProfileLink($row['receiverId'], $row['receiverName']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?php
                echo $row['action'];

                if ($row['action'] == 'HEIST' && $row['success'] == 1) {
                    printf(' (%s)', getItemName($row['item']));
                }
                ?></td>
            <td><?= ($row['action'] == 'HEIST') && $row['amount'] !== null ? formatWeight($row['amount']) : ($row['amount'] === null ? '-' : formatCurrency($row['amount'])); ?></td>
            <td><?= getYesOrNo($row['success']); ?></td>
            <td><?= formatPercent($row['chance']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('pages', '/?p=admin_log_mafia'
        . '&amp;wer=' . urlencode($wer)
        . '&amp;wen=' . urlencode($wen)
        . '&amp;art=' . escapeForOutput($art)
        . '&amp;success=' . $success
        , $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<div>
    <a href="./?p=admin">&lt;&lt; Zurück</a>
</div>
