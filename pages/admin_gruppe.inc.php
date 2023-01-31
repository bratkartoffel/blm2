<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.webp" alt=""/>
    <span>Administrationsbereich - Gruppen</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<table class="Liste">
    <tr>
        <th>Name</th>
        <th>Kürzel</th>
        <th>Erstellt</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entriesCount = Database::getInstance()->getGroupCount();
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getGroupIdAndNameAndKuerzelAndErstellt($offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createGroupLink($row['ID'], $row['Name']); ?></td>
            <td><?= escapeForOutput($row['Kuerzel']); ?></td>
            <td><?= formatDate(strtotime($row['Erstellt'])); ?></td>
            <td>
                <a href="/?p=admin_gruppe_bearbeiten&amp;id=<?= $row['ID']; ?>&amp;o=<?= $offset; ?>">Bearbeiten</a> |
                <a class="delete_group" data-groupname="<?= escapeForOutput($row['Name']); ?>"
                   href="/actions/admin_gruppe.php?a=4&amp;id=<?= $row['ID']; ?>&amp;o=<?= $offset; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Löschen</a>
            </td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="7" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_gruppe', $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<a href="/?p=admin">&lt;&lt; Zurück</a>

<script nonce="<?= getCspNonce(); ?>">
    for (let deleteLink of document.getElementsByClassName('delete_group')) {
        deleteLink.onclick = () => confirm('Gruppe "' + deleteLink.getAttribute('data-groupname') + '"wirklich löschen?');
    }
</script>
