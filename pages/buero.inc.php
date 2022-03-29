<?php
/**
 * Wird in die index.php eingebunden; Seite mit Statistiken zum Account (ähnlich BWL)
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

include("include/preise.inc.php");        // Hier brauchen wir noch die Verkaufspreise der Waren
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/buero.png" alt="B&uuml;ro"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Das B&uuml;ro
            <a href="./?p=hilfe&amp;mod=1&amp;cat=8"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier sehen Sie verschiedene Statistiken zu Ihrem Account.<br/>
</b>
<br/>
<table>
    <tr>
        <td style="vertical-align: top;">
            <table class="Liste" style="width: 350px; margin-right: 25px;" cellspacing="0">
                <tr>
                    <th colspan="4">Kosten / Erl&ouml;s&uuml;bersicht</th>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Ware</td>
                    <td style="font-weight: bold;">Kosten / kg</td>
                    <td style="font-weight: bold;">Erl&ouml;s / kg</td>
                    <td style="font-weight: bold;">Gewinn / kg</td>
                </tr>
                <?php
                for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Rennt alle Forschungen durch
                    $temp = "Forschung" . $i;
                    if ($ich->$temp > 0) {        // Gibt nur die Kurse der Waren aus, welche wir schon erforscht haben.
                        $produktion_menge = ($i - 1) * PRODUKTIONS_WAREN_FAKTOR_MENGE + $Produktion->BasisMenge + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE);        // Berechnet, wieviel produziert wird
                        $produktion_kosten = $Produktion->BasisKosten + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN);                    // und berechnet, was es uns kostet

                        $kosten_pro_kg = $produktion_kosten / $produktion_menge;        // Rechnet nun die Kosten / kg aus...
                        $erloes_pro_kg = $Preis[$i];                                                            // ... und auch gleich den Erl�s / kg

                        echo '<tr>
											<td>' . WarenName($i) . '</td>
											<td>' . number_format($kosten_pro_kg, 2, ',', '.') . ' ' . $Currency . '</td>
											<td>' . number_format($erloes_pro_kg, 2, ',', '.') . ' ' . $Currency . '</td>
											<td style="font-weight: bold;">' . number_format($erloes_pro_kg - $kosten_pro_kg, 2, ',', '.') . ' ' . $Currency . '</td>
										</tr>';    // Gibt dann die Kosten, den Erl�s und den somit enstehenden Gewinn pro Kilo aus
                    }
                }
                ?>
            </table>
            <table class="Liste" cellspacing="0" style="width: 350px; margin-top: 30px;">
                <tr>
                    <th colspan="4">Aktuelle Marktkurse (<?= date("H") . ":00:00 - " . date("H") . ":59:59"; ?>):</th>
                </tr>
                <tr>
                    <th>Ware</th>
                    <th>Erl&ouml;s<br/>in %</th>
                    <th>Erl&ouml;s<br/>in <?= $Currency; ?></th>
                </tr>
                <?php
                for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Rennt alle Waren durch
                    $temp = "Forschung" . $i;
                    if ($ich->$temp > 0) {            // Druckt nur die Kurse der Waren, die wir schon erforscht haben
                        if ($KursWare[$i] >= 0.92) {                // Wenn der Kurs über 91 % liegt, dann schreib es gr�m
                            echo '<tr class="Green">';
                        } else {
                            if ($KursWare[$i] >= 0.82) {        // Wenn der Kurs �ber 81 % liegt, dann gelb
                                echo '<tr class="Yellow">';
                            } else {
                                echo '<tr class="Red">';            // Ansonsten (zwischen 75% und 81%) rot
                            }
                        }

                        echo '<td>' . WarenName($i) . ':</td>
										<td>' . (100 * $KursWare[$i]) . ' %</td>
										<td style="font-weight: bold;">' . number_format($Preis[$i], 2, ",", ".") . ' ' . $Currency . '</td>
									</tr>';        // Gib das Ergebnis aus ;)
                    }
                }
                ?>
            </table>
        </td>
        <td style="vertical-align: top;">
            <table class="Liste" style="width: 300px;" cellspacing="0">
                <tr>
                    <th colspan="3">Ausgaben / Einnahmen:</th>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>Einnahmen Verkauf:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenVerkauf, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinnahmenVerkauf;
                        ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Einnahmen Geb&auml;ude:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenGebaeude, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinnahmenGebaeude;
                        ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Einnahmen Zinsen:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenZinsen, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinnahmenZinsen;
                        ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Einnahmen Marktplatz:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenMarkt, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinnahmenMarkt;
                        ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Einnahmen Vertr&auml;ge:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenVertraege, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinahmenVertraege;
                        ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Einnahmen Mafia:</td>
                    <td style="text-align: right;"><?= number_format($ich->EinnahmenMafia, 2, ',', '.') . " " . $Currency;
                        $EinnahmenGesamt += $ich->EinnahmenMafia;
                        ?></td>
                </tr>
                <tr>
                    <td>=</td>
                    <td>Gesamteinnahmen:</td>
                    <td style="text-align: right;"><?= number_format($EinnahmenGesamt, 2, ",", ".") . " " . $Currency;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Geb&auml;ude:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenGebaeude, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenGebaeude;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Forschung:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenForschung, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenForschung;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Produktion:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenProduktion, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenProduktion;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Zinsen:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenZinsen, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenZinsen;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Marktplatz:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenMarkt, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenMarkt;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Vertr&auml;ge:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenVertraege, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenVertraege;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Mafia:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenMafia, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenMafia;
                        ?></td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Ausgaben Sonstiges:</td>
                    <td style="text-align: right;"><?= number_format($ich->AusgabenSonstiges, 2, ',', '.') . " " . $Currency;
                        $AusgabenGesamt += $ich->AusgabenSonstiges;
                        ?></td>
                </tr>
                <tr>
                    <td>=</td>
                    <td>Gesamtausgaben:</td>
                    <td style="text-align: right;"><?= number_format($AusgabenGesamt, 2, ",", ".") . " " . $Currency;
                        ?></td>
                </tr>
                <tr>
                    <td>=&gt;</td>
                    <td>Gewinn / Verlust:</td>
                    <td style="text-align: right;"><?php
                        $Gewinn = $EinnahmenGesamt - $AusgabenGesamt;

                        echo number_format($Gewinn, 2, ",", ".") . " " . $Currency;
                        ?></td>
                </tr>
            </table>
            <table class="Liste" style="width: 300px; margin-top: 30px;">
                <tr>
                    <th colspan="3">
                        Punkteverteilung
                    </th>
                </tr>
                <tr>
                    <td style="width: 15px;">&nbsp;</td>
                    <td>Gebäude</td>
                    <td style="text-align: right;"><?= number_format($ich->GebaeudePlus, 0, ",", "."); ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Forschung</td>
                    <td style="text-align: right;"><?= number_format($ich->ForschungPlus, 0, ",", "."); ?></td>
                </tr>
                <tr>
                    <td>+</td>
                    <td>Mafia</td>
                    <td style="text-align: right;"><?= number_format($ich->MafiaPlus, 0, ",", "."); ?></td>
                </tr>
                <tr>
                    <td>=</td>
                    <td>Gesamtplus</td>
                    <td style="text-align: right;"><?= number_format($ich->GebaeudePlus + $ich->ForschungPlus + $ich->ProduktionPlus + $ich->MafiaPlus, 0, ",", "."); ?></td>
                <tr>
                    <td>-</td>
                    <td>Mafia</td>
                    <td style="text-align: right;"><?= number_format($ich->MafiaMinus, 0, ",", "."); ?></td>
                </tr>
                <tr>
                    <td>=</td>
                    <td>Gesamtpunkte</td>
                    <td style="text-align: right;"><?= number_format($ich->GebaeudePlus + $ich->ForschungPlus + $ich->ProduktionPlus + $ich->MafiaPlus - $ich->MafiaMinus, 0, ",", "."); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
