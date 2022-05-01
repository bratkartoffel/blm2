<?php
restrictSitter('Marktplatz');

$filter = getOrDefault($_GET, 'filter', 0);
$offset = getOrDefault($_GET, 'offset', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/marktplatz.png" alt=""/>
    <span>Marktplatz<?= createHelpLink(1, 11); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie die aktuellen Angebote auf dem anonymen Marktplatz. Die Preise können selbst bestimmt werden
    und richten sich wahrscheinlich nach dem aktuellen Fortschritt des Spiels.
</p>

<?php
$offerCount = Database::getInstance()->getMarktplatzCount();
$offset = verifyOffset($offset, $offerCount, market_page_size);
?>
<table class="Liste Marktplatz">
    <tr>
        <th>Nr</th>
        <th>Ware</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getMarktplatzEntries(array(), $offset, market_page_size);
    foreach ($entries as $row) {
        ?>
        <tr>
            <td><?= $row['ID']; ?></td>
            <td><?= getItemName($row['Was']); ?></td>
            <td><?= formatWeight($row['Menge']); ?></td>
            <td><?= formatCurrency($row['Preis']); ?></td>
            <td><?= formatCurrency($row['Menge'] * $row['Preis']); ?></td>
            <td>
                <?php
                if ($row['VonId'] != $_SESSION['blm_user']) {
                    echo '<a href="./actions/marktplatz.php?a=2&amp;id=' . $row['ID'] . '" onclick="return confirm(\'Wollen Sie das Angebot Nr ' . $row['ID'] . ' wirklich kaufen?\')">Kaufen</a>';
                } else {
                    echo '<a href="./actions/marktplatz.php?a=3&amp;id=' . $row['ID'] . '" onclick="return confirm(\'Wollen Sie das Angebot Nr ' . $row['ID'] . ' zurückziehen?\nSie erhalten lediglich ' . formatWeight(floor($row['Menge'] * market_retract_rate)) . ' der Waren zurück.\')">Zurückziehen</a>';
                }
                ?>
            </td>
        </tr>
        <?php
    }
    if (count($entries) == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>Es wurden keine Angebote gefunden</i></td></tr>';
    }
    ?>
</table>

<?= createPaginationTable('/?p=marktplatz_liste', $offset, $offerCount, market_page_size); ?>

<a href="/?p=marktplatz_verkaufen">Neues Angebot einstellen</a>
