<?php
$wer = getOrDefault($_GET, 'wer');
$wen = getOrDefault($_GET, 'wen');
$gruppe = getOrDefault($_GET, 'gruppe');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Gruppenkasse</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_gruppenkasse"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <label for="wen">Wen:</label>
        <input type="text" name="wen" id="wen" value="<?= sichere_ausgabe($wen); ?>"/>
        <label for="gruppe">Gruppe:</label>
        <?= createGroupDropdown($gruppe); ?>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste">
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
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminGroupTreasuryLogEntries($filter_wer, $filter_wen, $gruppe, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= createProfileLink($row['WenId'], $row['Wen']); ?></td>
            <td><?= createGroupLink($row['GruppeId'], $row['Gruppe']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= formatCurrency($row['Wieviel']); ?></td>
            <td><?= sichere_ausgabe($row['Wohin']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('./?p=admin_log_bank&amp;wer=' . sichere_ausgabe($wer), $offset, $entriesCount, ADMIN_LOG_OFFSET); ?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
