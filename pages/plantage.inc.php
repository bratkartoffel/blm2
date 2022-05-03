<?php
restrictSitter('Produktion');

$auftraege_db = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user'], 200, 300);
$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevel($_SESSION['blm_user']);

$auftraege = array();
for ($i = 0; $i < count($auftraege_db); $i++) {
    $auftraege[$auftraege_db[$i]['item'] % 100] = $auftraege_db[$i];
}

$productionData = array();
$productionCostSum = .0;
for ($i = 1; $i <= count_wares; $i++) {
    if (!productionRequirementsMet($i, $data['Gebaeude1'], $data['Forschung' . $i])) continue;
    $productionData[$i] = calculateProductionDataForPlayer($i, $data['Gebaeude1'], $data['Forschung' . $i]);
    if (!array_key_exists($i, $auftraege)) {
        $productionCostSum += $productionData[$i]['Kosten'];
    }
}
?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/Staroffice.png" alt=""/>
        <span>Plantage<?= createHelpLink(1, 5); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

    <p>
        Hier können Sie Ihre erforschten Obst- und Warensorten anbauen.
    </p>

    <div class="form Schnellanbau">
        <form action="/actions/plantage.php" method="post">
            <input type="hidden" name="alles" value="1"/>
            <header>Schnellanbau</header>
            <div>
                <label for="stunden">Produziere</label>
                <input type="number" id="stunden" name="stunden" value="1" size="2" maxlength="2" min="1"
                       max="<?= production_hours_max; ?>"
                       onchange="RechneProduktionsKosten(1, <?= $productionCostSum; ?>, this.value,
                       <?= $data['Geld']; ?>, document.getElementById('pr_ko_all'), document.getElementById('anbauen_all'));"/>
                Stunde(n) von Allem.
            </div>
            <div id="pr_ko_all">Kosten: <?= formatCurrency($productionCostSum); ?></div>
            <input type="submit" value="Abschicken" id="plant_all"
                   onclick="return submit(this);"<?= (count($auftraege) < count($productionData) && $data['Geld'] >= $productionCostSum ? '' : ' disabled="disabled"'); ?>/>
        </form>
    </div>

<?php
for ($i = 1; $i <= count_wares; $i++) {
    $researchAttribute = 'Forschung' . $i;
    if (!productionRequirementsMet($i, $data['Gebaeude1'], $data[$researchAttribute])) continue;
    ?>
    <div class="form Produktion">
        <header id="p<?= $i; ?>">
            <?= getItemName($i); ?> (Stufe <?= $data[$researchAttribute]; ?>)
        </header>
        <img src="<?= getItemImage($i); ?>" alt=""/>
        <div class="ProduktionDaten">
            <div><?= formatWeight($productionData[$i]['Menge']); ?> / Stunde</div>
            <div><?= formatWeight($productionData[$i]['Menge'] / 60, true, 2); ?> / Minute</div>
            <div><?= formatCurrency($productionData[$i]['Kosten'] / $productionData[$i]['Menge'], true, true, 3); ?> /
                kg
            </div>
        </div>
        <div class="Action">
            <form action="/actions/plantage.php" method="post">
                <input type="hidden" name="was" value="<?= $i; ?>"/>
                <?php
                if (!array_key_exists($i, $auftraege)) {
                    ?>
                    <div>
                        <label for="menge<?= $i; ?>">Menge:</label>
                        <input type="text" size="4" maxlength="6" name="menge" id="amount_<?= $i; ?>"
                               value="<?= $productionData[$i]['Menge']; ?>"
                               onkeyup="RechneProduktionsKosten(<?= $productionData[$i]['Menge']; ?>, <?= $productionData[$i]['Kosten']; ?>, this.value,
                               <?= $data['Geld']; ?>, document.getElementById('pr_ko_<?= $i; ?>'), document.getElementById('anbauen_<?= $i; ?>'));"/>
                        kg
                        <div id="pr_ko_<?= $i; ?>">Kosten: <?= formatCurrency($productionData[$i]['Kosten']); ?></div>
                    </div>
                    <input type="submit" name="anbauen" id="plant_<?= $i; ?>" value="Ware anbauen"
                           onclick="return submit(this);"/>
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
                            <a onclick="return confirm('Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen die Kosten nicht zurück erstattet, lediglich die bisher produzierte Menge '
                                    + '(~ <?= formatWeight($auftrag['amount'] * $percent); ?>) wird Ihnen gut geschrieben.!');"
                               href="/actions/auftrag.php?id=<?= $auftrag['ID']; ?>&amp;back=plantage&amp;was=<?= $i; ?>">Abbrechen</a>
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
