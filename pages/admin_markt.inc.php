<?php
/**
 * Wird in die index.php eingebunden; Seite zur Verwaltung des Marktes für Admins
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

if (!istAdmin()) {
    header("location: ./?p=index&m=101");
    header("HTTP/1.0 404 Not Found");
    die();
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Marktplatz"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Marktplatz</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<table class="Liste" style="width: 490px" cellspacing="0">
    <tr>
        <th>Ware</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
    *
FROM
    marktplatz
ORDER BY
    Was,
    Preis;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Alle Angebote abrufen
    $_SESSION['blm_queries']++;

    $eintrag = false;        // Bisher haben wir noch kein Angebot ausgegeben, oder? ;)

    while ($angebot = mysql_fetch_object($sql_ergebnis)) {        // Alle Angebote abrufen...
        echo '<tr>
							<td>' . WarenName($angebot->Was) . '</td>
							<td>' . number_format($angebot->Menge, 0, ",", ".") . ' kg</td>
							<td>' . number_format($angebot->Preis, 2, ",", ".") . ' ' . $Currency . '</td>
							<td>' . number_format($angebot->Preis * $angebot->Menge, 2, ",", ".") . ' ' . $Currency . '</td>
							<td style="padding-top: 3px; white-space: nowrap;">
								<a href="./?p=admin_markt_bearbeiten&amp;id=' . $angebot->ID . '"><img src="pics/small/info.png" alt="Bearbeiten" style="border: none;" /></a>
								<a href="./actions/admin_markt.php?a=3&amp;id=' . $angebot->ID . '"><img src="pics/small/error.png" alt="Löschen" style="border: none;" /></a>';
        echo '</td></tr>';            // ...und ausgeben
        $eintrag = true;        // Jetzt haben wir mindestens einen Eintrag
    }

    if (!$eintrag) {    // Falls kein Angebot gefunden wurde, dann ne entsprechende Meldung ausgeben
        echo '<tr><td colspan="5" style="text-align: center;"><i>Bisher sind noch keine Angebote vorhanden.</i></td></tr>';
    }
    ?>
</table>
<br/>
<a href="./?p=admin_markt_einstellen">Neues Angebot einstellen</a>
