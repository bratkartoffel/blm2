<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$wer = getOrDefault($_GET, 'wer');
$ip = getOrDefault($_GET, 'ip');
$art = getOrDefault($_GET, 'art', -1);
$success = getOrDefault($_GET, 'success', -1);
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Login Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_login"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= escapeForOutput($wer); ?>"/>
        <label for="ip">IP:</label>
        <input type="text" name="ip" id="ip" value="<?= escapeForOutput($ip); ?>"/>
        <label for="art">Art:</label>
        <select name="art" id="art">
            <option value="-1">- Alle -</option>
            <option value="0"<?= ($art === 0 ? ' selected="selected"' : '') ?>>Regulär</option>
            <option value="1"<?= ($art === 1 ? ' selected="selected"' : '') ?>>Sitter</option>
        </select>
        <label for="success">Erfolgreich:</label>
        <select name="success" id="success">
            <option value="-1">- Alle -</option>
            <option value="0"<?= ($success === 0 ? ' selected="selected"' : '') ?>>Nein</option>
            <option value="1"<?= ($success === 1 ? ' selected="selected"' : '') ?>>Ja</option>
        </select>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste AdminLog">
    <tr>
        <th>Wer</th>
        <th>IP</th>
        <th>Wann</th>
        <th>Erfolgreich</th>
        <th>Art</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_ip = empty($ip) ? "%" : $ip;
    $filter_art = $art === -1 ? null : intval($art);
    $filter_success = $success === -1 ? null : intval($success);
    $entriesCount = Database::getInstance()->getAdminLoginLogCount($filter_wer, $filter_ip, $filter_art, $filter_success);
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAdminLoginLogEntries($filter_wer, $filter_ip, $filter_art, $filter_success, $offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['playerId'], $row['playerName']); ?></td>
            <td><?= escapeForOutput($row['ip']); ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= getYesOrNo($row['success']); ?></td>
            <td><?= ($row['sitter'] == 1 ? 'Sitter' : 'Regulär'); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="4" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_login&amp;wer=' . escapeForOutput($wer) . '&amp;ip=' . escapeForOutput($ip) . '&amp;art=' . escapeForOutput($art), $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
