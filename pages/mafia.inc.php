<?php
/**
 * Wird in die index.php eingebunden; Formulare, dient zur Steuerung der Mafia
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/mafia.png" alt="Mafia"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die Mafia
                <a href="./?p=hilfe&amp;mod=1&amp;cat=12"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Mafia && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {

    echo $m;
    if ($ich->Punkte < 8000) {
        echo '<span class="MeldungR" style="font-size: 12pt;">Die Mafia kann erst ab 8.000 Punkten benutzt werden!</span>';
    } else {
        ?>
        <b>
            Hier finden Sie Jungs die für Sie die Konkurrenz in Bedrängnis setzen und die Drecksarbeit für
            Sie erledigen.
        </b>
        <br/><br/>
        <table class="Liste" cellspacing="0" style="width: 650px;">
            <tr>
                <th>Aktion</th>
                <th>Wirkung</th>
                <th style="width: 95px;">Sperrzeit</th>
                <th style="width: 50px;">Punkte</th>
            </tr>
            <tr>
                <td>Spionage</td>
                <td>Sammelt Informationen über den Gegner</td>
                <td><?= (MAFIA_SPERRZEIT_SPIONAGE / 60); ?> Minuten</td>
                <td><?= MAFIA_PUNKTE_SPIONAGE; ?></td>
            </tr>
            <tr>
                <td>Angriff</td>
                <td>Stiehlt dem Gegner zwischen <?= MAFIA_DIEBSTAHL_MIN_RATE; ?> und <?= MAFIA_DIEBSTAHL_MAX_RATE; ?>%
                    seines Bargeldes
                </td>
                <td><?= (MAFIA_SPERRZEIT_ANGRIFF / 60); ?> Minuten</td>
                <td><?= MAFIA_PUNKTE_ANGRIFF; ?></td>
            </tr>
            <tr>
                <td>Diebstahl</td>
                <td>Stiehlt dem Gegner alle Waren, die er im Lager hat</td>
                <td><?= (MAFIA_SPERRZEIT_DIEBSTAHL / 60); ?> Minuten</td>
                <td><?= MAFIA_PUNKTE_DIEBSTAHL; ?></td>
            </tr>
            <tr>
                <td>Bomben</td>
                <td>Versucht die gegnerische Plantage um ein Level zu senken</td>
                <td><?= (MAFIA_SPERRZEIT_BOMBEN / 60); ?> Minuten</td>
                <td><?= MAFIA_PUNKTE_BOMBEN; ?></td>
            </tr>
        </table>
        <script type="text/javascript">
            function Senden(Aktion) { // Schickt das Formular mit einer bestimmten Aktionsnummer weg, je nachdem was ausgewählt wurde.
                document.formular.a.value = Aktion;
                document.formular.submit();

                return false;
            }
        </script>
        <form action="actions/mafia.php" method="get" name="formular">
            <input type="hidden" name="a" value=""/>
            <table class="Liste" style="width: 660px; margin-top: 20px; margin-bottom: 10px;" cellspacing="0">
                <tr>
                    <th>Gegner</th>
                    <th>Aktion</th>
                    <th colspan="2">Kosten / Wahrscheinlichkeit für Erfolg</th>
                </tr>
                <tr>
                    <td rowspan="4" style="text-align: center; border-right: solid 1px #999999;">
                        <select name="gegner" style="min-width: 150px;">
                            <option value="0" selected="selected" disabled="disabled">Bitte wählen...</option>
                            <?php
                            $punkte_minimal = $ich->Punkte / MAFIA_FAKTOR_MIN_PUNKTE;        // Wieviele Punkte muss der Gegner mindestens haben?
                            $punkte_maximal = $ich->Punkte * MAFIA_FAKTOR_MAX_PUNKTE;        // und wieviele darf er maximal haben?

                            $sql_abfrage = "
SELECT
	ID,
	Name
FROM
	mitglieder
WHERE
(
		ID <> '" . $_SESSION['blm_user'] . "'
	AND
		ID > 0
	AND
		Punkte > 7000
	AND
		Punkte >= " . $punkte_minimal . "
	AND
		Punkte <= " . $punkte_maximal . "
	AND
		Gruppe IS NULL
)
UNION SELECT
	ID,
	Name
FROM
	mitglieder
WHERE
(
		ID <> '" . $_SESSION['blm_user'] . "'
	AND
		ID > 0
	AND
		Punkte > 7000
	AND
		Punkte >= " . $punkte_minimal . "
	AND
		Punkte <= " . $punkte_maximal . "
	AND
		Gruppe IS NOT NULL
	
	";

                            if ($ich->Gruppe != NULL) {
                                $sql_abfrage .= "AND
		Gruppe <> " . $ich->Gruppe . "
	";
                            }

                            if (count($ich->GruppeBND) > 0) {
                                foreach ($ich->GruppeBND as $bnd) {
                                    $sql_abfrage .= "AND
		Gruppe <> " . $bnd . "
	";
                                }
                            }

                            if (count($ich->GruppeNAP) > 0) {
                                foreach ($ich->GruppeNAP as $nap) {
                                    $sql_abfrage .= "AND
		Gruppe <> " . $nap . "
	";
                                }
                            }

                            $sql_abfrage .= "
)
";

                            if (count($ich->GruppeKriege) > 0) {
                                $sql_abfrage .= "OR
	Gruppe IN (" . implode(", ", $ich->GruppeKriege) . ")
";
                            }
                            $sql_abfrage .= "ORDER BY Name;";
                            $sql_ergebnis = mysql_query($sql_abfrage) or die("<pre>" . $sql_abfrage . "\n\n" . mysql_error() . "</pre>"); // Alle in Frage kommenden Gegner abrufen
                            $_SESSION['blm_queries']++;

                            while ($user = mysql_fetch_object($sql_ergebnis)) {        // und gleich mal ausgeben
                                $sql_abfrage2 = "SELECT
    1
FROM
    mitglieder
WHERE
    ID='" . $user->ID . "'
AND
    Gruppe IN (" . implode(", ", $ich->GruppeKriege) . ");";
                                $sql_ergebnis2 = mysql_query($sql_abfrage2);

                                if (mysql_num_rows($sql_ergebnis2) > 0) {
                                    echo '<option value="' . $user->ID . '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . ' (Kriegsgegner)</option>';
                                } else {
                                    echo '<option value="' . $user->ID . '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td><b>Spionage</b></td>
                    <td>
                        <select name="spionage" style="width: 200px;">
                            <option value="1">200 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;20%</option>
                            <option value="2">400 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;30%</option>
                            <option value="3">600 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;40%</option>
                            <option value="4">800 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;50%</option>
                        </select>
                    </td>
                    <?php
                    if ($ich->LastMafia + 600 >= time()) {        // Der Countdownzum erneuten Senden der Mafia ist noch nicht abgelaufen
                        echo '<td rowspan="4" style="text-align: center;"><input type="submit" value="Losschicken" disabled="disabled" /><br /><i>(Die Mafia kann erst wieder in ' . date("H:i:s", $ich->LastMafia + 600 - time() - 3600) . ' Std angeheuert werden)</i></td>';
                    } else {
                        echo '<td><input type="submit" value="Losschicken" onclick="Senden(\'1\'); return false;" /></td>';
                    }
                    ?>
                </tr>
                <tr>
                    <td><b>Angriff</b></td>
                    <td>
                        <select name="bargeld_angriff" style="width: 200px;">
                            <option value="1">300 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;20%</option>
                            <option value="2">600 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;30%</option>
                            <option value="3">900 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;40%</option>
                            <option value="4">1200 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;50%</option>
                        </select>
                    </td>
                    <?php
                    if ($ich->LastMafia + 600 < time()) {
                        echo '<td><input type="submit" value="Losschicken" onclick="Senden(\'4\'); return false;" /></td>';
                    }
                    ?>
                </tr>
                <tr>
                    <td><b>Diebstahl</b></td>
                    <td>
                        <select name="diebstahl" style="width: 200px;">
                            <option value="1">500 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;20%</option>
                            <option value="2">1000 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;30%</option>
                            <option value="3">1500 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;40%</option>
                            <option value="4">2000 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;50%</option>
                        </select>
                    </td>
                    <?php
                    if ($ich->LastMafia + 600 < time()) {
                        echo '<td><input type="submit" value="Losschicken" onclick="Senden(\'2\'); return false;" /></td>';
                    }
                    ?>
                </tr>
                <tr>
                    <td><b>Bomben</b></td>
                    <td>
                        <select name="angriff" style="width: 200px;">
                            <option value="1">1000 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;10%</option>
                            <option value="2">2500 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;18%</option>
                            <option value="3">4000 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;26%</option>
                            <option value="4">6500 <?php echo $Currency; ?>&nbsp;&nbsp;-&nbsp;&nbsp;32%</option>
                        </select>
                    </td>
                    <?php
                    if ($ich->LastMafia + 600 < time()) {
                        echo '<td><input type="submit" value="Losschicken" onclick="Senden(\'3\'); return false;" /></td>';
                    }
                    ?>
                </tr>
            </table>
        </form>
        <?php
    }
}
