<?php
$data = Database::getInstance()->getInformationForBuero($_SESSION['blm_user']);
$rates = calculateSellRates();
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/buero.png" alt=""/>
    <span>Büro<?= createHelpLink(1, 8); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie verschiedene Statistiken zu Ihrem Account.
</p>

<div class="Buero">
    <div>
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
        $EinnahmenGesamt = 0;
        $AusgabenGesamt = 0;
        ?>
        <table class="Liste EinnahmenAusgaben">
            <tr>
                <th colspan="3">Ausgaben / Einnahmen:</th>
            </tr>
            <tr>
                <td></td>
                <td>Einnahmen Verkauf:</td>
                <td id="b_i_1"><?= formatCurrency($data['EinnahmenVerkauf']);
                    $EinnahmenGesamt += $data['EinnahmenVerkauf']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Einnahmen Gebäude:</td>
                <td id="b_i_2"><?= formatCurrency($data['EinnahmenGebaeude']);
                    $EinnahmenGesamt += $data['EinnahmenGebaeude']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Einnahmen Zinsen:</td>
                <td id="b_i_3"><?= formatCurrency($data['EinnahmenZinsen']);
                    $EinnahmenGesamt += $data['EinnahmenZinsen']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Einnahmen Marktplatz:</td>
                <td id="b_i_4"><?= formatCurrency($data['EinnahmenMarkt']);
                    $EinnahmenGesamt += $data['EinnahmenMarkt']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Einnahmen Verträge:</td>
                <td id="b_i_5"><?= formatCurrency($data['EinnahmenVertraege']);
                    $EinnahmenGesamt += $data['EinnahmenVertraege']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Einnahmen Mafia:</td>
                <td id="b_i_6"><?= formatCurrency($data['EinnahmenMafia']);
                    $EinnahmenGesamt += $data['EinnahmenMafia']; ?></td>
            </tr>
            <tr class="Separator">
                <td>=</td>
                <td>Gesamteinnahmen:</td>
                <td id="b_i_7"><?= formatCurrency($EinnahmenGesamt); ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Gebäude:</td>
                <td id="b_s_1"><?= formatCurrency($data['AusgabenGebaeude']);
                    $AusgabenGesamt += $data['AusgabenGebaeude'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Forschung:</td>
                <td id="b_s_2"><?= formatCurrency($data['AusgabenForschung']);
                    $AusgabenGesamt += $data['AusgabenForschung'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Produktion:</td>
                <td id="b_s_3"><?= formatCurrency($data['AusgabenProduktion']);
                    $AusgabenGesamt += $data['AusgabenProduktion'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Zinsen:</td>
                <td id="b_s_4"><?= formatCurrency($data['AusgabenZinsen']);
                    $AusgabenGesamt += $data['AusgabenZinsen'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Marktplatz:</td>
                <td id="b_s_5"><?= formatCurrency($data['AusgabenMarkt']);
                    $AusgabenGesamt += $data['AusgabenMarkt'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Verträge:</td>
                <td id="b_s_6"><?= formatCurrency($data['AusgabenVertraege']);
                    $AusgabenGesamt += $data['AusgabenVertraege'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Mafia:</td>
                <td id="b_s_7"><?= formatCurrency($data['AusgabenMafia']);
                    $AusgabenGesamt += $data['AusgabenMafia'];
                    ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Ausgaben Sonstiges:</td>
                <td id="b_s_8"><?= formatCurrency($data['AusgabenSonstiges']);
                    $AusgabenGesamt += $data['AusgabenSonstiges'];
                    ?></td>
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
    </div>
    <div>
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
                <td><?= formatPoints($data['GebaeudePlus']);
                    $pointsPlus += $data['GebaeudePlus']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Forschung</td>
                <td><?= formatPoints($data['ForschungPlus']);
                    $pointsPlus += $data['ForschungPlus']; ?></td>
            </tr>
            <tr>
                <td>+</td>
                <td>Mafia</td>
                <td><?= formatPoints($data['MafiaPlus']);
                    $pointsPlus += $data['MafiaPlus']; ?></td>
            </tr>
            <tr class="Separator">
                <td>=</td>
                <td>Gesamtplus</td>
                <td><?= formatPoints($pointsPlus); ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Mafia</td>
                <td><?= formatPoints($data['MafiaMinus']);
                    $pointsMinus += $data['MafiaMinus']; ?></td>
            </tr>
            <tr>
                <td>-</td>
                <td>Kriege</td>
                <td><?= formatPoints($data['KriegMinus']);
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
                <td><?= formatPoints($pointsPlus - $pointsMinus); ?></td>
            </tr>
        </table>
    </div>
</div>
