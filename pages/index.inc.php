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
		<td style="width: 80px;"><img src="pics/big/news.png" alt="Startseite" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Startseite
			<?php
				if(istAngemeldet()) {
					echo '<a href="./?p=hilfe&amp;mod=1&amp;cat=3"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>';
				}
			?>
		</td>
	</tr>
</table>

<?=$m; ?>


<h2>Willkommen beim Bioladenmanager 2!</h2>
<h4>
	Legen Sie sich in die Sonne und schaun Sie den Pflanzen beim wachsen zu.<br />
	Bauen und Forschen Sie <?=ANZAHL_WAREN; ?> verschiedene Gem&uuml;sesorten und feilschen Sie mit Ihren Mitspielern um den besten Preis.<br /><br />
	Bauen Sie Ihre Plantage, Ihr Forschungszentrum oder noch weitere <?=(ANZAHL_GEBAEUDE-2); ?> Geb&auml;ude aus.<br />
	Hetzen Sie die Mafia auf Ihre Mitspieler und klauen Sie Ihr gelagertes Gem&uuml;se oder bomben Sie Ihre Plantagen nieder.<br /><br />
	Werden Sie zum <span style="text-decoration: underline;">K&ouml;nig</span> der Biobauern!
</h4>
<h2>Infos zur aktuellen Runde:</h2>
<?php
	if(CheckGameLock()) {		// Ist das Spiel gesperrt? Wenn ja, dann
		echo 'Die letzte Runde ist beendet, und die neue Runde beginnt am <b>' . date("d.m.Y \u\m H:i", LAST_RESET) . '</b>.<br />
					Die Rundengewinner stehen in der Rundmail, welche versandt wurde.';
	}
	else {			// Ne, es läuft gerade eine Runde
		echo 'Die aktuelle Runde läuft seit dem <b>' . date("d.m.Y", LAST_RESET) . '</b> und dauert somit <b>bis zum ' . date("d.m.Y \u\m H:i", LAST_RESET+RUNDEN_DAUER) . '.</b><br />
					Der Erstplatzierte ist im Moment <b>' . htmlentities(GetSpielerNachPlatz(1), ENT_QUOTES, "UTF-8") . '</b>, 
					gefolgt von <b>' . htmlentities(GetSpielerNachPlatz(2), ENT_QUOTES, "UTF-8") . '</b> und 
					<b>' . htmlentities(GetSpielerNachPlatz(3), ENT_QUOTES,"UTF-8") . '.</b>';
	}
?>
<br />
<h3>Je mehr Spieler dabei sind, desto mehr Spaß macht der BLM2. Helft uns, das Spiel bekannter zu machen und gebt bitte 1x täglich eure Stimme bei einem oder mehreren Seiten ab:</h3>
<a href="http://www.galaxy-news.de/charts/?op=vote&amp;game_id=2839" target="_blank" style="margin-left: 40px;">
	<img src="pics/vote.gif" style="border: none;" alt="Die besten Browsergames in den Galaxy-News MMOG-Charts!" />
</a>
<a href="http://bgs.gdynamite.de/charts_vote_1600.html" target="_blank">
	<img src="http://voting.gdynamite.de/images/gd_animbutton.gif" style="border: none;" alt="Jetzt voten auf GamesDynamite.de!" />
</a>
<a href="http://www.browserwelten.net/index.php?ac=vote&amp;gameid=2160" target="_blank">
	<img src="http://www.browserwelten.net/img/bw_votebutton.gif" style="border: none;" alt="Jetzt voten auf Browserwelten.net!" />
</a><br />
<?php
	$plaetze=getPlatzierung();
?>
<div style="margin-left: 40px; float: left; width: 90px; text-align: center;">
	Platz <b><?=$plaetze["galaxynews.de"]; ?></b>
</div>
<div style="float: left; width: 90px; text-align: center;">
	Platz <b><?=$plaetze["gamesdynamite.de"]; ?></b>
</div>
<div style="float: left; width: 90px; text-align: center;">
	Platz <b><?=$plaetze["browserwelten.de"]; ?></b>
</div>
<br />
<i>(Die Platzierungen werden jede halbe Stunde aktualisiert.)</i>