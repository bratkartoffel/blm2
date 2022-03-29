<?php
	/**
		* Wird in die index.php eingebunden; Hauptseite des Administrations Bereichs
		* 
		* @version 1.0.2
		* @author Simon Frankenberger <simonfrankenberger@web.de>
		* @package blm2.pages
		* 
		* @todo Gruppenverwaltung
		* @todo Benutzerverwaltung
		* @todo Auftragsverwaltung
		* 
		* @todo Logbuch Nachrichten
	*/
	
	if(!istAdmin()) {
		header("location: ./?p=index&m=101");
		header("HTTP/1.0 404 Not Found");
		die();
		break;
	}
?>
<table id="SeitenUeberschrift">
	<tr>
		<td style="width: 80px;"><img src="pics/big/admin.png" alt="Logo der Unterseite" /></td>
		<td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Administrations Bereich</td>
	</tr>
</table>

<?=$m; ?>

<br />
<br />
<ul style="margin-left: 20px;">
	<li><a href="./?p=admin_test&amp;<?=intval(time()); ?>">Variablen Testseite</a></li>
	<li><a href="./?p=admin_markt&amp;<?=intval(time()); ?>">Marktplatz</a></li>
	<li><a href="./?p=admin_vertrag&amp;<?=intval(time()); ?>">Verträge</a></li>
	<li><a href="./?p=admin_gruppe&amp;<?=intval(time()); ?>">Gruppen</a></li>
	<li><a href="./?p=admin_benutzer&amp;<?=intval(time()); ?>">Benutzer</a></li>
	<li><a href="./?p=admin_auftrag&amp;<?=intval(time()); ?>">Aufträge</a></li>
	<li><a href="./?p=admin_changelog&amp;<?=intval(time()); ?>">Changelog</a></li>
	<li>
		Logbücher:
		<ul>
			<li><a href="./?p=admin_log_bank&amp;<?=intval(time()); ?>">Bank</a></li>
			<li><a href="./?p=admin_log_bioladen&amp;<?=intval(time()); ?>">Bioladen</a></li>
			<li><a href="./?p=admin_log_gruppenkasse&amp;<?=intval(time()); ?>">Gruppenkasse</a></li>
			<li><a href="./?p=admin_log_login&amp;<?=intval(time()); ?>">Login</a></li>
			<li><a href="./?p=admin_log_mafia&amp;<?=intval(time()); ?>">Mafia</a></li>
			<li><a href="./?p=admin_log_vertraege&amp;<?=intval(time()); ?>">Verträge</a></li>
		</ul>
	</li>
	<li>
		Vorlagen:
		<ul>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=1&amp;<?=intval(time()); ?>">Verwarnung Multiaccounts</a></li>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=2&amp;<?=intval(time()); ?>">Verwarnung Passwortweitergabe</a></li>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=3&amp;<?=intval(time()); ?>">Verwarnung Bugusing</a></li>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=4&amp;<?=intval(time()); ?>">Verwarnung Spamming</a></li>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=5&amp;<?=intval(time()); ?>">Verwarnung Ausnutzung</a></li>
			<li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=6&amp;<?=intval(time()); ?>">Verwarnung Accountpushing</a></li>
		</ul>
	</li>
</ul>