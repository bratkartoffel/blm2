<?php
/**
 * Wird in die index.php eingebunden; Seite zum Bauen von Gebäuden
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
include("include/kosten_dauer.inc.php");

$sql_abfrage = "SELECT
    ID,
    Was,
    Start,
    Dauer,
    Kosten
FROM
    auftrag
WHERE
    Von = '" . $_SESSION['blm_user'] . "'
AND
    Was > 100
AND
    Was < 200;";
$sql_ergebnis = mysql_query($sql_abfrage);
$_SESSION['blm_queries']++;

$auftraege = new stdClass();
while ($auftrag = mysql_fetch_object($sql_ergebnis)) {
    $temp = "a_" . intval($auftrag->Was);

    $auftraege->$temp = $auftrag;
}
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td><img src="/pics/big/gebaeude.png" alt="Gebäude"/></td>
            <td>Ihre Gebäude
                <a href="./?p=hilfe&amp;mod=1&amp;cat=4"><img src="/pics/help.gif" alt="Hilfe"
                                                              style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if ($_SESSION['blm_sitter'] && !$ich->Sitter->Gebaeude) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>
    <?= $m; ?>

    <b>
        Hier können Sie alle Ihre Gebäude ausbauen, und ihre aktuelle Stufe abfragen.<br/>
    </b>
    <br/>
    <table class="Liste" cellspacing="0">
        <tr>
            <th colspan="3"><a id="g1"></a>Plantage, aktuell Stufe <?= $ich->Gebaeude1; ?></th>
        </tr>
        <tr>
            <td style="width: 170px;">
                <img src="./pics/gebaeude/plantage.jpg" alt="Plantage"/>
            </td>
            <td colspan="2">
                Dies ist das wichtigste Gebäude des Spiels.<br/>
                Je weiter Sie die Plantage ausbauen, desto mehr Gemüse kann schneller angebaut werden.<br/>
                Ausserdem können auch neue Gemüsesorten erst mit einem gewissen Plantagenlevel angebaut werden.
            </td>
        </tr>
    </table>
    <table class="Liste" cellspacing="0" style="margin-top: -2px;">
        <tr>
            <td colspan="2" style="text-align: left; padding-left: 170px;">
                <?php
                if (!property_exists($auftraege, "a_101")) {
                    ?>
                    <b><u>Für Stufe <?= (1 + $ich->Gebaeude1); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten: <b><?= number_format($Plantage->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", $Plantage->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Plantage->Dauer - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round($Plantage->Punkte); ?></b>
                    </p>
                    <?php
                } else {
                    ?>
                    <b><u>Für Stufe <?= (2 + $ich->Gebaeude1); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten:
                        <b><?= number_format(($Plantage->Kosten * $Plantage->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", ($Plantage->Dauer * $Plantage->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Plantage->Dauer * $Plantage->DauerFaktor) - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round(($Plantage->Punkte * $Plantage->PunkteFaktor)); ?></b>
                    </p>
                    <?php
                }
                ?>
            </td>
            <td style="width: 300px;">
                <div style="text-align: center;">
                    <form action="actions/gebaeude.php" method="post">
                        <input type="hidden" name="was" value="1"/>
                        <?php
                        if (property_exists($auftraege, "a_101")) {        // Ist ein derartiger Auftrag schon vorhanden?
                            echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                            echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_101->Start + $auftraege->a_101->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_101->Start + $auftraege->a_101->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Plantage->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_101->ID . '&amp;back=gebaeude&amp;was=1">Abbrechen</a>';        // Dann sag uns, wie lange er noch dauert...
                        } else {        // Der Auftrag wurde noch nicht erteilt
                            if ($ich->Geld >= $Plantage->Kosten) {        // hat der Benutzer genügend Geld?
                                echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[0].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                            } else {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Sie haben nicht genügend Geld!</i>';
                            }
                        }
                        ?>
                    </form>
                </div>
            </td>
        </tr>
    </table>
    <div style="padding-top: 10px;">&nbsp;</div>
    <table class="Liste" cellspacing="0">
        <tr>
            <th colspan="3"><a id="g2"></a>Forschungszentrum, aktuell Stufe <?= $ich->Gebaeude2; ?></th>
        </tr>
        <tr>
            <td style="width: 170px;">
                <img src="/pics/gebaeude/forschungszentrum.jpg" alt="Forschungszentrum"/>
            </td>
            <td colspan="2">
                Dies ist ebenfalls ein sehr wichtiges Gebäude in Ihrem Betrieb.<br/>
                Hier können Sie neue Gemüsesorten erforschen (damit Sie sie anbauen können) oder
                bestehende verbessern (schnellerer Anbau).<br/>
                Ausserdem werden neue Gemüsesorten bekannt, je höher das Forschungszentrum ist und die
                Forschungszeit für eine Stufe um <?= BONUS_FAKTOR_FORSCHUNGSZENTRUM * 100; ?>% je Stufe gesenkt.
            </td>
        </tr>
    </table>
    <table class="Liste" cellspacing="0" style="margin-top: -2px;">
        <tr>
            <td colspan="2" style="text-align: left; padding-left: 170px;">
                <?php
                if (!property_exists($auftraege, "a_102")) {
                    ?>
                    <b><u>Für Stufe <?= (1 + $ich->Gebaeude2); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten:
                        <b><?= number_format($Forschungszentrum->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", $Forschungszentrum->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Forschungszentrum->Dauer - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round($Forschungszentrum->Punkte); ?></b>
                    </p>
                    <?php
                } else {
                    ?>
                    <b><u>Für Stufe <?= (2 + $ich->Gebaeude2); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten:
                        <b><?= number_format(($Forschungszentrum->Kosten * $Forschungszentrum->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", ($Forschungszentrum->Dauer * $Forschungszentrum->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Forschungszentrum->Dauer * $Forschungszentrum->DauerFaktor) - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round(($Forschungszentrum->Punkte * $Forschungszentrum->PunkteFaktor)); ?></b>
                    </p>
                    <?php
                }
                ?>
            </td>
            <td style="width: 300px;">
                <div style="text-align: center;">
                    <form action="actions/gebaeude.php" method="post">
                        <input type="hidden" name="was" value="2"/>
                        <?php
                        if (property_exists($auftraege, "a_102")) {
                            echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                            echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_102->Start + $auftraege->a_102->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_102->Start + $auftraege->a_102->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Forschungszentrum->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_102->ID . '&amp;back=gebaeude&amp;was=2">Abbrechen</a>';
                        } else {
                            if ($ich->Geld >= $Forschungszentrum->Kosten) {
                                echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[1].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                            } else {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Sie haben nicht genügend Geld!</i>';
                            }
                        }
                        ?>
                    </form>
                </div>
            </td>
        </tr>
    </table>
    <div style="padding-top: 10px;">&nbsp;</div>
    <table class="Liste" cellspacing="0">
        <tr>
            <th colspan="3"><a id="g3"></a>Bioladen, aktuell Stufe <?= $ich->Gebaeude3; ?></th>
        </tr>
        <tr>
            <td style="width: 170px;">
                <img src="/pics/gebaeude/bioladen.jpg" alt="Bioladen"/>
            </td>
            <td colspan="2">
                Dieses Gebäude ist genau so wichtig, wie die Plantage und das Forschungszentrum,
                denn hier können Sie Ihre Gemüse verkaufen.<br/>
                Ausserdem steigt Ihr Grundeinkommen und der Preis, den Sie pro Kilogramm erhalten, mit jeder
                Stufe, die der Laden erreicht.
            </td>
        </tr>
    </table>
    <table class="Liste" cellspacing="0" style="margin-top: -2px;">
        <tr>
            <td colspan="2" style="text-align: left; padding-left: 170px;">
                <?php
                if (!property_exists($auftraege, "a_103")) {
                    ?>
                    <b><u>Für Stufe <?= (1 + $ich->Gebaeude3); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten: <b><?= number_format($Bioladen->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", $Bioladen->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Bioladen->Dauer - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round($Bioladen->Punkte); ?></b>
                    </p>
                    <?php
                } else {
                    ?>
                    <b><u>Für Stufe <?= (2 + $ich->Gebaeude3); ?>:</u></b>
                    <p style="padding-top: 10px; margin: 0;">
                        Kosten:
                        <b><?= number_format(($Bioladen->Kosten * $Bioladen->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                        Dauer:
                        &nbsp;<b><?= (date("d", ($Bioladen->Dauer * $Bioladen->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Bioladen->Dauer * $Bioladen->DauerFaktor) - 3600); ?>
                            min</b><br/>
                        Punkte: <b><?= round(($Bioladen->Punkte * $Bioladen->PunkteFaktor)); ?></b>
                    </p>
                    <?php
                }
                ?>
            </td>
            <td style="width: 300px;">
                <div style="text-align: center;">
                    <form action="actions/gebaeude.php" method="post">
                        <input type="hidden" name="was" value="3"/>
                        <?php
                        if (property_exists($auftraege, "a_103")) {
                            echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                            echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_103->Start + $auftraege->a_103->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_103->Start + $auftraege->a_103->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Bioladen->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_103->ID . '&amp;back=gebaeude&amp;was=3">Abbrechen</a>';
                        } else {
                            if ($ich->Geld >= $Bioladen->Kosten) {
                                echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[2].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                            } else {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Sie haben nicht genügend Geld!</i>';
                            }
                        }
                        ?>
                    </form>
                </div>
            </td>
        </tr>
    </table>
    <?php
    if ($ich->Gebaeude3 >= 5) {        // Sind die Anforderungen für das Gebäude schon erreicht?
        /*

            *******************************************************************************************
            Weiter werde ich diese Datei nicht mehr kommentieren, da sich das alles immer wiederholt...
            *******************************************************************************************

        */
        ?>
        <div style="padding-top: 10px;">&nbsp;</div>
        <table class="Liste" cellspacing="0">
            <tr>
                <th colspan="3"><a id="g4"></a>Dönerstand, aktuell Stufe <?= $ich->Gebaeude4; ?></th>
            </tr>
            <tr>
                <td style="width: 170px;">
                    <img src="/pics/gebaeude/doenerstand.jpg" alt="Dönerstand"/>
                </td>
                <td colspan="2">
                    Dieses Gebäude hat zwar nicht viel mit &quot;Biowaren&quot; zu tun, <br/>
                    aber Sie haben erkannt, dass alleine mit Biolebensmitteln kein Geld zu verdienen ist.<br/>
                    Deshalb kann man sich hier einen Dönerstand mieten, der das Grundeinkommen des Spielers erhöht.
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="margin-top: -2px;">
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 170px;">
                    <?php
                    if (!property_exists($auftraege, "a_104")) {
                        ?>
                        <b><u>Für Stufe <?= (1 + $ich->Gebaeude4); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format($Doenerstand->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", $Doenerstand->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Doenerstand->Dauer - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round($Doenerstand->Punkte); ?></b>
                        </p>
                        <?php
                    } else {
                        ?>
                        <b><u>Für Stufe <?= (2 + $ich->Gebaeude4); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format(($Doenerstand->Kosten * $Doenerstand->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", ($Doenerstand->Dauer * $Doenerstand->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Doenerstand->Dauer * $Doenerstand->DauerFaktor) - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round(($Doenerstand->Punkte * $Doenerstand->PunkteFaktor)); ?></b>
                        </p>
                        <?php
                    }
                    ?>
                </td>
                <td style="width: 300px;">
                    <div style="text-align: center;">
                        <form action="actions/gebaeude.php" method="post">
                            <input type="hidden" name="was" value="4"/>
                            <?php
                            if (property_exists($auftraege, "a_104")) {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_104->Start + $auftraege->a_104->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_104->Start + $auftraege->a_104->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Doenerstand->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_104->ID . '&amp;back=gebaeude&amp;was=4">Abbrechen</a>';
                            } else {
                                if ($ich->Geld >= $Doenerstand->Kosten) {
                                    echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[3].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                } else {
                                    echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                    echo '<i>Sie haben nicht genügend Geld!</i>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    if ($ich->Gebaeude1 >= 5) {
        ?>
        <div style="padding-top: 10px;">&nbsp;</div>
        <table class="Liste" cellspacing="0">
            <tr>
                <th colspan="3"><a id="g6"></a>Verkäuferschule, aktuell Stufe <?= $ich->Gebaeude6; ?></th>
            </tr>
            <tr>
                <td style="width: 170px;">
                    <img src="/pics/gebaeude/schule.jpg" alt="Verkäuferschule"/>
                </td>
                <td colspan="2">
                    Hier bilden Sie Ihre Verkäufer aus, so dass diese in Ihrem Bioshop mehr Gewinn erzielen können.<br/>
                    Dabei steigt der Gewinn pro Kilo und Stufe
                    um <?= number_format(WAREN_PREIS_VERKAEUFERSCHULE, 2, ",", ".") . " " . $Currency; ?>!
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="margin-top: -2px;">
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 170px;">
                    <?php
                    if (!property_exists($auftraege, "a_106")) {
                        ?>
                        <b><u>Für Stufe <?= (1 + $ich->Gebaeude6); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten: <b><?= number_format($Schule->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", $Schule->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Schule->Dauer - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round($Schule->Punkte); ?></b>
                        </p>
                        <?php
                    } else {
                        ?>
                        <b><u>Für Stufe <?= (2 + $ich->Gebaeude6); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format(($Schule->Kosten * $Schule->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", ($Schule->Dauer * $Schule->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Schule->Dauer * $Schule->DauerFaktor) - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round(($Schule->Punkte * $Schule->PunkteFaktor)); ?></b>
                        </p>
                        <?php
                    }
                    ?>
                </td>
                <td style="width: 300px;">
                    <div style="text-align: center;">
                        <form action="actions/gebaeude.php" method="post">
                            <input type="hidden" name="was" value="6"/>
                            <?php
                            if (property_exists($auftraege, "a_106")) {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_106->Start + $auftraege->a_106->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_106->Start + $auftraege->a_106->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Schule->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_106->ID . '&amp;back=gebaeude&amp;was=6">Abbrechen</a>';
                            } else {
                                if ($ich->Geld >= $Schule->Kosten) {
                                    echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[4].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                } else {
                                    echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                    echo '<i>Sie haben nicht genügend Geld!</i>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    if ($ich->Gebaeude1 >= 8 && $ich->Gebaeude2 >= 9) {
        ?>
        <div style="padding-top: 10px;">&nbsp;</div>
        <table class="Liste" cellspacing="0">
            <tr>
                <th colspan="3"><a id="g5"></a>Bauhof, aktuell Stufe <?= $ich->Gebaeude5; ?></th>
            </tr>
            <tr>
                <td style="width: 170px;">
                    <img src="/pics/gebaeude/bauhof.jpg" alt="Bauhof"/>
                </td>
                <td colspan="2">
                    Dieses Gebäude senkt die Ausbauzeiten sämtlicher Gebäude um <?= BONUS_FAKTOR_BAUHOF * 100; ?>% pro
                    Stufe. Der Bauhof wird erst beim späten Spielverlauf wichtig.
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="margin-top: -2px;">
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 170px;">
                    <?php
                    if (!property_exists($auftraege, "a_105")) {
                        ?>
                        <b><u>Für Stufe <?= (1 + $ich->Gebaeude5); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten: <b><?= number_format($Bauhof->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", $Bauhof->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Bauhof->Dauer - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round($Bauhof->Punkte); ?></b>
                        </p>
                        <?php
                    } else {
                        ?>
                        <b><u>Für Stufe <?= (2 + $ich->Gebaeude5); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format(($Bauhof->Kosten * $Bauhof->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", ($Bauhof->Dauer * $Bauhof->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Bauhof->Dauer * $Bauhof->DauerFaktor) - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round(($Bauhof->Punkte * $Bauhof->PunkteFaktor)); ?></b>
                        </p>
                        <?php
                    }
                    ?>
                </td>
                <td style="width: 300px;">
                    <div style="text-align: center;">
                        <form action="actions/gebaeude.php" method="post">
                            <input type="hidden" name="was" value="5"/>
                            <?php
                            if (property_exists($auftraege, "a_105")) {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_105->Start + $auftraege->a_105->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_105->Start + $auftraege->a_105->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Bauhof->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_105->ID . '&amp;back=gebaeude&amp;was=5">Abbrechen</a>';
                            } else {
                                if ($ich->Geld >= $Bauhof->Kosten) {
                                    echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[5].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                } else {
                                    echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                    echo '<i>Sie haben nicht genügend Geld!</i>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    if ($ich->AusgabenMafia >= 10000 && $ich->Gebaeude1 > 9) {
        ?>
        <div style="padding-top: 10px;">&nbsp;</div>
        <table class="Liste" cellspacing="0">
            <tr>
                <th colspan="3"><a id="g7"></a>Zaun, aktuell Stufe <?= $ich->Gebaeude7; ?></th>
            </tr>
            <tr>
                <td style="width: 170px;">
                    <img src="/pics/gebaeude/zaun.jpg" alt="Zaun"/>
                </td>
                <td colspan="2">
                    Dieses Gebäude bietet den einzigen Schutz gegen Angriffe der Mafia. Dabei senkt jede Stufe des Zauns
                    die Erfolgschancen des Gegners um <?= BONUS_FAKTOR_ZAUN; ?>%.<br/>
                    Dies ist das teuerste Gebäude und dauert auch am längsten.
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="margin-top: -2px;">
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 170px;">
                    <?php
                    if (!property_exists($auftraege, "a_107")) {
                        ?>
                        <b><u>Für Stufe <?= (1 + $ich->Gebaeude7); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten: <b><?= number_format($Zaun->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", $Zaun->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Zaun->Dauer - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round($Zaun->Punkte); ?></b>
                        </p>
                        <?php
                    } else {
                        ?>
                        <b><u>Für Stufe <?= (2 + $ich->Gebaeude7); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format(($Zaun->Kosten * $Zaun->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", ($Zaun->Dauer * $Zaun->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Zaun->Dauer * $Zaun->DauerFaktor) - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round(($Zaun->Punkte * $Zaun->PunkteFaktor)); ?></b>
                        </p>
                        <?php
                    }
                    ?>
                </td>
                <td style="width: 300px;">
                    <div style="text-align: center;">
                        <form action="actions/gebaeude.php" method="post">
                            <input type="hidden" name="was" value="7"/>
                            <?php
                            if (property_exists($auftraege, "a_107")) {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_107->Start + $auftraege->a_107->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_107->Start + $auftraege->a_107->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Zaun->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_107->ID . '&amp;back=gebaeude&amp;was=7">Abbrechen</a>';
                            } else {
                                if ($ich->Geld >= $Zaun->Kosten) {
                                    echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[6].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                } else {
                                    echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                    echo '<i>Sie haben nicht genügend Geld!</i>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    if ($ich->AusgabenMafia >= 25000 && $ich->Gebaeude1 > 11) {
        ?>
        <div style="padding-top: 10px;">&nbsp;</div>
        <table class="Liste" cellspacing="0">
            <tr>
                <th colspan="3"><a id="g8"></a>Pizzeria, aktuell Stufe <?= $ich->Gebaeude8; ?></th>
            </tr>
            <tr>
                <td style="width: 170px;">
                    <img src="/pics/gebaeude/pizzeria.jpg" alt="Pizzeria"/>
                </td>
                <td colspan="2">
                    Dieses Gebäude ist das genaue Gegenstück zum Zaun.<br/>
                    Je weiter Sie die Pizzeria ausbauen, desto mehr Mafiosi lassen sich in der Stadt nieder und desto
                    höher
                    sind Ihre Erfolgschancen. Dabei steigen die Chancen pro Stufe um <?= BONUS_FAKTOR_PIZZERIA; ?>%.
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="margin-top: -2px;">
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 170px;">
                    <?php
                    if (!property_exists($auftraege, "a_108")) {
                        ?>
                        <b><u>Für Stufe <?= (1 + $ich->Gebaeude8); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten: <b><?= number_format($Pizzeria->Kosten, 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", $Pizzeria->Dauer - 3600) - 1) . " Tage " . date("H:i:s", $Pizzeria->Dauer - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round($Pizzeria->Punkte); ?></b>
                        </p>
                        <?php
                    } else {
                        ?>
                        <b><u>Für Stufe <?= (2 + $ich->Gebaeude8); ?>:</u></b>
                        <p style="padding-top: 10px; margin: 0;">
                            Kosten:
                            <b><?= number_format(($Pizzeria->Kosten * $Pizzeria->KostenFaktor), 2, ',', '.') . ' ' . $Currency; ?></b><br/>
                            Dauer:
                            &nbsp;<b><?= (date("d", ($Pizzeria->Dauer * $Pizzeria->DauerFaktor) - 3600) - 1) . " Tage " . date("H:i:s", ($Pizzeria->Dauer * $Pizzeria->DauerFaktor) - 3600); ?>
                                min</b><br/>
                            Punkte: <b><?= round(($Pizzeria->Punkte * $Pizzeria->PunkteFaktor)); ?></b>
                        </p>
                        <?php
                    }
                    ?>
                </td>
                <td style="width: 300px;">
                    <div style="text-align: center;">
                        <form action="actions/gebaeude.php" method="post">
                            <input type="hidden" name="was" value="8"/>
                            <?php
                            if (property_exists($auftraege, "a_108")) {
                                echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                echo '<i>Es läuft bereits ein Ausbau!</i><br />
										(noch ' . (date("d", ($auftraege->a_108->Start + $auftraege->a_108->Dauer) - time() - 3600) - 1) . " Tage " . date("H:i:s", ($auftraege->a_108->Start + $auftraege->a_108->Dauer) - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($Pizzeria->Kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->a_108->ID . '&amp;back=gebaeude&amp;was=8">Abbrechen</a>';
                            } else {
                                if ($ich->Geld >= $Pizzeria->Kosten) {
                                    echo '<input type="submit" value="Gebäude ausbauen" onclick="document.forms[7].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                } else {
                                    echo '<input type="submit" disabled="disabled" value="Gebäude ausbauen" /><br />';
                                    echo '<i>Sie haben nicht genügend Geld!</i>';
                                }
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
