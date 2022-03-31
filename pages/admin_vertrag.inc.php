<?php
/**
 * Wird in die index.php eingebunden; Seite zur Verwaltung der Verträge für Admins
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 *
 * @todo Eintrag löschen
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt="Verträge"/></td>
        <td>Admin - Verträge</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<table class="Liste" style="width: 490px" cellspacing="0">
    <tr>
        <th>Von</th>
        <th>An</th>
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
    vertraege
ORDER BY
    Von,
    Was,
    Preis;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Alle Angebote abrufen
    $_SESSION['blm_queries']++;

    $eintrag = false;        // Bisher haben wir noch kein Angebot ausgegeben, oder? ;)

    while ($angebot = mysql_fetch_object($sql_ergebnis)) {        // Alle Angebote abrufen...
        echo '<tr>
							<td>' . sichere_ausgabe(Database::getInstance()->getPlayerNameById($angebot->Von)) . '</td>
							<td>' . sichere_ausgabe(Database::getInstance()->getPlayerNameById($angebot->An)) . '</td>
							<td>' . WarenName($angebot->Was) . '</td>
							<td>' . number_format($angebot->Menge, 0, ",", ".") . ' kg</td>
							<td>' . number_format($angebot->Preis, 2, ",", ".") . ' ' . $Currency . '</td>
							<td>' . number_format($angebot->Preis * $angebot->Menge, 2, ",", ".") . ' ' . $Currency . '</td>
							<td style="white-space: nowrap; padding-top: 3px;">
								<a href="./?p=admin_vertrag_bearbeiten&amp;id=' . $angebot->ID . '"><img src="/pics/small/info.png" alt="Bearbeiten" style="border: none;" /></a>
								<a href="./actions/admin_vertrag.php?a=3&amp;id=' . $angebot->ID . '"><img src="/pics/small/error.png" alt="Löschen" style="border: none;" /></a>';
        echo '</td></tr>';            // ...und ausgeben
        $eintrag = true;        // Jetzt haben wir mindestens einen Eintrag
    }

    if (!$eintrag) {        // Falls kein Angebot gefunden wurde, dann ne entsprechende Meldung ausgeben
        echo '<tr><td colspan="7" style="text-align: center;"><i>Bisher sind noch keine Angebote vorhanden.</i></td></tr>';
    }
    ?>
</table>
<br/>
<p>
    <a href="./?p=admin_vertrag_einstellen">Neuen Vertrag erstellen</a><br/>
    <a href="./?p=admin">Zurück...</a>
</p>
