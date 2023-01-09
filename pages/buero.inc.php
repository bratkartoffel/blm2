<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$data = Database::getInstance()->getInformationForBuero($_SESSION['blm_user']);
$rates = calculateSellRates();
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kspread.webp" alt=""/>
    <span>Büro<?= createHelpLink(1, 8); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie verschiedene Statistiken zu Ihrem Account.
</p>

<div class="Buero">
    <table class="Liste">
        <tr>
            <th colspan="5">Kosten / Erlösübersicht</th>
        </tr>
        <tr>
            <th>Ware</th>
            <th>Kosten / kg</th>
            <th>Erlös<br/>in %</th>
            <th>Erlös / kg</th>
            <th>Gewinn / kg</th>
        </tr>
        <?php
        for ($i = 1; $i <= count_wares; $i++) {
            if ($data['Forschung' . $i] == 0) continue;
            $temp = "Forschung" . $i;
            $prodData = calculateProductionDataForPlayer($i, $data['Gebaeude1'], $data['Forschung' . $i]);
            $sellPrice = calculateSellPrice($i, $data['Forschung' . $i], $data['Gebaeude3'], $data['Gebaeude6'], $rates[$i]);
            $costPerKg = $prodData['Kosten'] / $prodData['Menge'];
            $rateStep = (wares_rate_max - wares_rate_min) / 3;
            if ($rates[$i] >= wares_rate_max - $rateStep) {
                $color = 'RateGreen';
            } else if ($rates[$i] >= wares_rate_min + $rateStep) {
                $color = 'RateYellow';
            } else {
                $color = 'RateRed';
            }
            ?>
            <tr class="<?= $color; ?>">
                <td><?= getItemName($i); ?></td>
                <td><?= formatCurrency($costPerKg, true, true, 3); ?></td>
                <td><?= formatPercent($rates[$i], true, 0); ?></td>
                <td><?= formatCurrency($sellPrice); ?></td>
                <td><?= formatCurrency($sellPrice - $costPerKg); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    $EinnahmenGesamt = $data['EinnahmenVerkauf'] + $data['EinnahmenGebaeude'] + $data['EinnahmenZinsen']
        + $data['EinnahmenMarkt'] + $data['EinnahmenVertraege'] + $data['EinnahmenMafia'];
    $AusgabenGesamt = $data['AusgabenGebaeude'] + $data['AusgabenForschung'] + $data['AusgabenProduktion']
        + $data['AusgabenZinsen'] + $data['AusgabenMarkt'] + $data['AusgabenVertraege'] + $data['AusgabenMafia'];
    $difference = starting_values['mitglieder']['Geld'] + starting_values['mitglieder']['Bank'] + $EinnahmenGesamt - $AusgabenGesamt - $data['Geld'] - $data['Bank'];
    if ($difference < 0) {
        $EinnahmenSonstige = abs($difference);
        $AusgabenSonstige = 0;
    } else {
        $EinnahmenSonstige = 0;
        $AusgabenSonstige = abs($difference);
    }
    $AusgabenGesamt += $AusgabenSonstige;
    $EinnahmenGesamt += $EinnahmenSonstige;
    ?>
    <table class="Liste EinnahmenAusgaben">
        <tr>
            <th colspan="3">Ausgaben / Einnahmen:</th>
        </tr>
        <tr>
            <td></td>
            <td>Einnahmen Verkauf:</td>
            <td id="b_i_1"><?= formatCurrency($data['EinnahmenVerkauf']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Gebäude:</td>
            <td id="b_i_2"><?= formatCurrency($data['EinnahmenGebaeude']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Zinsen:</td>
            <td id="b_i_3"><?= formatCurrency($data['EinnahmenZinsen']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Marktplatz:</td>
            <td id="b_i_4"><?= formatCurrency($data['EinnahmenMarkt']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Verträge:</td>
            <td id="b_i_5"><?= formatCurrency($data['EinnahmenVertraege']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Mafia:</td>
            <td id="b_i_6"><?= formatCurrency($data['EinnahmenMafia']); ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Einnahmen Sonstige:</td>
            <td id="b_i_7"><?= formatCurrency($EinnahmenSonstige); ?></td>
        </tr>
        <tr class="Separator">
            <td>=</td>
            <td>Gesamteinnahmen:</td>
            <td id="b_i_8"><?= formatCurrency($EinnahmenGesamt); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Gebäude:</td>
            <td id="b_s_1"><?= formatCurrency($data['AusgabenGebaeude']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Forschung:</td>
            <td id="b_s_2"><?= formatCurrency($data['AusgabenForschung']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Produktion:</td>
            <td id="b_s_3"><?= formatCurrency($data['AusgabenProduktion']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Zinsen:</td>
            <td id="b_s_4"><?= formatCurrency($data['AusgabenZinsen']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Marktplatz:</td>
            <td id="b_s_5"><?= formatCurrency($data['AusgabenMarkt']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Verträge:</td>
            <td id="b_s_6"><?= formatCurrency($data['AusgabenVertraege']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Mafia:</td>
            <td id="b_s_7"><?= formatCurrency($data['AusgabenMafia']); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Ausgaben Sonstiges:</td>
            <td id="b_s_8"><?= formatCurrency($AusgabenSonstige); ?></td>
        </tr>
        <tr class="Separator">
            <td>=</td>
            <td id="b_s_9">Gesamtausgaben:</td>
            <td><?= formatCurrency($AusgabenGesamt); ?></td>
        </tr>
        <tr>
            <td>=&gt;</td>
            <td id="b_guv">Gewinn / Verlust:</td>
            <td><?= formatCurrency($EinnahmenGesamt - $AusgabenGesamt); ?></td>
        </tr>
    </table>
    <?php
    $pointsPlus = 0;
    $pointsMinus = 0;
    ?>
    <table class="Liste PunktePlusMinus">
        <tr>
            <th colspan="3"> Punkteverteilung</th>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>Gebäude</td>
            <td id="b_p_1"><?= formatPoints($data['GebaeudePlus']);
                $pointsPlus += $data['GebaeudePlus']; ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Forschung</td>
            <td id="b_p_2"><?= formatPoints($data['ForschungPlus']);
                $pointsPlus += $data['ForschungPlus']; ?></td>
        </tr>
        <tr>
            <td>+</td>
            <td>Mafia</td>
            <td id="b_p_3"><?= formatPoints($data['MafiaPlus']);
                $pointsPlus += $data['MafiaPlus']; ?></td>
        </tr>
        <tr class="Separator">
            <td>=</td>
            <td>Gesamtplus</td>
            <td id="b_p_4"><?= formatPoints($pointsPlus); ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Mafia</td>
            <td id="b_p_5"><?= formatPoints($data['MafiaMinus']);
                $pointsMinus += $data['MafiaMinus']; ?></td>
        </tr>
        <tr>
            <td>-</td>
            <td>Kriege</td>
            <td id="b_p_6"><?= formatPoints($data['KriegMinus']);
                $pointsMinus += $data['KriegMinus']; ?></td>
        </tr>
        <tr class="Separator">
            <td>=</td>
            <td>Gesamtminus</td>
            <td><?= formatPoints($pointsMinus); ?></td>
        </tr>
        <tr>
            <td>=&gt;</td>
            <td>Gesamtpunkte</td>
            <td id="b_p_7"><?= formatPoints($pointsPlus - $pointsMinus); ?></td>
        </tr>
    </table>
</div>
