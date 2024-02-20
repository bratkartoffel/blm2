<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Vertraege');

$empfaenger = getOrDefault($_GET, 'empfaenger');
$ware = getOrDefault($_GET, 'ware', 1);
$menge = getOrDefault($_GET, 'menge', 0);
$preis = getOrDefault($_GET, 'preis', .0);

if ($ware <= 0 || $ware > count_wares) {
    redirectTo('/?p=bioladen', 112);
}
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/kfax.webp" alt=""/>
    <span>Neuen Vertrag verfassen<?= createHelpLink(1, 10); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Waren gegen Bezahlung an einen anderen Spieler verschicken. Der Preis muss mindestens der aktuelle
    Preis im Laden sein und darf den doppelten Wert nicht übersteigen.
</p>

<?php
$data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);
?>
<div class="form VertragNeu">
    <form action="./actions/vertraege.php" method="post">
        <input type="hidden" name="a" value="1"/>
        <header>Vertragswerte</header>
        <div>
            <label for="ware">Ware</label>
            <?= createWarenDropdown($ware, 'ware', false, $data); ?>
        </div>
        <div>
            <label for="menge">Menge</label>
            <input type="number" min="1" maxlength="5" name="menge" id="menge" value="<?= $menge; ?>"/>
        </div>
        <div>
            <label for="preis">Preis</label>
            <input type="number" min="1" step="0.01" maxlength="5" name="preis" id="preis" value="<?= $preis; ?>"/>
        </div>
        <div>
            <label for="empfaenger">Empfänger</label>
            <input type="text" name="empfaenger" id="empfaenger" value="<?= escapeForOutput($empfaenger); ?>"/>
        </div>
        <div>
            <input type="submit" value="Absenden"/>
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
    for ($i = 1; $i <= count_wares; $i++) {
        if ($data['Lager' . $i] == 0) continue;
        $waresFound = true;
        $sellPrice = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude' . building_shop], $data['Gebaeude' . building_school]);
        ?>
        <tr>
            <td><?= getItemName($i); ?></td>
            <td><?= formatWeight($data['Lager' . $i]); ?></td>
            <td><?= formatCurrency($sellPrice); ?></td>
            <td>
                <?= formatCurrency($sellPrice * Config::getFloat(Config::SECTION_CONTRACT, 'min_price')); ?>
                - <?= formatCurrency($sellPrice * Config::getFloat(Config::SECTION_CONTRACT, 'max_price')); ?>
            </td>
            <td>
                <a href="./?p=vertraege_neu&amp;empfaenger=<?= urlencode($empfaenger); ?>&amp;ware=<?= $i; ?>&amp;menge=<?= $data['Lager' . $i]; ?>&amp;preis=<?= $sellPrice * Config::getFloat(Config::SECTION_CONTRACT, 'max_price'); ?>">Übernehmen</a>
            </td>
        </tr>
        <?php
    }

    if (!$waresFound) {
        redirectTo('/?p=vertraege_liste', 122, __LINE__);
    }
    ?>
</table>

<div>
    <a href="./?p=vertraege_liste">&lt;&lt; Zurück</a>
</div>
