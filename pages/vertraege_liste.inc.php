<?php
	/**
		* Wird in die index.php eingebunden; Zeigt alle ein- und ausgehenden Verträge eines Benutzers an
		* 
		* @version 1.0.1
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/vertraege.png" alt="Vertragsliste" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Ihre vorliegenden Vertr&auml;ge
			<a href="./?p=hilfe&amp;mod=1&amp;cat=10"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>
		</td>
	</tr>
</table>
<?php
	if(!$ich->Sitter->Vertraege && $_SESSION['blm_sitter']) {
		echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
	}
	else {
?> 

<?=$m; ?>

<b>Hier sehen Sie alle Ihre eingehenden Vertr&auml;ge, die Sie noch nicht angenommen oder abgelehnt haben.</b>
<table class="Liste" style="width: 600px; margin-top: 20px; margin-bottom: 10px;" cellspacing="0">
	<tr>
		<th>Nr</th>
		<th>Von</th>
		<th>Was</th>
		<th>Menge</th>
		<th>Preis / kg</th>
		<th>Gesamtpreis</th>
		<th style="width: 80px;">Aktion</th>
	</tr>
<?php
	$sql_abfrage="SELECT
									*,
									m.Name AS Absender,
									v.ID AS vID
								FROM
									(vertraege v LEFT OUTER JOIN mitglieder m ON m.ID=v.Von)
								WHERE
									v.An=" . $_SESSION['blm_user'] . "
								ORDER BY
									v.ID;";
	$sql_ergebnis=mysql_query($sql_abfrage);		// Ruft erstmal alle Verfträge ab
	$_SESSION['blm_queries']++;
	
	$eintrag=false;		// Bisher haben wir noch keinen Eintrag
	
	while($vertrag=mysql_fetch_object($sql_ergebnis))		// Holt sich jetzt der Reihe nach alle Verträge
	{
		$nr++;		// Die Nummer für die erste Spalte
		echo '<tr>
						<td>' . $nr . '</td>
						<td>' . htmlentities(stripslashes($vertrag->Absender), ENT_QUOTES, "UTF-8") . '</td>
						<td>' . WarenName($vertrag->Was) . '</td>
						<td>' . $vertrag->Menge . ' kg</td>
						<td>' . number_format($vertrag->Preis, 2, ",", ".") . ' ' . $Currency . '</td>
						<td>' . number_format($vertrag->Preis*$vertrag->Menge,2,",",".") . " " . $Currency . '</td>
						<td>
							<a href="./actions/vertraege.php?a=2&amp;vid=' . $vertrag->vID . '" onclick="VertragAnnehmen(' . $vertrag->vID . ', this.parentNode.parentNode); this.removeAttribute(\'onclick\'); return false;">
								<img src="./pics/small/ok.png" border="0" alt="Vertrag annehmen" />
							</a>
							<a href="./actions/vertraege.php?a=3&amp;vid=' . $vertrag->vID . '" onclick="VertragAblehnen(' . $vertrag->vID . ', this.parentNode.parentNode); this.removeAttribute(\'onclick\'); return false;">
								<img src="./pics/small/error.png" border="0" alt="Vertrag ablehnen" />
							</a>
						</td>
					</tr>';		// gibt die Infos zum Vertag als Zeile aus
		$eintrag=true;		// Ja, wir haben mindestens einen Eintrag
	}
	
	if(!$eintrag) {		// falls wir keinen Eintrag haben, dann gib ne entsprechende Zeile aus
		echo '<tr><td colspan="7" style="text-align: center;"><i>Sie haben keine Vertr&auml;ge in diesem Ordner.</i></td></tr>';
	}
	
	echo '</table>';
	
	for($i=1; $i<=ANZAHL_WAREN;$i++) {		// Schaut alle Plätze des Lagers duch, und schaut ob wir überhaupt was auf Lager haben
		$temp="Lager" . $i;		// Tempöräre variable mit dem MySQL-Spalten Namen
		if($ich->$temp > 0) {			// Wenn der Lagerstand der Ware > 0 ist, dann..
			$hat_waren=true;			// ... hat der Benutzer irgendwas auf Lager
		}
	}
	
	if($hat_waren) {		// Wenn der Benutzer Waren hat, dann...
		echo '<a href="./?p=vertrag_neu&amp;' . intval(time()) . '">Neuen Vertrag aufsetzen</a><br />';		// Zeige den Link für ein neues Angebot an.
	}
	
	/*
		Ab hier werde ich die Kommentierung nicht mehr weiter machen, weil der nachfolgende Teil zu 99% das selbe beschreibt wie oben.
	*/
?>
<br />
<table cellspacing="0">
	<tr>
		<td style="width: 80px;"><img src="pics/big/vertraege.png" alt="Vertragsliste" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Ihre ausgehenden Vertr&auml;ge
			<a href="./?p=hilfe&amp;mod=1&amp;cat=10"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>
		</td>
	</tr>
</table>
<b>Hier sehen Sie alle ausgehenden Vertr&auml;ge, die Ihr Gegen&uuml;ber noch nicht angenommen hat.</b>
<table class="Liste" style="width: 600px; margin-top: 20px; margin-bottom: 10px;" cellspacing="0">
	<tr>
		<th>Nr</th>
		<th>An</th>
		<th>Was</th>
		<th>Menge</th>
		<th>Preis / kg</th>
		<th>Gesamtpreis</th>
		<th style="width: 80px;">Aktion</th>
	</tr>
	<?php
		$sql_abfrage="SELECT
										*,
										m.Name AS Empfaenger,
										v.ID AS vID
									FROM
										vertraege v LEFT OUTER JOIN mitglieder m ON m.ID=v.An
									WHERE
										v.Von=" . $_SESSION['blm_user'] . "
									ORDER BY
										v.ID;";
		$sql_ergebnis=mysql_query($sql_abfrage);
		$_SESSION['blm_queries']++;
		
		$eintrag=false;
		
		while($vertrag=mysql_fetch_object($sql_ergebnis)) {
		if($vertrag->Empfaenger == "")
			$vertrag->Empfaenger  = "-System-";
		
			$nr++;
			echo '<tr>';
			
			echo '<td>' . $nr . '</td>
							<td>' . htmlentities(stripslashes($vertrag->Empfaenger), ENT_QUOTES, "UTF-8") . '</td>
							<td>' . WarenName($vertrag->Was) . '</td>
							<td>' . $vertrag->Menge . ' kg</td>
							<td>' . number_format($vertrag->Preis, 2, ",", ".") . ' ' . $Currency . '</td>
							<td>' . number_format($vertrag->Preis*$vertrag->Menge, 2, ",", ".") . " " . $Currency . '</td>
							<td>
								<a href="./actions/vertraege.php?a=3&amp;vid=' . $vertrag->vID . '" onclick="VertragAblehnen(' . $vertrag->vID . ', this.parentNode.parentNode); this.removeAttribute(\'onclick\'); return false;">
									<img src="./pics/small/error.png" border="0" alt="Vertrag revidieren" />
								</a>
							</td>
						</tr>';
			$eintrag=true;
		}
		
		if(!$eintrag) {
			echo '<tr><td colspan="7" style="text-align: center;"><i>Sie haben keine Vertr&auml;ge in diesem Ordner.</i></td></tr>';
		}
	?>
</table>
<?php
	}
?>