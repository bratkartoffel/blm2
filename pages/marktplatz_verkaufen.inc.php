<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Marktplatz');

$amount = getOrDefault($_GET, 'amount', .0);
$ware = getOrDefault($_GET, 'ware', 0);
$price = getOrDefault($_GET, 'price', .0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/package_network.webp" alt=""/>
    <span>Marktplatz inserieren<?= createHelpLink(1, 11); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie ein neues Angebot auf den freien Markt stellen.
</p>
<p>
    Das Einstellen von Angeboten ist kostenlos, jedoch gehen beim zurückziehen aus dem Markt ein kleiner Anteil
    (<?= formatPercent(1 - market_retract_rate); ?>) der Waren verloren.
</p>

<?php
$data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);
?>
<div class="form MarktplatzNeu">
    <form action="/actions/marktplatz.php?a=1" method="post">
        <header>Angebotsdaten</header>
        <div>
            <label for="amount">Menge:</label>
            <input type="number" min="1" name="amount" id="amount" value="<?= $amount; ?>"/>
        </div>
        <div>
            <label for="ware">Ware:</label>
            <?= createWarenDropdown($ware, 'ware', false, $data); ?>
        </div>
        <div>
            <label for="price">Preis:</label>
            <input type="number" min="1.0" step="0.01" name="price" id="price"
                   value="<?= formatCurrency($price, false, false); ?>"/>
        </div>
        <div>
            <input type="submit" value="Inserat erstellen" onclick="return submit(this);"/>
        </div>
    </form>
</div>

<h3>Warenbestand</h3>
<table class="Liste">
    <tr>
        <th>Ware</th>
        <th>Menge</th>
        <th>Preis (Laden)</th>
        <th>Erlaubter Bereich</th>
        <th>Aktion</th>
    </tr>
    <?php
    $waresFound = false;
    for ($i = 1; $i < count_wares; $i++) {
        if ($data['Lager' . $i] == 0) continue;
        $waresFound = true;
        $sellPrice = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude3'], $data['Gebaeude6']);
        ?>
        <tr>
            <td><?= getItemName($i); ?></td>
            <td><?= formatWeight($data['Lager' . $i]); ?></td>
            <td><?= formatCurrency($sellPrice); ?></td>
            <td>
                <?= formatCurrency($sellPrice * market_min_sell_price); ?>
                - <?= formatCurrency($sellPrice * market_max_sell_price); ?>
            </td>
            <td>
                <a href="/?p=marktplatz_verkaufen&amp;ware=<?= $i; ?>&amp;amount=<?= $data['Lager' . $i]; ?>&amp;price=<?= $sellPrice * market_max_sell_price; ?>">Übernehmen</a>
            </td>
        </tr>
        <?php
    }

    if (!$waresFound) {
        redirectTo('/?p=marktplatz_liste', 122, __LINE__);
    }
    ?>
</table>

<div>
    <a href="/?p=marktplatz_liste">&lt;&lt; Zurück</a>
</div>
