<?php
/**
 * Wird in die index.php eingebunden; Seite des Forschungszentrums
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
include("include/kosten_dauer.inc.php");
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td><img src="/pics/big/forschungszentrum.png" alt="Forschungszentrum"/></td>
            <td>Das Forschungszentrum
                <a href="./?p=hilfe&amp;mod=1&amp;cat=6"><img src="/pics/help.gif" alt="Hilfe"
                                                              style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if ($_SESSION['blm_sitter'] && !$ich->Sitter->Forschung) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {

    echo $m;

    if ($ich->Gebaeude2 == 0) {
        echo '<span class="MeldungR" style="font-size: 12pt;">Sie müssen zuerst mal ein Forschungszentrum bauen, bevor Sie Forschungen starten können!</span>';
    } else {
        $sql_abfrage = "SELECT
    ID,
    Was,
    Start,
    Dauer,
    Kosten
FROM
    auftrag
WHERE
    Von='" . $_SESSION['blm_user'] . "'
AND
    Was > 300
AND
    Was < 400;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $auftraege = new stdClass();
        while ($auftrag = mysql_fetch_object($sql_ergebnis)) {
            $temp = "a_" . intval($auftrag->Was);

            $auftraege->$temp = $auftrag;
        }
        ?>
        <b>
            Hier können Sie das entsprechende Gemüse erforschen bzw. verbessern.<br/>
            Stufe 1 ermöglicht den Anbau des Gemüses, und jede weitere Stufe
            erhöht die Menge, die produziert wird.<br/>
        </b>
        <br/>
        <?php
        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
            $temp = "Forschung" . $i;
            $forschung_dauer = $$temp->Dauer;
            $forschung_kosten = $$temp->Kosten;
            $forschung_punkte = $$temp->Punkte;

            if ($ich->Gebaeude1 >= intval($i * 1.5) && $ich->Gebaeude2 >= intval($i * 1.5)) {
                ?>
                <table class="Liste" cellspacing="0" style="margin-bottom: 20px;">
                    <tr>
                        <th colspan="3"><a id="f<?= $i; ?>"></a><?= WarenName($i); ?>, aktuell Stufe <?= $ich->$temp; ?>
                        </th>
                    </tr>
                    <tr>
                        <td style="width: 170px;">
                            <img src="/pics/forschung/<?= BildVonWare($i); ?>" alt="<?= WarenName($i); ?>"/>
                        </td>
                        <td>
                            <b><u>Für Stufe <?= (1 + $ich->$temp); ?>:</u></b>
                            <p>
                                Dauer:
                                <b><?= (date("d", $forschung_dauer - 3600) - 1) . " Tage " . date("H:i:s", $forschung_dauer - 3600); ?>
                                    min</b><br/>
                                Kosten:
                                <b><?= number_format($forschung_kosten, 2, ',', '.') . " " . $Currency; ?></b><br/>
                                Punkte: <b><?= round($forschung_punkte); ?></b>
                            </p>
                        </td>
                        <td style="width: 240px;">
                            <div align="center">
                                <form action="actions/forschungszentrum.php" method="post">
                                    <input type="hidden" name="was" value="<?= $i; ?>"/>
                                    <?php
                                    $temp = "a_" . (300 + $i);

                                    if (property_exists($auftraege, $temp)) {        // Gibt es schon einen derartigen Auftrag?
                                        echo '<input type="submit" name="anbauen" disabled="disabled" value="Forschen"/><br />';
                                        echo '<i>Es läuft bereits eine Forschung!</i><br />
										(noch ' . (date("d", $auftraege->$temp->Start + $auftraege->$temp->Dauer - time() - 3600) - 1) . " Tage " . date("H:i:s", $auftraege->$temp->Start + $auftraege->$temp->Dauer - time() - 3600) . ' verbleibend.)<br />
										<a onclick="return confirm(\'Wollen Sie den Auftrag wirklich abbrechen? Sie bekommen  nur ' . (AUFTRAG_RUECKZIEH_RETURN * 100) . '% (' . number_format($forschung_kosten * AUFTRAG_RUECKZIEH_RETURN, 2, ",", ".") . ' ' . $Currency . ') der Kosten zurück erstattet!\');" href="actions/auftrag.php?a=1&amp;id=' . $auftraege->$temp->ID . '&amp;back=forschungszentrum&amp;was=' . $i . '">Abbrechen</a>';
                                    } else {                        // Nein:
                                        if ($ich->Geld >= $forschung_kosten) {            //Habe ich genügend Geld? Wenn ja, dann...
                                            echo '<input type="submit" name="anbauen" value="Forschen"  onclick="document.forms[' . ($i - 1) . '].submit(); this.disabled=\'disabled\'; this.value=\'Bitte warten...\'; return false;" />';
                                        } else {        // ... ansonsten:
                                            echo '<input type="submit" name="anbauen" disabled="disabled" value="Forschen"/><br />';
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
    }
}
