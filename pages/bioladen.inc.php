<?php
/**
 * Wird in die index.php eingebunden; Seite mit Lageransicht und Möglichkeit, die Waren gleich zu verkaufen.
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

include("include/preise.inc.php");        // Hier brauchen wir noch zusätzlich die Verkaufspreise der Waren
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/bioladen.png" alt="Bioladen"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Der Bioladen
            <a href="./?p=hilfe&amp;mod=1&amp;cat=7"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>
<?php
if ($_SESSION['blm_sitter'] && !$ich->Sitter->Bioladen) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <b>
        Hier können Sie Ihr produziertes Obst und Gemüse zum Festpreis verkaufen.<br/>
        Dieser richtet sich nach der Stufe der Schule und nach der Ausbaustufe des Bioladens.<br/>
    </b>
    <br/>
    <table class="Liste" style="width: 450px" cellspacing="0">
        <tr>
            <th width="60">Lager</th>
            <th width="100">Ware</th>
            <th width="80">Preis / kg</th>
            <th width="230">Menge / Aktion</th>
        </tr>
        <?php
        $eintrag = 0;
        $menge = 0;
        $erloese = 0;

        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
            $Lager = "Lager" . $i;        // Git das gesamte Lager zeilenweise aus.
            if ($ich->$Lager > 0) {
                echo '<tr>';
                echo '<td>' . $ich->$Lager . ' kg</td>';
                echo '<td>' . Warenname($i) . '</td>';
                echo '<td>' . number_format($Preis[$i], 2, ',', '.') . ' ' . $Currency . '</td>';
                echo '<td align="right" style="padding-right: 5px;"><form action="actions/bioladen.php" method="post"><input type="text" maxlength="5" name="menge" size="3" style="margin-right: 8px;" value="' . $ich->$Lager . '" />';
                echo '<input type="hidden" name="was" value="' . $i . '" /><input type="submit" value="Verkaufen" onclick="this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; this.parentNode.submit(); return false;" /></form></td>';
                echo '</tr>';

                $eintrag++;
                $menge += $ich->$Lager;
                $erloese += $ich->$Lager * $Preis[$i];
            }
        }

        if ($eintrag == 0) {
            echo '<tr><td colspan="4" style="text-align: center;"><i>Sie haben kein Obst/Gemüse auf Lager.</i></td></tr>';
        }

        if ($eintrag > 1) {
            echo '<tr><td colspan="4" style="text-align: center; border-top: darkred solid 1px;"><i>Alles (' . number_format($menge, 0, ",", ".") . ' kg) für ' . number_format($erloese, 2, ",", ".") . ' ' . $Currency . ') verkaufen:</i><form action="actions/bioladen.php" method="post"><input type="hidden" name="was" value="1337" /><input type="submit" value="Verkaufen" /></form></td></tr>';
        }
        ?>
    </table>
    <?php
}
