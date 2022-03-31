<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Mafia</td>
    </tr>
</table>

<?= $m; ?>
<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_mafia"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <label for="wen">Wen:</label> <input type="text" name="wen" id="wen" value="<?= sichere_ausgabe($wen); ?>"/>
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
    </tr>
    <?php
    $filter_wer = empty($wer) ? "%" : $wer;
    $filter_wen = empty($wen) ? "%" : $wen;
    $entriesCount = Database::getInstance()->getAdminMafiaLogCount($filter_wer, $filter_wen);
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminMafiaLogEntries($filter_wer, $filter_wen, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= createProfileLink($row['WenId'], $row['Wen']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= sichere_ausgabe($row['Art']); ?></td>
            <td><?= ($row['Art'] == 'Angriff') ? formatWeight($row['Wieviel']) : formatCurrency($row['Wieviel']); ?></td>
            <td><?= sichere_ausgabe($row['Erfolgreich']); ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?php
if ($entriesCount == 0) {
    echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
} else {
    echo createPaginationTable('./?p=admin_log_mafia&amp;wer=' . sichere_ausgabe($wer) . '&amp;wen=' . sichere_ausgabe($wen), $offset, $entriesCount, ADMIN_LOG_OFFSET);
}
?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
