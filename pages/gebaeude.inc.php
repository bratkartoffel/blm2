<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Gebaeude');

$auftraege_db = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user'], 100, 200);
$data = Database::getInstance()->getPlayerMoneyAndBuildingLevelsAndExpenseMafia($_SESSION['blm_user']);

$auftraege = array();
for ($i = 0; $i < count($auftraege_db); $i++) {
    $auftraege[$auftraege_db[$i]['item'] % 100] = $auftraege_db[$i];
}

function printBuildingInformation($playerData, $auftraege, $buildingId, $buildingDescription)
{
    $buildingAttribute = 'Gebaeude' . $buildingId;
    ?>
    <div class="form Gebaeude">
        <header id="g<?= $buildingId; ?>">
            <?= getBuildingName($buildingId); ?> (Stufe <?= $playerData[$buildingAttribute]; ?>)
        </header>
        <div class="BuildingImage" id="Building_<?= $buildingId; ?>"></div>
        <div class="Beschreibung">
            <?= $buildingDescription; ?>
        </div>
        <div class="Information">
            <div class="Upgrade">
                <?php
                if (!array_key_exists($buildingId, $auftraege)) {
                    $buildingData = calculateBuildingDataForPlayer($buildingId, $playerData);
                    $nextLevel = $playerData[$buildingAttribute] + 1;
                    $currentDuration = null;
                    $currentKosten = null;
                    $currentID = null;
                } else {
                    $buildingData = calculateBuildingDataForPlayer($buildingId, $playerData, 2);
                    $nextLevel = $playerData[$buildingAttribute] + 2;
                    $currentDuration = strtotime($auftraege[$buildingId]['finished']) - time();
                    $currentKosten = $auftraege[$buildingId]['cost'];
                    $currentID = $auftraege[$buildingId]['ID'];
                }
                $nextKosten = $buildingData['Kosten'];
                $nextDauer = $buildingData['Dauer'];
                $nextPunkte = $buildingData['Punkte'];
                ?>
                <header>Für Stufe <?= $nextLevel; ?></header>
                <div>Kosten: <?= formatCurrency($nextKosten); ?></div>
                <div>Dauer: <?= formatDuration($nextDauer); ?></div>
                <div>Punkte: <?= formatPoints($nextPunkte); ?></div>
            </div>
            <div class="Action">
                <form action="/actions/gebaeude.php" method="post">
                    <input type="hidden" name="was" value="<?= $buildingId; ?>"/>
                    <?php
                    if ($currentDuration != null) {
                        ?>
                        <div>Es läuft bereits ein Ausbau!</div>
                        <div>
                            (noch <span class="countdown"><?= formatDuration($currentDuration); ?></span> verbleibend)
                        </div>
                        <div>
                            <a onclick="return confirmAbort('<?= formatCurrency($currentKosten * Config::getFloat(Config::SECTION_BASE, 'cancel_refund')); ?>', '<?= formatPercent(Config::getFloat(Config::SECTION_BASE, 'cancel_refund')); ?>');"
                               href="/actions/auftrag.php?id=<?= $currentID; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                               id="abort_<?= $buildingId; ?>">Abbrechen</a>
                        </div>
                        <?php
                    } else {
                        ?>
                        <input type="submit" value="Gebäude ausbauen" id="build_<?= $buildingId; ?>"
                              <?= ($playerData['Geld'] >= $nextKosten ? '' : ' disabled="disabled"'); ?>/>
                        <?php
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
    <?php
}

?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/kfm_home.webp" alt=""/>
        <span>Gebäude<?= createHelpLink(1, 4); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

    <p>
        Hier können Sie alle Ihre Gebäude ausbauen und ihre aktuelle Stufe sehen.
    </p>

    <script>
        reloadOnCountdown = true;
    </script>

<?php
if (buildingRequirementsMet(1, $data)) {
    printBuildingInformation($data, $auftraege, 1,
        'Dies ist das wichtigste Gebäude des Spiels.<br/>
Je weiter Sie die Plantage ausbauen, desto mehr Gemüse kann schneller angebaut werden.
Ausserdem können auch neue Gemüsesorten erst mit einem gewissen Level angebaut werden.');
}

if (buildingRequirementsMet(2, $data)) {
    printBuildingInformation($data, $auftraege, 2,
        'Dies ist ebenfalls ein sehr wichtiges Gebäude in Ihrem Betrieb.<br/>
Hier können Sie neue Gemüsesorten erforschen (damit Sie sie anbauen können)
oder bestehende verbessern (schnellerer Anbau).<br/>
Ausserdem werden neue Gemüsesorten bekannt, je höher das Forschungszentrum ist und die
Forschungszeit für eine Stufe um ' . formatPercent(Config::getFloat(Config::SECTION_RESEARCH_LAB, 'bonus_factor')) . '
je Stufe gesenkt.');
}

if (buildingRequirementsMet(3, $data)) {
    printBuildingInformation($data, $auftraege, 3,
        'Dieses Gebäude ist genau so wichtig, wie die Plantage und das Forschungszentrum,
denn hier können Sie Ihre Gemüse verkaufen.<br/>
Ausserdem steigt Ihr Grundeinkommen und der Preis den Sie pro Kilogramm erhalten mit jeder
Stufe.');
}

if (buildingRequirementsMet(4, $data)) {
    printBuildingInformation($data, $auftraege, 4,
        'Dieses Gebäude hat zwar nicht viel mit "Biowaren" zu tun, <br/>
aber Sie haben erkannt, dass alleine mit Biolebensmitteln kein Geld zu verdienen ist.<br/>
Deshalb kann man sich hier einen Dönerstand mieten, der das Grundeinkommen des Spielers erhöht.');
}

if (buildingRequirementsMet(6, $data)) {
    printBuildingInformation($data, $auftraege, 6,
        'Hier bilden Sie Ihre Verkäufer aus, so dass diese in Ihrem Bioladen mehr Gewinn erzielen können.<br/>
Dabei steigt der Gewinn pro Kilo und Stufe um ' . formatCurrency(Config::getInt(Config::SECTION_SHOP, 'item_price_school_bonus')) . '!');
}

if (buildingRequirementsMet(5, $data)) {
    printBuildingInformation($data, $auftraege, 5,
        'Dieses Gebäude senkt die Ausbauzeiten sämtlicher Gebäude um ' . formatPercent(Config::getFloat(Config::SECTION_BUILDING_YARD, 'bonus_factor')) . ' pro
Stufe.<br/>Der Bauhof wird erst beim späten Spielverlauf wichtig.');
}

if (buildingRequirementsMet(7, $data)) {
    printBuildingInformation($data, $auftraege, 7,
        'Dieses Gebäude bietet den einzigen Schutz gegen Angriffe der Mafia. Dabei senkt jede Stufe des Zauns
die Erfolgschancen des Gegners um ' . formatPercent(Config::getFloat(Config::SECTION_FENCE, 'mafia_bonus')) . '.<br/>
Dies ist das teuerste Gebäude und dauert auch am längsten.');
}

if (buildingRequirementsMet(8, $data)) {
    printBuildingInformation($data, $auftraege, 8,
        'Dieses Gebäude ist das genaue Gegenstück zum Zaun.<br/>
Je weiter Sie die Pizzeria ausbauen, desto mehr Mafiosi lassen sich in der Stadt nieder und desto
höher
sind Ihre Erfolgschancen. Dabei steigen die Chancen pro Stufe um ' . formatPercent(Config::getFloat(Config::SECTION_PIZZERIA, 'mafia_bonus')) . '.');
}
