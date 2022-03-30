<?php
/**
 * Wird in die index.php eingebunden; Formular zum Verfassen eines neuen Vertrags
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
include("include/preise.inc.php");
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/makevertrag.png" alt="Vertrag verfassen"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Neuen Vertrag verfassen
                <a href="./?p=hilfe&amp;mod=1&amp;cat=10"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Vertraege && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    $menge = intval($_GET['menge']);
    $ware = intval($_GET['ware']);
    $an = intval($_GET['an']);
    $_preis = number_format($_GET['preis'], 2, ",", ".");
    ?>

    <?= $m; ?>

    <p class="seite_beschreibung">
        Hier können Sie Waren gegen Bezahlung an einen anderen Spieler verschicken. Der Preis muss mindestens der
        Preis im Laden sein und darf den dreifachen Wert nicht übersteigen.
    </p>

    <form action="./actions/vertraege.php" method="post" name="form_vertrag">
        <input type="hidden" name="a" value="1"/>
        <table class="Liste" style="margin-top: 20px; width: 550px;" cellspacing="0">
            <tr>
                <th>Vertragswerte bearbeiten</th>
            </tr>
            <tr>
                <td style="font-weight: bold; height: 40px;">
                    <input type="text" name="menge" size="3" maxlength="5" value="<?= $menge; ?>"/> kg
                    <select name="ware">
                        <?php
                        $eintrag = false;        // Zeigt an, ob wir was auf Lager haben.
                        $last_index = -1;

                        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Läuft das gesamte Lager durch, und schaut ob wir irgendwas auf Lager haben
                            $temp = "Lager" . $i;        // Temporäre Variable mit dem MySQL-Spaltennamen der Ware im Lager
                            if ($ich->$temp > 0) {            // Wenn wir die Ware haben, dann
                                echo '<option value="' . $i . '"' . ($ware == $i ? ' selected="selected"' : '') . '>' . WarenName($i) . "</option>\n";        // ...geben wir die entsprechende Option aus
                                $eintrag = true;        //... und notieren, dass wir was haben.

                                if (!isset($first_ware)) {
                                    $first_ware = $i;
                                }

                                $Ware[$i] = $last_index + 1;
                                $last_index++;
                            }
                        }

                        if (!$eintrag) {        // Falls wir aber keine Waren auf Lager haben, dann...
                            echo '<script type="text/javascript">document.location.href="./?p=vertraege_liste&m=122";</script>';        //... leiten wir ihn zurück
                            die();        // und brechen ab.
                        }
                        ?>
                    </select>
                    an
                    <select name="an" style="min-width: 150px;">
                        <?php
                        $sql_abfrage = "SELECT
    ID,
    Name
FROM
    mitglieder
WHERE
    ID <> '" . $_SESSION['blm_user'] . "'
AND
    ID > 0
ORDER BY
    Name;";
                        $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft alle Spieler ab
                        $_SESSION['blm_queries']++;

                        while ($empfaenger = mysql_fetch_object($sql_ergebnis)) {        // Holt sich nun der Reihe nach alle Spieler (Empfänger)
                            echo '<option value="' . $empfaenger->ID . '"' . ($an == $empfaenger->ID ? ' selected="selected"' : '') . '>' . htmlentities(stripslashes($empfaenger->Name), ENT_QUOTES, "UTF-8") . '</option>'; // schreib einfach die Option hin.
                        }
                        ?>
                    </select>
                    zu
                    <input type="text" name="preis" size="3" value="<?= $_preis; ?>"/> <?php echo $Currency; ?> pro kg
                    <input type="submit" value="verkaufen"
                           onclick="document.forms[0].submit(); this.disabled='disabled'; this.value='Bitte warten...'; return false;"/>.
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
                    echo '<tr><td>' . WarenName($i) . '</td><td style="text-align: right;">' . number_format($lager->$temp, 0, ",", ".") . ' kg</td><td style="text-align: right;">' . number_format($Preis[$i], 2, ",", ".") . ' ' . $Currency . '</td><td style="padding: 0 5px 0 15px;"><a href="#" onclick="const z=document.form_vertrag; z.menge.value=\'' . $lager->$temp . '\'; z.Ware.selectedIndex=\'' . $Ware[$i] . '\'; return false;">Übernehmen</a></td></tr>' . "\n";
                }
            }
            ?>
        </table>
    </form>
    <?php
}
