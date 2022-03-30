<?php
/**
 * Wird in die index.php eingebunden; Seite zur Verwaltung des Changelogs
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 *
 * @todo Einträge hinzufügen
 * @todo Einträge bearbeiten
 * @todo Einträge löschen
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Admin Changelog"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Changelog</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<table class="Liste" style="width: 700px;">
    <tr>
        <th>ID</th>
        <th>Datum</th>
        <th>Kategorie</th>
        <th>Beschreibung</th>
        <th>Aktion</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
    *,
    UNIX_TIMESTAMP(Datum) AS Datum
FROM
    changelog
ORDER BY
    ID DESC;";
    $sql_ergebnis = mysql_query($sql_abfrage);

    while ($c = mysql_fetch_object($sql_ergebnis)) {
        ?>
        <tr>
            <td><?= $c->ID; ?></td>
            <td><?= date("d.m.Y", $c->Datum); ?></td>
            <td><?= $c->Kategorie; ?></td>
            <td><?= $c->Aenderung; ?></td>
            <td style="white-space: nowrap;">
                <a href="./?p=admin_changelog_bearbeiten&amp;id=<?= $c->ID; ?>">
                    <img src="pics/small/info.png" style="border: none;" alt="Bearbeiten"/>
                </a>
                <a href="actions/admin_changelog.php?a=2&amp;id=<?= $c->ID; ?>">
                    <img src="pics/small/error.png" style="border: none;" alt="Löschen"/>
                </a>
            </td>
        </tr>
        <?php
    }
    ?>
</table>

<p>
    <a href="./?p=admin">Zurück...</a>
</p>
