<?php
/**
 * Wird in die index.php eingebunden; Formular zum Hinzufügen eines neuen Angebots für den Markt
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<?php
include("include/preise.inc.php");
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td><img src="/pics/big/marktplatz.png" alt="Marktplatz"/></td>
            <td>Der Marktplatz
                <a href="./?p=hilfe&amp;mod=1&amp;cat=11"><img src="/pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Marktplatz && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <b>
        Hier können Sie ein neues Angebot auf den freien Markt stellen.
    </b>
    <br/>
    <br/>
    <form action="./actions/marktplatz.php" method="post" name="form_marktplatz">
        <input type="hidden" name="a" value="1"/>
        <table class="Liste" cellspacing="0" style="width: 400px;">
            <tr>
                <th>Angebotsdaten bearbeiten</th>
            </tr>
            <tr>
                <td style="font-weight: bold; height: 40px;">
                    <input type="text" name="menge" size="3" maxlength="5" value="0"/> kg <select name="was">
                        <?php
                        $eintrag = false;        // Bisher haben wir noch keine Waren auf Lager, welche wir ausgegeben haben.
                        $last_index = -1;

                        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Schaut das ganze Lager durch, und gibt nur die Einträge aus, bei denen der Lagerstand > 0 ist
                            $temp = "Lager" . $i;
                            if ($ich->$temp > 0) {
                                echo '					<option value="' . $i . '">' . WarenName($i) . "</option>\n";
                                $eintrag = true;        // jetzt haben wir mindestens einen Eintrag

                                if (!isset($first_ware)) {
                                    $first_ware = $i;
                                }

                                $Ware[$i] = $last_index + 1;
                                $last_index++;
                            }
                        }

                        if (!$eintrag) {        // Falls wir keine Waren auf Lager haben, wird abgebrochen...
                            echo '<script type="text/javascript">document.location.href="./?p=vertraege_liste&m=122";</script>';
                            die();
                        }
                        ?>
                    </select> zu <input type="text" name="preis" size="3" value="0,00"/> <?= $Currency; ?> pro kg
                    <input type="submit" value="verkaufen"/>.
                </td>
            </tr>
        </table>
        <table class="Liste" style="margin-top: 20px; width: 340px;" cellspacing="0">
            <tr>
                <th colspan="4">
                    Warenbestand
                </th>
            </tr>
            <tr>
                <th>Ware</th>
                <th>Menge</th>
                <th style="text-align: right;">Preis im Laden</th>
                <th>&nbsp;</th>
            </tr>
            <?php
            $sql_abfrage = "SELECT
    *
FROM
    lagerhaus
WHERE
    ID='" . $_SESSION['blm_user'] . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft das Lager nochmals ab
            $_SESSION['blm_queries']++;

            $lager = mysql_fetch_object($sql_ergebnis);

            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Hier wird die kleine Hilfstabelle ausgegeben, bei der der User sehen kann, wieviel er für das Gemüse im Laden bekommen würde
                $temp = "Lager" . $i;
                if ($lager->$temp > 0) {
                    echo '<tr><td>' . WarenName($i) . '</td><td style="text-align: right;">' . number_format($lager->$temp, 0, ",", ".") . ' kg</td><td style="text-align: right;">' . number_format($Preis[$i], 2, ",", ".") . ' ' . $Currency . '</td><td style="padding: 0 5px 0 15px;"><a href="#" onclick="const z=document.form_marktplatz; z.menge.value=\'' . $lager->$temp . '\'; z.was.selectedIndex=\'' . $Ware[$i] . '\'; return false;">Übernehmen</a></td></tr>';
                }
            }
            ?>
        </table>
    </form>
    <?php
}
