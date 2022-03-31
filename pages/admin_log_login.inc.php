<?php
$wer = getOrDefault($_GET, 'wer');
$ip = getOrDefault($_GET, 'ip');
$art = getOrDefault($_GET, 'art');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Login</td>
    </tr>
</table>

<?= $m; ?>
<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_login"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <label for="ip">IP:</label>
        <input type="text" name="ip" id="ip" value="<?= sichere_ausgabe($ip); ?>"/>
        <label for="art">Art:</label>
        <select name="art" id="art">
            <option value="">- Alle -</option>
            <option value="0"<?= ($art === 0 ? ' selected="selected"' : '') ?>>Normal</option>
            <option value="1"<?= ($art === 1 ? ' selected="selected"' : '') ?>>Sitter</option>
        </select>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Wer</th>
        <th>IP</th>
        <th>Wann</th>
        <th>Art</th>
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_ip = empty($ip) ? "%" : $ip;
    $entriesCount = Database::getInstance()->getAdminLoginLogCount($filter_wer, $filter_ip, $art);
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminLoginLogEntries($filter_wer, $filter_ip, $art, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= sichere_ausgabe($row['IP']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= sichere_ausgabe($row['Art']); ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?php
if ($entriesCount == 0) {
    echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
} else {
    echo createPaginationTable('./?p=admin_log_login&amp;wer=' . sichere_ausgabe($wer) . '&amp;ip='. sichere_ausgabe($ip) . '&amp;art='. sichere_ausgabe($art), $offset, $entriesCount, ADMIN_LOG_OFFSET);
}
?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
