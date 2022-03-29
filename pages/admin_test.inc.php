<?php
	/**
		* Wird in die index.php eingebunden; Seite zur Ausgabe der wichtigen Variablen des Servers und des Spiels zu Debug-Zwecken
		* 
		* @version 1.0.0
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
	*/
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/admin_test.png" alt="Logo der Unterseite" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Administrations Bereich - gesetzte Variablen</td>
	</tr>
</table>

<?=$m; ?>

<br />
<br />
<fieldset>
	<legend style="font-weight: bold; font-size: 120%;">$_SERVER</legend>
	<pre><?php
	var_dump($_SERVER);
	?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
	<legend style="font-weight: bold; font-size: 120%;">$_SESSION</legend>
	<pre><?php
	var_dump($_SESSION);
	?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
	<legend style="font-weight: bold; font-size: 120%;">$ich</legend>
	<pre><?php
	var_dump($ich);
	?></pre>
</fieldset>
