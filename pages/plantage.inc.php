<?php
/**
 * Wird in die index.php eingebunden; Formular für die Plantage
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
/*
Changelog:

[1.0.1]
    - Neue Berechnung für die PRoduktionskostenrechnung

*/
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/plantage.png" alt="Plantage"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die Plantage
                <a href="./?p=hilfe&amp;mod=1&amp;cat=5"><img src="pics/help.gif" alt="Hilfe"
                                                              style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Produktion && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>
    <?php
    $sql_abfrage = "SELECT
    ID,
    Was,
    Start,
    Dauer,
    Kosten,
    Menge
FROM
    auftrag
WHERE
    Von='" . $_SESSION['blm_user'] . "'
AND
    Was > 200
AND
    Was < 300;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    while ($auftrag = mysql_fetch_object($sql_ergebnis)) {
        $temp = "a_" . intval($auftrag->Was);

        $auftraege->$temp = $auftrag;
    }
    ?>
    <b>
        Hier können Sie Ihre erforschten Obst- und Waresorten anbauen.<br/>
    </b>
    <br/>
    <form action="actions/plantage.php" method="post">
        <input type="hidden" name="alles" value="1"/>
        <table class="Liste" style="width: 350px; margin-bottom: 20px;">
            <tr>
                <th>Schnellmenü</th>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 5px;">
                    Produziere
                    <input type="text" name="stunden" value="1" size="1" maxlength="2"/>
                    Stunde(n) von allem.
                    <?php
                    if (isset($auftraege)) {
                        ?>
                        <input type="submit" value="Abschicken" disabled="disabled"/>
                        <?php
                    } else {
                        ?>
                        <input type="submit" value="Abschicken"
                               onclick="document.forms[0].submit(); this.disabled='disabled'; this.value='Bitte warten...'; return false;"/>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </form>
    <?php
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Hier werden die Kosten, Mengen und die Dauer für jedes Ware berechnet.
        $temp = "Forschung" . $i;        // Temporäre Variable mit dem MySQL Spaltennamen für die Forschung des aktuellen Wares

        $menge = ($ich->Gebaeude1 * PRODUKTIONS_PLANTAGE_FAKTOR_MENGE) + ($i * PRODUKTIONS_WAREN_FAKTOR_MENGE) + $Produktion->BasisMenge + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE);        // Berechnet die Produktionsmenge
        $kosten = $Produktion->BasisKosten + ($ich->$temp * PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN);                                                                        // Berechnet die Kosten für den Auftrag
        $dauer = $Produktion->BasisDauer - 3600;                    // Berechnet, wie lange er dauert

        if ($ich->$temp > 0 && $ich->Gebaeude1 >= intval($i * 1.5)) {
            ?>
            <table class="Liste" cellspacing="0" style="margin-bottom: 20px;">
                <tr>
                    <th colspan="3" id="p<?= $i; ?>"><?= WarenName($i); ?>, aktuell Stufe <?= $ich->$temp; ?></th>
                </tr>
                <tr>
                    <td width="170">
                        <img src="pics/obst/<?= BildVonWare($i); ?>" alt="<?= WarenName($i); ?>"/>
                    </td>
                    <td>
                        <p>
                            <b><?= number_format($menge / date("H", $dauer), 0, ",", "."); ?> kg / Stunde</b><br/>
                            <b><?= number_format(($menge / date("H", $dauer)) / 60, 3, ",", "."); ?> kg /
                                Minute</b><br/>
                            <b><?= number_format(round($kosten / $menge, 4), 4, ",", ".") . " " . $Currency; ?> / kg</b>
                        </p>
                    </td>
                    <td width="200">
                        <div align="center">
                            <form action="actions/plantage.php" method="post" name="pr_<?= $i; ?>">
                                <input type="hidden" name="was" value="<?= $i; ?>"/>
                                <?php
                                $temp = "a_" . (200 + $i);

                                if (intval($auftraege->$temp->ID) > 0) {        // Wenn der Auftrag schon gegeben wurde, dann...
                                    $ProzentFertig = 1 - (($auftraege->$temp->Start + $auftraege->$temp->Dauer) - time()) / $auftraege->$temp->Dauer;

                                    echo '<input type="submit" name="anbauen" disabled="disabled" value="Ware anbauen"/><br />';
                                    echo '<i>Es läuft bereits ein Anbau!</i><br />
										(noch ' . date("H:i:s", $auftraege->$temp->Start + $auftraege->$temp->Dauer - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen die Kosten nicht zurück erstattet, lediglich die bisher produzierte Menge (~ ' . intval($auftraege->$temp->Menge * $ProzentFertig) . ' kg) wird Ihnen gut geschrieben.!\');" href="actions/auftrag.php?id=' . $auftraege->$temp->ID . '&amp;back=plantage&amp;was=' . $i . '">Abbrechen</a>';
                                } else {    // Der Auftrag wurde noch nicht erteilt:
                                    echo '<b>Menge: <input type="text" size="3" maxlength="5" name="menge" value="' . $menge . '" onkeyup="RechneProduktionsKosten(' . $menge . ', ' . $kosten . ', document.pr_' . $i . '.menge.value, ' . $ich->Geld . ', document.getElementById(\'pr_ko_' . $i . '\'));" />  kg</b><br /><span id="pr_ko_' . $i . '">Kosten: ' . number_format($kosten, 2) . ' €</span><br />';
                                    echo '<input type="submit" name="anbauen" value="Ware anbauen" style="margin-top: 8px;" onclick="document.forms[' . $i . '].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                    ?>
                                    <script type="text/javascript">
                                        RechneProduktionsKosten(<?=$menge; ?>, <?=$kosten; ?>, document.pr_<?=$i; ?>.menge.value, <?=$ich->Geld; ?>, document.getElementById('pr_ko_<?=$i; ?>'));
                                    </script>
                                    <?php
                                }
                                ?>
                            </form>
                        </div>
                    </td>
                </tr>
            </table>
            <?php
        }
    }
}
