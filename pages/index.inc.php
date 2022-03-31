<?php
/**
 * Wird in die index.php eingebunden; Startseite
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td><img src="/pics/big/news.png" alt="Startseite"/></td>
            <td>Startseite
                <?php
                if (istAngemeldet()) {
                    echo '<a href="./?p=hilfe&amp;mod=1&amp;cat=3"><img src="/pics/help.gif" alt="Hilfe" style="border: none;" /></a>';
                }
                ?>
            </td>
        </tr>
    </table>

<?= $m; ?>


    <h2>Willkommen beim Bioladenmanager 2!</h2>
    <h4>
        Legen Sie sich in die Sonne und schaun Sie den Pflanzen beim wachsen zu.<br/>
        Bauen und Forschen Sie <?= ANZAHL_WAREN; ?> verschiedene Gemüsesorten und feilschen Sie mit Ihren
        Mitspielern um den besten Preis.<br/><br/>
        Bauen Sie Ihre Plantage, Ihr Forschungszentrum oder noch weitere <?= (ANZAHL_GEBAEUDE - 2); ?> Gebäude aus.<br/>
        Hetzen Sie die Mafia auf Ihre Mitspieler und klauen Sie Ihr gelagertes Gemüse oder bomben Sie Ihre
        Plantagen nieder.<br/><br/>
        Werden Sie zum <span style="text-decoration: underline;">König</span> der Biobauern!
    </h4>
    <h2>Infos zur aktuellen Runde:</h2>
<?php
$db = Database::getInstance();

if (CheckGameLock()) {        // Ist das Spiel gesperrt? Wenn ja, dann
    echo 'Die letzte Runde ist beendet, und die neue Runde beginnt am <b>' . date("d.m.Y \u\m H:i", LAST_RESET) . '</b>.<br />
					Die Rundengewinner stehen in der Rundmail, welche versandt wurde.';
} else {            // Ne, es läuft gerade eine Runde
    echo 'Die aktuelle Runde läuft seit dem <b>' . date("d.m.Y", LAST_RESET) . '</b> und dauert somit <b>bis zum ' . date("d.m.Y \u\m H:i", LAST_RESET + RUNDEN_DAUER) . '.</b><br />
					Der Erstplatzierte ist im Moment <b>' . sichere_ausgabe($db->getPlayerNameByRank(1)) . '</b>, 
					gefolgt von <b>' . sichere_ausgabe($db->getPlayerNameByRank(2)) . '</b> und 
					<b>' . sichere_ausgabe($db->getPlayerNameByRank(3)) . '.</b>';
}
