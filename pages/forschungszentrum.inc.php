<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Forschung');

$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevelAndResearchLabLevel($_SESSION['blm_user']);
if ($data['Gebaeude' . building_research_lab] == 0) {
    redirectTo('/?p=gebaeude', 145, "g2");
}

$auftraege_db = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user'],
    job_type_factor * job_type_research, (job_type_factor * job_type_research) + job_type_factor);
$auftraege = array();
for ($i = 0; $i < count($auftraege_db); $i++) {
    $auftraege[$auftraege_db[$i]['item'] % job_type_factor] = $auftraege_db[$i];
}

?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/katomic.webp" alt=""/>
    <span>Forschungszentrum<?= createHelpLink(1, 6); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier können Sie das entsprechende Gemüse erforschen bzw. verbessern.<br/>
    Stufe 1 ermöglicht den Anbau des Gemüses, jede weitere Stufe erhöht die produzierte Menge.<br/>
</p>

<?php
for ($i = 1; $i <= count_wares; $i++) {
    if (!researchRequirementsMet($i, $data['Gebaeude' . building_plantage], $data['Gebaeude' . building_research_lab])) continue;

    $researchData = calculateResearchDataForPlayer($i, $data['Gebaeude' . building_research_lab], $data['Forschung' . $i]);
    $researchDataNext = calculateResearchDataForPlayer($i, $data['Gebaeude' . building_research_lab], $data['Forschung' . $i], 2);
    $researchAttribute = "Forschung" . $i;
    ?>
    <div class="form Research">
        <header id="f<?= $i; ?>">
            <?= getItemName($i); ?> (Stufe <?= $data[$researchAttribute]; ?>)
        </header>
        <div class="ItemImage" id="Item_<?= $i; ?>">
            <div class="ResearchOverlay"></div>
        </div>
        <div class="ResearchDaten">
            <?php
            if (!array_key_exists($i, $auftraege)) {
                ?>
                <span>Für Stufe <?= $data[$researchAttribute] + 1; ?>:</span>
                <div>Dauer: <?= formatDuration($researchData['Dauer']); ?></div>
                <div>Kosten <?= formatCurrency($researchData['Kosten']); ?></div>
                <?php
            } else {
                ?>
                <span>Für Stufe <?= $data[$researchAttribute] + 2; ?>:</span>
                <div>Dauer: <?= formatDuration($researchDataNext['Dauer']); ?></div>
                <div>Kosten <?= formatCurrency($researchDataNext['Kosten']); ?></div>
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
                        <?= ($researchData['Kosten'] > $data['Geld']) ? ' disabled="disabled"' : ''; ?> />
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
                            <a class="delete_job"
                               data-refund="<?= formatCurrency($auftrag['cost'] * Config::getFloat(Config::SECTION_BASE, 'cancel_refund')); ?>"
                               data-percent="<?= formatPercent(Config::getFloat(Config::SECTION_BASE, 'cancel_refund')); ?>"
                               href="/actions/auftrag.php?id=<?= $auftrag['ID']; ?>&amp;was=<?= $i; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
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

<script nonce="<?= getCspNonce(); ?>">
    for (let deleteLink of document.getElementsByClassName('delete_job')) {
        deleteLink.onclick = () => confirmAbort(deleteLink.getAttribute('data-refund'), deleteLink.getAttribute('data-percent'));
    }
</script>
