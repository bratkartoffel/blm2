<?php
/**
 * Wird in die index.php eingebunden; Seite zur Verwaltung des Bankkontos
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/bank.png" alt="Bank"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die Bank
            <a href="./?p=hilfe&amp;mod=1&amp;cat=9"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>
<?php
if (!$ich->Sitter->Bank && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <b>
        Hier k&ouml;nnen Sie Ihr verdientes Geld anlegen oder auch Kredite aufnehmen, wenns mal knapp wird. Die Zinsen
        werden jeden Tag neu ausgerechnet. Gebucht werden die Zinsen alle <?php echo(ZINSEN_DAUER / 60); ?> Minuten. Die
        maximale Summe, die Sie anlegen k&ouml;nnen, sind <span style="color: red;"><?php
            if ($ich->Punkte < 100000) {
                echo '99.999,99';
            } else {
                echo number_format($ich->Punkte, 2, ",", ".");
            }
            ?><?php echo $Currency; ?></span>, Ihr Kreditlimit sind <span style="color: red;"><?php
            if ($ich->Punkte < 100000) {
                echo '-25.000';
            } else {
                echo number_format((-0.25 * $ich->Punkte) - 0.01, 2, ",", ".");
            }
            ?><?php echo $Currency; ?></span>.<br/>
        <br/>
        <span style="color: red;">Wichtig! Falls der Kontostand unter <?php
            if ($ich->Punkte < 100000) {
                echo number_format(DISPO_LIMIT, 0, ",", ".");
            } else {
                echo number_format((-0.33 * $ich->Punkte), 0, ",", ".") . " " . $Currency;
            }
            ?> f&auml;llt, wird Ihr Account automatisch resettet!</span><br/>
        <br/>
        Die aktuellen Anlagenzinsen: <?php echo number_format(($ZinsenAnlage - 1) * 100, 2, ",", "."); ?> %<br/>
        Die aktuellen Kreditzinsen: <?php echo number_format(($ZinsenKredit - 1) * 100, 2, ",", "."); ?> %<br/>
    </b>
    <br/>
    <h2>Ihr Kontostand: <?php echo number_format($ich->Bank, 2, ",", ".") . " " . $Currency; ?></h2>
    <?php
    if ($ich->Punkte < 100000) {
        if ($ich->Bank <= (DISPO_LIMIT * 0.90)) {
            echo '<h3 style="color: red;">WICHTIG: Falls der Kredit unter -' . number_format(DISPO_LIMIT, 0, ",", ".") . " " . $Currency . ' f&auml;llt, wird der Account automatisch resettet!</h3>';
        }
    } else {
        if ($ich->Bank <= (-0.25 * $ich->Punkte)) {
            echo '<h3 style="color: red;">WICHTIG: Falls der Kredit unter ' . number_format((-0.33 * $ich->Punkte) - 0.01, 0, ",", ".") . " " . $Currency . ' f&auml;llt, wird der Account automatisch resettet!</h3>';
        }
    }

    ?>
    <script type="text/javascript">
        <!--
        let changed = false;

        function AuswahlBank(option) {
            // Funktion zum Eintragen der Werte, falls die Option Einzahlen / Auszaheln verändert wurde.
            // Wenn Einzahlen gewählt wurde, dann schreib das aktuelle Guthaben in die Box,
            // falls auszahlen gewählt wurde, schreibe den Kontostand rein.

            const KontostandAusgabe = '<?=number_format($ich->Bank, 2, ",", ""); ?>';
            const KontoStand = <?=$ich->Bank; ?>;
            const BargeldAusgabe = '<?=number_format($ich->Geld, 2, ",", ""); ?>';
            const Zeiger = document.form_bank.betrag;

            if (changed) {
                return false;
            }
            if (option == 1 || option == 3) {
                Zeiger.value = BargeldAusgabe;
            } else {
                if (KontoStand > 0) {
                    Zeiger.value = KontostandAusgabe;
                } else {
                    Zeiger.value = "0,00";
                }
            }
        }

        -->
    </script>
    <form action="./actions/bank.php" method="post" name="form_bank">
        <table class="Liste" style="width: 350px;" cellspacing="0">
            <tr>
                <th>Art</th>
                <th>Betrag</th>
                <th>Best&auml;tigen</th>
            </tr>
            <tr>
                <td>
                    <select name="art" onchange="AuswahlBank(this.value); return true;">
                        <option value="1">Einzahlen</option>
                        <option value="2">Auszahlen</option>
                        <?php
                        if (intval($ich->Gruppe) > 0) {
                            ?>
                            <option value="3">In die Gruppenkasse</option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="white-space: nowrap;">
                    <input type="text" name="betrag" size="9"
                           value="<?php echo number_format($ich->Geld, 2, ",", ""); ?>"
                           onkeyup="changed=true;"/> <?= $Currency; ?>
                </td>
                <td>
                    <input type="submit" value="Best&auml;tigen"
                           onclick="document.forms[0].submit(); this.disabled='disabled'; this.value='Bitte warten...'; return false;"/>
                </td>
            </tr>
        </table>
    </form>
    <?php
}
