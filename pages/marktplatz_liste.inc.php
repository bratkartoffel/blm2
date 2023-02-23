<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Marktplatz');

$filter = getOrDefault($_GET, 'filter', 0);
$offset = getOrDefault($_GET, 'offset', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/package_network.webp" alt=""/>
    <span>Marktplatz<?= createHelpLink(1, 11); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie die aktuellen Angebote auf dem anonymen Marktplatz. Die Preise können selbst bestimmt werden
    und richten sich wahrscheinlich nach dem aktuellen Fortschritt des Spiels.
</p>

<?php
$offerCount = Database::getInstance()->getMarktplatzCount();
$offset = verifyOffset($offset, $offerCount, Config::getInt(Config::SECTION_BASE, 'market_page_size'));
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
    $entries = Database::getInstance()->getMarktplatzEntries(array(), $offset, Config::getInt(Config::SECTION_BASE, 'market_page_size'));
    $nr = $offerCount - $offset * Config::getInt(Config::SECTION_BASE, 'market_page_size');
    foreach ($entries as $row) {
        $rowNr = $nr--;
        ?>
        <tr>
            <td><?= $rowNr; ?></td>
            <td><?= getItemName($row['Was']); ?></td>
            <td><?= formatWeight($row['Menge']); ?></td>
            <td><?= formatCurrency($row['Preis']); ?></td>
            <td><?= formatCurrency($row['Menge'] * $row['Preis']); ?></td>
            <td>
                <?php
                if ($row['VonId'] != $_SESSION['blm_user']) {
                    printf('<a class="market_buy_offer" data-id="%d" href="/actions/marktplatz.php?a=2&amp;id=%s&amp;token=%s">Kaufen</a>',
                        $rowNr, $row['ID'], $_SESSION['blm_xsrf_token']);
                } else {
                    $refundWeight = formatWeight(floor($row['Menge'] * Config::getFloat(Config::SECTION_MARKET, 'retract_rate')), false);
                    printf('<a class="market_retract_offer" data-id="%d" data-refund="%s" href="/actions/marktplatz.php?a=3&amp;id=%s&amp;token=%s">Zurückziehen</a>',
                        $rowNr, $refundWeight, $row['ID'], $_SESSION['blm_xsrf_token']);
                }
                ?>
            </td>
        </tr>
        <?php
    }
    if (count($entries) == 0) {
        echo '<tr><td colspan="6" class="center"><i>Es wurden keine Angebote gefunden</i></td></tr>';
    }
    ?>
</table>

<?= createPaginationTable('/?p=marktplatz_liste', $offset, $offerCount, Config::getInt(Config::SECTION_BASE, 'market_page_size')); ?>

<a href="/?p=marktplatz_verkaufen">Neues Angebot einstellen</a>
