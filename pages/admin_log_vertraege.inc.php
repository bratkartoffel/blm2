<?php
	/**
		* Wird in die index.php eingebunden; Seite zur Ansicht des Logbuches (Verträge)
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
	
	if(!istAdmin()) {
		header("location: ./?p=index&m=101");
		header("HTTP/1.0 404 Not Found");
		die();
		break;
	}
	
	if($_GET['wer'] != "") {
		$filter_wer='%' . mysql_real_escape_string($_GET['wer']) . '%';
	}
	else {
		$filter_wer='%';
	}
	
	if($_GET['wen'] != "") {
		$filter_wen='%' . mysql_real_escape_string($_GET['wen']) . '%';
	}
	else {
		$filter_wen='%';
	}
	
	if($_GET['ware'] != "") {
		$filter_ware = intval($_GET['ware']);
	}
	else {
		$filter_ware='%';
	}
	
	if($_GET['angenommen'] != "") {
		if(intval($_GET['angenommen']) == 0)
			$filter_angenommen = 'Nein';
		else
			$filter_angenommen = 'Ja';
	}
	else {
		$filter_angenommen='%';
	}
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/admin.png" alt="Vertgslogbuch" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Logbücher - Verträge</td>
	</tr>
</table>

<?=$m; ?>
<br />
<form action="./" method="get">
	<input type="hidden" name="p" value="admin_log_vertraege" />
	<h3>Filtern nach Absender:</h3>
	<input type="text" name="wer" value="<?=htmlentities(stripslashes($_GET['wer']), ENT_QUOTES, "UTF-8"); ?>" />
	<br />
	<h3>Filtern nach Empfänger:</h3>
	<input type="text" name="wen" value="<?=htmlentities(stripslashes($_GET['wen']), ENT_QUOTES, "UTF-8"); ?>" />
	<br />
	<h3>Filtern nach Ware:</h3>
	<select name="ware">
		<option value="">- Alle -</option>
<?php
	for($i = 1; $i <= ANZAHL_WAREN; $i++) {
		if($i == $_GET['ware'])
			echo '<option value="' . $i . '" selected="selected">' . WarenName($i) . '</option>';
		else
			echo '<option value="' . $i . '">' . WarenName($i) . '</option>';
	}
?>
	</select>
	<br />
	<h3>Filtern nach Angenommen:</h3>
	<select name="angenommen">
		<option value="0">Nein</option>
		<option value="1" <?php
	if($_GET['angenommen'] == "1")
		echo 'selected="selected"';
?>>Ja</option>
		<option value="" <?php
	if($_GET['angenommen'] == "")
		echo 'selected="selected"';
?>>Alle</option>
	</select><br />
	<br />
	<input type="submit" value="Abschicken" /><br />
</form>
<br />
<table class="Liste" style="width: 720px;">
	<tr>
		<th>Wer</th>
		<th>Wen</th>
		<th>Wann</th>
		<th>Ware</th>
		<th>Wieviel</th>
		<th>Einzelpreis</th>
		<th>Gesamtpreis</th>
		<th>Angenommen?</th>
	</tr>
<?php
	$sql_abfrage="SELECT
									*,
									UNIX_TIMESTAMP(Wann) AS Wann
								FROM
									log_vertraege_view
								WHERE
									Wer LIKE '" . $filter_wer . "'
								AND
									Wen LIKE '" . $filter_wen . "'
								AND
									Ware LIKE '" . $filter_ware . "'
								AND
									Angenommen LIKE '" . $filter_angenommen . "'
								;";
	$sql_ergebnis=mysql_query($sql_abfrage);
	
	while($l=mysql_fetch_object($sql_ergebnis)) {
		?> 
		<tr>
			<td><?=htmlentities(stripslashes($l->Wer), ENT_QUOTES, "UTF-8"); ?></td>
			<td><?=htmlentities(stripslashes($l->Wen), ENT_QUOTES, "UTF-8"); ?></td>
			<td><?=date("d.m.Y H:i:s", $l->Wann); ?></td>
			<td><?=WarenName($l->Ware); ?></td>
			<td><?=number_format($l->Wieviel, 0, "", "."); ?> kg</td>
			<td><?=number_format($l->Einzelpreis, 2, ",", ".") . " " . $Currency; ?></td>
			<td><?=number_format($l->Gesamtpreis, 2, ",", ".") . " " . $Currency; ?></td>
			<td><?=$l->Angenommen; ?></td>
		</tr>
		<?php
	}
	
	if(mysql_num_rows($sql_ergebnis) == 0) {
?>
		<tr>
			<td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td>
		</tr>
<?php
	}
?>
</table>