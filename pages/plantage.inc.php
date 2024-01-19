<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Produktion');

$auftraege_db = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user'],
    job_type_factor * job_type_production, (job_type_factor * job_type_production) + job_type_factor);
$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevel($_SESSION['blm_user']);

$auftraege = array();
for ($i = 0; $i < count($auftraege_db); $i++) {
    $auftraege[$auftraege_db[$i]['item'] % job_type_factor] = $auftraege_db[$i];
}

$productionData = array();
$productionCostSum = .0;
for ($i = 1; $i <= count_wares; $i++) {
    if (!productionRequirementsMet($i, $data['Gebaeude' . building_plantage], $data['Forschung' . $i])) continue;
    $productionData[$i] = calculateProductionDataForPlayer($i, $data['Gebaeude' . building_plantage], $data['Forschung' . $i]);
    if (!array_key_exists($i, $auftraege)) {
        $productionCostSum += $productionData[$i]['Kosten'];
    }
}
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/Staroffice.webp" alt=""/>
    <span>Plantage<?= createHelpLink(1, 5); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie Ihre erforschten Obst- und Warensorten anbauen.
</p>

<div class="form Schnellanbau">
    <form action="./actions/plantage.php" method="post" id="fast_plant" data-cost-per-hour="<?= $productionCostSum; ?>"
          data-geld="<?= $data['Geld']; ?>">
        <input type="hidden" name="alles" value="1"/>
        <header>Schnellanbau</header>
        <div>
            <label for="stunden">Produziere</label>
            <input type="number" id="stunden" name="stunden" value="1" size="2" maxlength="2" min="1"
                   max="<?= Config::getInt(Config::SECTION_PLANTAGE, 'production_hours_max'); ?>"/>
            Stunde(n) von Allem.
        </div>
        <div id="pr_ko_all">Kosten: <?= formatCurrency($productionCostSum); ?></div>
        <div>
            <input type="submit" value="Abschicken" id="plant_all"
                <?= (count($auftraege) < count($productionData) && $data['Geld'] >= $productionCostSum ? '' : ' disabled="disabled"'); ?>/>
        </div>
    </form>
</div>

<?php
$cspFields = array();
for ($i = 1; $i <= count_wares; $i++) {
    $researchAttribute = 'Forschung' . $i;
    if (!productionRequirementsMet($i, $data['Gebaeude' . building_plantage], $data[$researchAttribute])) continue;
    $cspFields[] = $i;
    ?>
    <div class="form Produktion">
        <header id="p<?= $i; ?>">
            <?= getItemName($i); ?> (Stufe <?= $data[$researchAttribute]; ?>)
        </header>
        <div class="ItemImage" id="Item_<?= $i; ?>"></div>
        <div class="ProduktionDaten">
            <div><?= formatWeight($productionData[$i]['Menge']); ?> / Stunde</div>
            <div><?= formatWeight($productionData[$i]['Menge'] / 60, true, 2); ?> / Minute</div>
            <div><?= formatCurrency($productionData[$i]['Kosten'] / $productionData[$i]['Menge'], true, true, 3); ?> /
                kg
            </div>
        </div>
        <div class="Action">
            <form action="./actions/plantage.php" method="post">
                <input type="hidden" name="was" value="<?= $i; ?>"/>
                <?php
                if (!array_key_exists($i, $auftraege)) {
                    ?>
                    <div>
                        <label for="amount_<?= $i; ?>">Menge:</label>
                        <input type="text" size="4" maxlength="6" name="menge" id="amount_<?= $i; ?>"
                               class="amount_field"
                               data-id="<?= $i; ?>"
                               data-menge="<?= $productionData[$i]['Menge']; ?>"
                               data-kosten="<?= $productionData[$i]['Kosten']; ?>"
                               value="<?= $productionData[$i]['Menge']; ?>"/>
                        kg
                        <div id="pr_ko_<?= $i; ?>">Kosten: <?= formatCurrency($productionData[$i]['Kosten']); ?></div>
                    </div>
                    <input type="submit" name="anbauen" id="plant_<?= $i; ?>" value="Ware anbauen"
                    />
                    <?php
                } else {
                    $auftrag = $auftraege[$i];
                    $duration = strtotime($auftrag['finished']) - strtotime($auftrag['created']);
                    $completed = time() - strtotime($auftrag['created']);
                    $percent = $completed / $duration;
                    ?>
                    <div>
                        <div>Es läuft bereits ein Anbau!</div>
                        <div>
                            (noch <span class="countdown"><?= formatDuration($duration - $completed); ?></span>
                            verbleibend)
                        </div>
                        <div>
                            <a class="delete_plant_job"
                               data-refund="<?= formatWeight($auftrag['amount'] * $percent); ?>"
                               href="./actions/auftrag.php?id=<?= $auftrag['ID']; ?>&amp;was=<?= $i; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                               id="abort_<?= $i; ?>">Abbrechen</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </form>
        </div>
    </div>
    <?php
}
?>
