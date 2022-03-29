<?php
	/**
		* Wird in die index.php eingebunden; Seite für die Specialevents
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/special.png" alt="Special" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Das Weihnachtsspecial
		</td>
	</tr>
</table>
<?php
	$hash = $_GET['hash'];
	
	if(preg_match('/^[a-f0-9]{40}$/i', $hash)) {
		$sql_abfrage="UPDATE
										special
									SET
										Abgeholt=1
									WHERE
										Hash='" . $hash . "'
									AND
										Wer='" . $_SESSION['blm_user'] . "'
									;";
		mysql_query($sql_abfrage);
		
		if(mysql_affected_rows() > 0) {
?>
<h2>
	Das Christkind hat in Ihrem Bioladen vorbeigeschaut und ein Geschenk hinterlassen. Schön versteckt hinter einem Haufen aus frisch geernteten Kartoffeln haben Sie das Geschenk gefunden. Wie ein Kind an Heiligabend reißen Sie die Verpackung herunter und öffnen das Paket.<br />
	<br />
	Darin finden Sie <?=intval($_GET['menge']); ?>kg <?=WarenName(intval($_GET['ware'])); ?>. Voller Stolz nehmen Sie die Waren aus dem Paket und verstauen sie im Lager.
</h2>
<?php
		}
		else {
?>
	<h2>Warum wollen Sie das selbe Paket 2x auspacken?</h2>
<?php
		}
	}
	else {
?>
<h2>Ho, Ho, Ho :)</h2>
Weihnachten ist die Zeit der Geschenke und des Friedens. Das gilt dieses Jahr auch für den Bioladen.<br />
Während der Zeit des Weihnachtsspecials (vom 24.12 bis 26.12) gibt es ein paar Besonderheiten für euch.<br />
<br />
Zunächst einmal habe ich die Mafia deaktiviert. In der Weihnachtszeit herrscht Frieden, und die Mafiabosse wollen da auch mal in Ruhe mit Ihren Familien die Feiertage verbringen.<br />
<br />
Ausserdem habe ich dem Christkind (ist ein guter Freund von mir *g*) die Adressen eurer Bioläden gegeben. Es wird dort in der Weihnachtszeit das eine oder andere Mal vorbeischauen und Geschenke dort verstecken. Die Geschenke werden dann auf der Seite angezeigt und ein Klick darauf lässt euch diese auspacken.<br />
Darin befindet sich dann eine Ware, welche ihr dann im Bioladen verkaufen könnt.<br />
<br />
Ich hoffe, euch gefällt diese Abwechslung im Spiel und ich wünsche euch eine schöne Weihnachtszeit.<br />
<br />
<i><b><u>Simon aka Bratkartoffel</u></b></i>
<?php
	}
?>