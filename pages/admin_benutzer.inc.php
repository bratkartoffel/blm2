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
    <span>Administrationsbereich - Benutzer</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<table class="Liste">
    <tr>
        <th>Name</th>
        <th>EMail</th>
        <th>Registriert</th>
        <th>Gesperrt</th>
        <th>Verw.</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entriesCount = Database::getInstance()->getPlayerCount();
    $offset = verifyOffset($offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));
    $entries = Database::getInstance()->getAllPlayerIdsAndNameAndEMailAndRegistriertAmAndGesperrtAndVerwarnungen($offset, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size'));

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
                <a class="delete_user" data-username="<?= escapeForOutput($row['Name']); ?>"
                   href="/actions/admin_benutzer.php?a=5&amp;id=<?= $row['ID']; ?>&amp;o=<?= $offset; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Löschen</a>
            </td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="7" class="center"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('pages', '/?p=admin_benutzer', $offset, $entriesCount, Config::getInt(Config::SECTION_BASE, 'admin_log_page_size')); ?>

<a href="/?p=admin">&lt;&lt; Zurück</a> | <a href="/?p=admin_benutzer_importieren">Importieren</a>
