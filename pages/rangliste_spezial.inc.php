<?php
	/**
		* Wird in die index.php eingebunden; Genauere Aufschlüsselung eines Spezialrangs
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
	
/*
	Hier kommt eine detailiertere Seite zu den Spezialrängen in der Rangliste.
	Dabei wird hier mit Zweidimensionalen Arrays gearbeitet, nach dem Schema:
	
	$Spezial[<RANG_NUMMER>][X]
	
	wobei "X" 2 Werte hat:
		X=0:		Der Text, welcher angezeigt wird
		X=1:		Die SQL-Abfrage, welche ausgeführt wird.
*/
	$Spezial[0][0]='<b>Der Bioladenfreak</b><br /><br />Sobald ein Spieler dessen Namen hört, läuft ihm bereits ein kalter Schauer über den Rücken. <i>"Der, der nie schläft..."</i> wird über ihn gemunkelt. Er ist immer da und kann bei jedem Angriff sofort reagieren.';
	$Spezial[0][1]="SELECT
										ID,
										Name,
										CONCAT(
											(DATE_FORMAT(FROM_UNIXTIME(Onlinezeit), '%m')-1),
											' Monate, ',
											(DATE_FORMAT(FROM_UNIXTIME(Onlinezeit), '%d')-1),
											' Tage, ',
											(DATE_FORMAT(FROM_UNIXTIME(Onlinezeit), '%H')-1),
											' Stunden und ',
											(DATE_FORMAT(FROM_UNIXTIME(Onlinezeit), '%i')),
											' Minuten'
										) AS Wert
									FROM
										mitglieder
									WHERE
										ID>1
									ORDER BY
										Onlinezeit DESC
									LIMIT 0,5;";
	
	
	$Spezial[1][0]='<b>Der Pate</b><br /><br />Vor ihm erzittern alle Spieler. Er ist der Mann ohne Gnade, und wer sich ihm in den Weg stellt, wird einfach plattgemacht. Der Pate ist ein aggresiver Spieler, welcher jedes Vergehen gegen ihn sofort zurückzahlt.';
	$Spezial[1][1]="SELECT
										m.ID,
										m.Name,
										s.AusgabenMafia AS Wert
									FROM
										mitglieder m NATURAL JOIN statistik s
									WHERE
										m.ID>1
									ORDER BY
										s.AusgabenMafia DESC
									LIMIT 0,5;";
	
	
	$Spezial[2][0]='<b>Der Händlerkönig</b><br /><br />Dieser Spieler ist im ganzen Lande hoch angesehen. Wenn er kommt, dann kann man sicher sein, dass seine Waren von ihm begutachtet und bei gutem Preis gleich gekauft werden. Niemand weiß, wohin er die Waren bringt, aber sein Lager muss Riesengroß sein...';
	$Spezial[2][1]="SELECT
										m.ID,
										m.Name,
										s.AusgabenMarkt AS Wert
									FROM
										mitglieder m NATURAL JOIN statistik s
									WHERE
										m.ID>1
									ORDER BY
										s.AusgabenMarkt DESC
									LIMIT 0,5;";
	
	
	$Spezial[3][0]='<b>Der Baumeister</b><br /><br />Alle Spieler erstarren beim ersten Anblick seines Imperiums. Er ist bekannt dafür, am Bau an nichts zu sparen, und so darf er die größten Gebäude des Spiels sein Eigen nennen.';
	$Spezial[3][1]="SELECT
										m.ID,
										m.Name,
										(";
		for($i=1; $i <= ANZAHL_GEBAEUDE; $i++) {
			$Spezial[3][1].="Gebaeude" . $i . "+";
		}
		
		$Spezial[3][1]=substr($Spezial[3][1], 0, -1) . ") AS Wert
									FROM
										mitglieder m NATURAL JOIN gebaeude g
									WHERE
										ID>1
									ORDER BY
										Wert DESC
									LIMIT 0,5;";
	
	
	$Spezial[4][0]='<b>Das Genie</b><br /><br />Dieser Spieler ist bekannt für seine verrückten Ideen. Dadurch ist es ihm gelangen, seine Gemüssorten dermaßen hoch zu forschen, dass er von allen beneided wird. Seine Pfalnzen sind die größten und schönsten im ganzen Land.';
	$Spezial[4][1]="SELECT
										m.ID as mID,
										m.Name,
										(";
		for($i=1; $i <= ANZAHL_WAREN; $i++) {
			$Spezial[4][1].="Forschung" . $i . "+";
		}
		
		$Spezial[4][1]=substr($Spezial[4][1], 0, -1) . ") AS Wert
									FROM
										mitglieder m NATURAL JOIN forschung f
									WHERE
										ID>1
									ORDER BY
										Wert DESC
									LIMIT 0,5;";
	
	
	$Spezial[5][0]='<b>Der Kapitalist</b><br /><br />Der Kapitalist ist der größte Schrecken der Banken. Durch geschicktes Anlegen seines Geldes hat er schon die eine oder andere Bank in den Ruin getrieben, so munkelt man. Er ist immer auf der Suche nach den besten Zinsen und nie lange bei einer Bank...';
	$Spezial[5][1]="SELECT
										m.ID,
										m.Name,
										s.EinnahmenZinsen AS Wert
									FROM
										mitglieder m NATURAL JOIN statistik s
									WHERE
										m.ID>1
									ORDER BY
										s.EinnahmenZinsen DESC
									LIMIT 0,5;";
	
	
	$Spezial[6][0]='<b>Der Mitteilungsbedürftige</b><br /><br />Der Spieler kommt nie zu Ruhe. Immer hat er irgendwas zu schreiben dabei. Sei es auch nur eine Kleinigkeit, so muss er es trotzdem jedem mitteilen. Sein Postfach läuft über, und der Postbote kommt nicht mehr mit der Ausstellung seiner Briefe nach.';
	$Spezial[6][1]="SELECT
										ID,
										Name,
										IgmGesendet AS Wert
									FROM
										mitglieder
									WHERE
										ID>1
									ORDER BY
										IgmGesendet DESC
									LIMIT 0,5;";
	
	
	$Spezial[7][0]='<b>Der Sponsor</b><br /><br />Dieser Spieler ist der Liebling des Entwicklers. Dadurch dass er jedes Banner ansieht und <u><i>keinen Werbeblocker</i></u> installiert hat, entgeht ihm auch keines der Werbebanner. Da das Spiel von einer solchen Finanzierung abhängt, hilft er dadurch dem Spiel immens.';
	$Spezial[7][1]="SELECT
										ID,
										Name,
										BannerViews AS Wert
									FROM
										mitglieder
									WHERE
										ID>1
									ORDER BY
										BannerViews DESC,
										RAND(" . date("H") . ")
									LIMIT 0,5;";
	
	
	/*
		Jetzt wird der gewünschte Rang aus der URL geholt, und die Daten ausgegeben...
	*/
	
	$rang=intval($_GET['rang']);
	
?><table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/rangliste.png" alt="Rangliste" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die (Spezial-)Rangliste
			<a href="./?p=hilfe&amp;mod=1&amp;cat=17"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>
		</td>
	</tr>
</table>

<?=$m; ?>
<br />
<div style="border: solid 1px black; width: 350px; padding: 5px; background-color: #dddddd;">
	<?=$Spezial[$rang][0]; ?>
</div>
<br />
<br />
<table class="Liste" style="width: 500px;">
	<tr>
		<th style="width: 50px;">Platz</th>
		<th style="width: 100%;">Name</th>
		<th>Werte</th>
	</tr>
<?php
	$sql_ergebnis=mysql_query($Spezial[$rang][1]) or die("<pre>" . $Spezial[$rang][1] . "<br /><br />" . mysql_error());
	$platz=1;
	while($s = mysql_fetch_object($sql_ergebnis)) {
		if(is_numeric($s->Wert)) {
			if(intval($s->Wert) != $s->Wert) {
				$s->Wert=number_format($s->Wert, 2, ",", ".") . " " . $CurrencyC;
			}
		}
		?> 
	<tr>
		<td style="font-weight: bold;"><?=$platz; ?></td>
		<td><a href="./?p=profil&amp;uid=<?=$s->ID; ?>&amp;<?=time(); ?>"><?=htmlentities(stripslashes($s->Name), ENT_QUOTES, "UTF-8"); ?></a></td>
		<td style="text-align: right; white-space: nowrap;"><?=$s->Wert; ?></td>
	</tr>
		<?php
		$platz++;
	}
?> 
</table>
<div style="margin-top: 20px; font-size: 140%;">
	<a href="./?p=rangliste&amp;o=<?=intval($_GET['o']); ?>&amp;highlight=<?=intval($_GET['highlight']); ?>&amp;<?=time(); ?>">
		&lt;&lt; Zurück
	</a>
</div>