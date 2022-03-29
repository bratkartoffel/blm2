<?php
/**
 * Wird in die index.php eingebunden; Seite mit Serverstatistiken
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/writemail.png" alt="Statistik"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Serverweite Statistik
            <a href="./?p=hilfe&amp;mod=1&amp;cat=18"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>Hier sehen Sie eine Serverweite Statistik.</b><br/>
<br/>
<?php
$sql_abfrage = "SELECT
(SELECT SUM(AusgabenGebaeude+AusgabenForschung+AusgabenZinsen+AusgabenProduktion+AusgabenMarkt+AusgabenVertraege+AusgabenMafia) FROM statistik) AS AusgabenGesamt,
(SELECT SUM(EinnahmenGebaeude+EinnahmenVerkauf+EinnahmenZinsen+EinnahmenMarkt+EinnahmenVertraege+EinnahmenMafia) FROM statistik) AS EinnahmenGesamt,
(SELECT SUM(";
for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
    $sql_abfrage .= "Forschung" . $i . "+";
}
$sql_abfrage = substr($sql_abfrage, 0, -1) . ") FROM forschung) AS GesamtForschung,
(SELECT SUM(AusgabenForschung) FROM statistik) AS AusgabenForschung,
(SELECT COUNT(*) FROM mitglieder WHERE ID>0) AS AnzahlSpieler,
(SELECT SUM(IGMGesendet) FROM mitglieder) AS AnzahlIGMs,
(SELECT SUM(AusgabenGebaeude) FROM statistik) AS AusgabenGebaeude,
(SELECT SUM(";
for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
    $sql_abfrage .= "Gebaeude" . $i . "+";
}
$sql_abfrage = substr($sql_abfrage, 0, -1) . ") FROM gebaeude) AS GesamtGebaeude,
(SELECT COUNT(*) FROM gruppe) AS AnzahlGruppen,
(SELECT COUNT(*) FROM mitglieder WHERE Gruppe IS NOT NULL) AS AnzahlSpielerInGruppe;";
$sql_ergebnis = mysql_query($sql_abfrage);        // Holt sich die Statistikfelder aus der Datenbank
$_SESSION['blm_queries']++;

$statistik = mysql_fetch_object($sql_ergebnis);        // Ruft die Infos ab

$sql_abfrage2 = "SHOW TABLE STATUS FROM " . DB_DATENBANK . " WHERE Name='auftrag';";
$sql_ergebnis2 = mysql_query($sql_abfrage2);
$statistik2 = mysql_fetch_object($sql_ergebnis2);

$statistik->AnzahlAuftraege = $statistik2->Auto_increment;
?>
<table class="Liste" cellspacing="0" style="width: 300px;">
    <tr>
        <th colspan="2">Finanzen</th>
    </tr>
    <tr>
        <td>Gesamteinnahmen:</td>
        <td style="text-align: right;"><?= number_format($statistik->EinnahmenGesamt, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td>Gesamtausgaben:</td>
        <td style="text-align: right;"><?= number_format($statistik->AusgabenGesamt, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td>-&gt;Gewinn:</td>
        <td style="text-align: right;"><?= number_format($statistik->EinnahmenGesamt - $statistik->AusgabenGesamt, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td>Anzahl aller Aufträge:</td>
        <td style="text-align: right;"><?php
            $statistik->AnzahlAuftraege++;
            echo $statistik->AnzahlAuftraege - 1; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Auftrag:</td>
        <td style="text-align: right;"><?php
            echo number_format($statistik->AusgabenGesamt / $statistik->AnzahlAuftraege, 2, ",", ".") . " " . $Currency;
            ?></td>
    </tr>
    <tr>
        <td>Gewinn / Auftrag:</td>
        <td style="text-align: right;"><?php
            echo number_format(($statistik->EinnahmenGesamt - $statistik->AusgabenGesamt) / $statistik->AnzahlAuftraege, 2, ",", ".") . " " . $Currency;
            ?></td>
    </tr>
</table>
<table class="Liste" cellspacing="0" style="width: 300px; margin-top: 30px;">
    <tr>
        <th colspan="2">Forschung</th>
    </tr>
    <tr>
        <td>Ausgaben für Forschung:</td>
        <td style="text-align: right;"><?= number_format($statistik->AusgabenForschung, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td>Gesamtforschungslevel:</td>
        <td style="text-align: right;"><?= $statistik->GesamtForschung; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Forschungslevel:</td>
        <td style="text-align: right;"><?= number_format(($statistik->AusgabenForschung / $statistik->GesamtForschung), 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
</table>
<table class="Liste" cellspacing="0" style="width: 300px; margin-top: 30px;">
    <tr>
        <th colspan="2">Gebäude</th>
    </tr>
    <tr>
        <td>Ausgaben für Gebäude:</td>
        <td style="text-align: right;"><?= number_format($statistik->AusgabenGebaeude, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td>Gesamtgebäudelevel:</td>
        <td style="text-align: right;"><?= $statistik->GesamtGebaeude; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Gebäudelevel:</td>
        <td style="text-align: right;"><?= number_format(($statistik->AusgabenGebaeude / $statistik->GesamtGebaeude), 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
</table>
<table class="Liste" cellspacing="0" style="width: 400px; margin-top: 30px;">
    <tr>
        <th colspan="2">Allgemein</th>
    </tr>
    <tr>
        <td>Anzahl Spieler:</td>
        <td style="text-align: right; white-space: nowrap;"><?= $statistik->AnzahlSpieler; ?></td>
    </tr>
    <tr>
        <td>Anzahl Gruppen:</td>
        <td style="text-align: right; white-space: nowrap;"><?= $statistik->AnzahlGruppen; ?></td>
    </tr>
    <tr>
        <td>Anzahl der IGMs:</td>
        <td style="text-align: right; white-space: nowrap;"><?= $statistik->AnzahlIGMs; ?></td>
    </tr>
    <tr>
        <td>IGMs / Spieler:</td>
        <td style="text-align: right; white-space: nowrap;"><?= number_format($statistik->AnzahlIGMs / $statistik->AnzahlSpieler, 2, ",", "."); ?></td>
    </tr>
    <tr>
        <td>Spieler / Gruppe:</td>
        <td style="text-align: right; white-space: nowrap;"><?php
            if ($statistik->AnzahlGruppen > 0) {
                echo number_format($statistik->AnzahlSpielerInGruppe / $statistik->AnzahlGruppen, 2, ",", ".");
            } else {
                echo '0';
            }
            ?></td>
    </tr>
    <tr>
        <td>Entstandene Serverkosten seit Rundenstart:</td>
        <td style="text-align: right; white-space: nowrap;"><?php
            $Sekunden_seit_Start = time() - LAST_RESET;
            $Kosten_seit_Start = SERVER_KOSTEN * $Sekunden_seit_Start;

            echo number_format($Kosten_seit_Start, 2, ",", ".") . " " . $Currency;
            ?></td>
    </tr>
    <tr>
        <td>Die Antwort auf die Frage nach dem Sinn des Lebens, des Universums und allem:</td>
        <td style="text-align: right; white-space: nowrap;">42</td>
    </tr>
</table>
