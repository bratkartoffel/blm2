<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Bioladen');

$data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);

$sumWeight = 0;
$sumMoney = 0;
$prices = array();
for ($i = 1; $i <= count_wares; $i++) {
    if ($data['Lager' . $i] == 0) continue;
    $prices[$i] = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude' . building_shop], $data['Gebaeude' . building_school]);

    $sumWeight += $data['Lager' . $i];
    $sumMoney += $data['Lager' . $i] * $prices[$i];
}
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/warehause.webp" alt=""/>
    <span>Bioladen<?= createHelpLink(1, 7); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Ihr produziertes Obst und Gemüse zum Festpreis verkaufen.<br/>
    Dieser richtet sich nach der Stufe der Schule und nach der Ausbaustufe des Bioladens.
</p>

<table class="Liste">
    <tr>
        <th>Lager</th>
        <th>Ware</th>
        <th>Preis / kg</th>
        <th>Menge / Aktion</th>
    </tr>
    <?php
    for ($i = 1; $i <= count_wares; $i++) {
        if ($data['Lager' . $i] == 0) continue;
        ?>
        <tr>
            <td id="cur_amount_<?= $i; ?>"><?= formatWeight($data['Lager' . $i]); ?></td>
            <td><label for="amount_<?= $i; ?>"><?= getItemName($i); ?></label></td>
            <td><?= formatCurrency($prices[$i]); ?></td>
            <td>
                <form action="./actions/bioladen.php" method="post">
                    <input type="hidden" name="was" value="<?= $i; ?>"/>
                    <input type="text" maxlength="6" name="menge" id="amount_<?= $i; ?>" size="4"
                           value="<?= $data['Lager' . $i]; ?>"/>
                    <input type="submit" value="Verkaufen" id="sell_<?= $i; ?>"/>
                </form>
            </td>
        </tr>
        <?php
    }

    if ($sumWeight == 0) {
        ?>
        <tr>
            <td colspan="4" class="center"><i>Ihr Lager ist leer</i></td>
        </tr>
        <?php
    } else {
        ?>
        <tr class="StorageSellAll">
            <td colspan="4">Alles (<?= formatWeight($sumWeight); ?>) für <?= formatCurrency($sumMoney); ?> verkaufen:
                <form action="./actions/bioladen.php" method="post">
                    <input type="hidden" name="alles" value="1"/>
                    <input type="submit" value="Verkaufen" id="sell_all"/>
                </form>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
