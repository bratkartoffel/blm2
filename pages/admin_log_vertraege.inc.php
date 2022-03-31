<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$angenommen = getOrDefault($_GET, 'angenommen');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Verträge</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_vertraege"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= sichere_ausgabe($wen); ?>"/>
        <label for="angenommen">Angenommen:</label>
        <select name="angenommen" id="angenommen">
            <option value="">- Alle -</option>
            <option value="0"<?= ($angenommen == "0" ? ' selected="selected"' : '') ?>>Nein</option>
            <option value="1"<?= ($angenommen == "1" ? ' selected="selected"' : '') ?>>Ja</option>
        </select>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste">
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
    $entriesCount = Database::getInstance()->getAdminVertraegeLogCount($filter_wer, $filter_wen, $angenommen);
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminVertraegeLogEntries($filter_wer, $filter_wen, $angenommen, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= createProfileLink($row['WenId'], $row['Wen']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= WarenName($row['Ware']); ?></td>
            <td><?= formatWeight($row['Wieviel']); ?></td>
            <td><?= formatCurrency($row['Einzelpreis']); ?></td>
            <td><?= formatCurrency($row['Gesamtpreis']); ?></td>
            <td><?= sichere_ausgabe($row['Angenommen']); ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?php
if ($entriesCount == 0) {
    echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
} else {
    echo createPaginationTable('./?p=admin_log_vertraege&amp;wer=' . sichere_ausgabe($wer) . '&amp;wen=' . sichere_ausgabe($wen) . '&amp;angenommen=' . sichere_ausgabe($angenommen), $offset, $entriesCount, ADMIN_LOG_OFFSET);
}
?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
