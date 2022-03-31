<?php
$wer = getOrDefault($_GET, 'wer');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Bank</td>
    </tr>
</table>

<?= $m; ?>
<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_bank"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Aktion</th>
    </tr>
    <?php
    $filter = empty($wer) ? "%" : $wer;
    $entriesCount = Database::getInstance()->getAdminBankLogCount($filter);
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminBankLogEntries($filter, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= formatCurrency($row['Wieviel']); ?></td>
            <td><?= sichere_ausgabe($row['Aktion']); ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?php
if ($entriesCount == 0) {
    echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
} else {
    echo createPaginationTable('./?p=admin_log_bank&amp;wer=' . sichere_ausgabe($wer), $offset, $entriesCount, ADMIN_LOG_OFFSET);
}
?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
