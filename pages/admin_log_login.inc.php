<?php
$wer = getOrDefault($_GET, 'wer');
$ip = getOrDefault($_GET, 'ip');
$art = getOrDefault($_GET, 'art');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.png" alt=""/>
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
            <option value="">- Alle -</option>
            <option value="0"<?= ($art == "0" ? ' selected="selected"' : '') ?>>Regulär</option>
            <option value="1"<?= ($art == "1" ? ' selected="selected"' : '') ?>>Sitter</option>
        </select>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste">
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
    $entriesCount = Database::getInstance()->getAdminLoginLogCount($filter_wer, $filter_ip, $art);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminLoginLogEntries($filter_wer, $filter_ip, $art, $offset, admin_log_page_size);

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
        echo '<tr><td colspan="4" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_login&amp;wer=' . escapeForOutput($wer) . '&amp;ip=' . escapeForOutput($ip) . '&amp;art=' . escapeForOutput($art), $offset, $entriesCount, admin_log_page_size); ?>
<p>
    <a href="/?p=admin">Zurück...</a>
</p>
