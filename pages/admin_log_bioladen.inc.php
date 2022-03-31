<?php
$wer = getOrDefault($_GET, 'wer');
$offset = getOrDefault($_GET, 'o', 0);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Admin - Logbücher - Bioladen</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="./" method="get">
        <input type="hidden" name="p" value="admin_log_bioladen"/>
        <label for="wer">Wer:</label>
        <input type="text" name="wer" id="wer" value="<?= sichere_ausgabe($wer); ?>"/>
        <input type="submit" value="Abschicken"/>
    </form>
</div>

<table class="Liste">
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
    $offset = verifyOffset($offset, $entriesCount, ADMIN_LOG_OFFSET);
    $entries = Database::getInstance()->getAdminBioladenLogEntries($filter, $offset, ADMIN_LOG_OFFSET);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['WerId'], $row['Wer']); ?></td>
            <td><?= date("d.m.Y H:i:s", $row['WannTs']); ?></td>
            <td><?= Warenname($row['Was']); ?></td>
            <td><?= formatWeight($row['Wieviel']); ?></td>
            <td><?= formatCurrency($row['Einzelpreis']); ?></td>
            <td><?= formatCurrency($row['Gesamtpreis']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('./?p=admin_log_bioladen&amp;wer=' . sichere_ausgabe($wer), $offset, $entriesCount, ADMIN_LOG_OFFSET); ?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
