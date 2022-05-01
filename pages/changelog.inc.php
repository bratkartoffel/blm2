<?php
$data = Database::getInstance()->getAllChangelog();
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/changelog.png" alt=""/>
    <span>Changelog<?= createHelpLink(1, 20); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie die Änderungen am Bioladenmanager 2 verfolgen.
    Jede größere Änderung wird hier festgehalten.
</p>

<?php
$groupedEntries = array();
foreach ($data as $entry) {
    $groupedEntries[$entry['created']][] = $entry;
}
?>
<table class="Liste Changelog">
    <tr>
        <th>Datum</th>
        <th>Änderung</th>
    </tr>
    <?php
    foreach ($groupedEntries as $date => $entries) {
        ?>
        <tr>
            <td rowspan="<?= count($entries); ?>"><?= formatDate(strtotime($date)); ?></td>
            <td>
                <u><?= escapeForOutput($entries[0]['category']); ?></u>: <?= escapeForOutput($entries[0]['description']); ?>
            </td>
        </tr>
        <?php
        for ($i = 1; $i < count($entries); $i++) {
            $entry = $entries[$i];
            ?>
            <tr>
                <td><u><?= escapeForOutput($entry['category']); ?></u>: <?= escapeForOutput($entry['description']); ?>
                </td>
            </tr>
            <?php
        }
    }

    if (count($groupedEntries) == 0) {
        echo '<tr><td colspan="2" style="text-align: center;"><i>Keine Einträge vorhanden</i></td></tr>';
    }
    ?>
</table>
