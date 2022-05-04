<?php
restrictSitter('Forschung');

$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevelAndResearchLabLevel($_SESSION['blm_user']);
if ($data['Gebaeude2'] == 0) {
    redirectTo('/?p=index', 145, __LINE__);
}

$auftraege_db = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user'], 300, 400);
$auftraege = array();
for ($i = 0; $i < count($auftraege_db); $i++) {
    $auftraege[$auftraege_db[$i]['item'] % 100] = $auftraege_db[$i];
}

?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/katomic.png" alt=""/>
        <span>Forschungszentrum<?= createHelpLink(1, 6); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

    <p>
        Hier können Sie das entsprechende Gemüse erforschen bzw. verbessern.<br/>
        Stufe 1 ermöglicht den Anbau des Gemüses, jede weitere Stufe erhöht die produzierte Menge.<br/>
    </p>

<?php
for ($i = 1; $i <= count_wares; $i++) {
    if (!researchRequirementsMet($i, $data['Gebaeude1'], $data['Gebaeude2'])) continue;

    $researchData = calculateResearchDataForPlayer($i, $data['Gebaeude2'], $data['Forschung' . $i]);
    $researchDataNext = calculateResearchDataForPlayer($i, $data['Gebaeude2'], $data['Forschung' . $i], 2);
    $researchAttribute = "Forschung" . $i;
    ?>
    <div class="form Research">
        <header id="f<?= $i; ?>">
            <?= getItemName($i); ?> (Stufe <?= $data[$researchAttribute]; ?>)
        </header>
        <img src="<?= getResearchImage($i); ?>" alt="<?= getItemName($i); ?>"/>
        <div class="ResearchDaten">
            <?php
            if (!array_key_exists($i, $auftraege)) {
                ?>
                <span>Für Stufe <?= $data[$researchAttribute] + 1; ?>:</span>
                <div>Dauer: <?= formatDuration($researchData['Dauer']); ?></div>
                <div>Kosten <?= formatCurrency($researchData['Kosten']); ?></div>
                <div>Punkte: <?= formatPoints($researchData['Punkte']); ?></div>
                <?php
            } else {
                ?>
                <span>Für Stufe <?= $data[$researchAttribute] + 2; ?>:</span>
                <div>Dauer: <?= formatDuration($researchDataNext['Dauer']); ?></div>
                <div>Kosten <?= formatCurrency($researchDataNext['Kosten']); ?></div>
                <div>Punkte: <?= formatPoints($researchDataNext['Punkte']); ?></div>
                <?php
            }
            ?>
        </div>
        <div class="Action">
            <form action="/actions/forschungszentrum.php" method="post">
                <input type="hidden" name="was" value="<?= $i; ?>"/>
                <?php
                if (!array_key_exists($i, $auftraege)) {
                    ?>
                    <input type="submit" name="forschen" id="research_<?= $i; ?>" value="Forschen"
                           onclick="return submit(this);" <?= ($researchData['Kosten'] > $data['Geld']) ? ' disabled="disabled"' : ''; ?> />
                    <?php
                } else {
                    $auftrag = $auftraege[$i];
                    $duration = strtotime($auftrag['finished']) - strtotime($auftrag['created']);
                    $completed = time() - strtotime($auftrag['created']);
                    $percent = $completed / $duration;
                    ?>
                    <div class="Running">
                        <div>Es läuft bereits eine Forschung!</div>
                        <div>
                            (noch <span class="countdown"><?= formatDuration($duration - $completed); ?></span>
                            verbleibend)
                        </div>
                        <div>
                            <a onclick="return confirmAbort('<?= formatCurrency($auftrag['cost'] * action_retract_rate); ?>', '<?= formatPercent(action_retract_rate); ?>');"
                               href="/actions/auftrag.php?id=<?= $auftrag['ID']; ?>&amp;back=forschungszentrum&amp;was=<?= $i; ?>">Abbrechen</a>
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
