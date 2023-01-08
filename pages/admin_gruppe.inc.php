<?php
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
        <th>Registriert</th>
        <th>Gesperrt</th>
        <th>Verw.</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entriesCount = Database::getInstance()->getPlayerCount();
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAllPlayerIdsAndNameAndEMailAndRegistriertAmAndGesperrtAndVerwarnungen($offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['ID'], $row['Name']); ?></td>
            <td><?= escapeForOutput($row['EMail']); ?></td>
            <td><?= formatDate(strtotime($row['RegistriertAm'])); ?></td>
            <td><?= getYesOrNo($row['Gesperrt']); ?></td>
            <td><?= escapeForOutput($row['Verwarnungen']); ?></td>
            <td>
                <a href="/?p=admin_benutzer_bearbeiten&amp;id=<?= $row['ID']; ?>&amp;o=<?= $offset; ?>">Bearbeiten</a> |
                <a href="/actions/admin_benutzer.php?a=5&amp;id=<?= $row['ID']; ?>&amp;o=<?= $offset; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                   onclick="return confirm('Benutzer \'<?= escapeForOutput($row['Name']); ?>\' wirklich löschen?');">Löschen</a>
            </td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="7" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_benutzer', $offset, $entriesCount, admin_log_page_size); ?>

<a href="/?p=admin">&lt;&lt; Zurück</a>
