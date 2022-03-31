<?php
/**
 * Wird in die index.php eingebunden; Zeigt die Hilfe zum Spiel an
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/hilfe.png" alt="Hilfe"/></td>
        <td>Die Hilfe</td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier finden Sie Hilfe, falls Sie mal was suchen oder nicht genau wissen, wie Sie eine Aktion durchführen.
</b>
<br/>
<br/>
<?php
require_once("./include/hilfe.inc.php");        // Jetzt brauchen wir auch die Hilfetexte :)

$mod = isset($_GET['mod']) ? intval($_GET['mod']) : 0;        // Welches Modul wollen wir anschauen?
$cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;        // Welches Modul wollen wir anschauen?
$cmb = intval($mod . "0" . $cat);    // Kombinierung der Modul- und der Teilnummer für das Array der Hilfetexte

switch ($cmb) {
    /*
        Überprüfung der verfügbaren Texte anhand einer Whitelist
    */
    case 101:
    case 102:
    case 103:
    case 104:
    case 105:
    case 106:
    case 107:
    case 108:
    case 109:
    case 1010:
    case 1011:
    case 1012:
    case 1013:
    case 1014:
    case 1015:
    case 1016:
    case 1017:
    case 1018:
    case 1019:
    case 1020:
    case 1021:
    case 1022:
    case 1023:
        echo '<div id="Hilfe">
							<div id="HilfeHeader">' . $cat . '. ' . $HilfeText[$cmb][0] . ':</div>
							<div id="HilfeText">
								' . ReplaceBBCode($HilfeText[$cmb][1]) . '
							</div>
						</div>';        // Den Hilfetext (welcher BB-Code haben darf) ausgeben.
        break;
}

// Nachfolgend eine Liste der verfügbaren Hilfetexte
?>
<h2>Unterseiten / Module:</h2>
<ol>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=1">Registrieren</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=2">Anmelden</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=3">Startseite</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=4">Gebäude</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=5">Plantage</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=6">Forschungszentrum</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=7">Bioladen</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=8">Büro</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=9">Bank</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=10">Verträge</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=11">Marktplatz</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=12">Mafia</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=13">Nachrichten</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=14">Notizblock</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=15">Einstellungen</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=16">Chefbox</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=17">Rangliste</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=18">Serverstatistik</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=19">Regeln</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=20">Changelog</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=21">Impressum</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=22">Abmelden</a></li>
    <li><a href="./?p=hilfe&amp;mod=1&amp;cat=23">Gruppen</a></li>
</ol>
