<?php
require_once('./include/hilfe.inc.php');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/hilfe.png" alt=""/>
    <span>Hilfe</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier finden Sie Hilfe, falls Sie mal was suchen oder nicht genau wissen, wie Sie eine Aktion durchführen.
</p>
<?php
$mod = getOrDefault($_GET, 'mod', 1);
$cat = getOrDefault($_GET, 'cat', 1);
$cmb = $mod * 100 + $cat;

if (!array_key_exists($cmb, hilfe_texte)) {
    $mod = 1;
    $cat = 1;
    $cmb = 101;
}
?>
<div id="Hilfe">
    <header><?= $cat ?>. <?= hilfe_texte[$cmb][0]; ?></header>
    <div><?= replaceBBCode(hilfe_texte[$cmb][1]); ?></div>
</div>
<h2>Unterseiten / Module:</h2>
<ol>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=1">Registrieren</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=2">Anmelden</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=3">Startseite</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=4">Gebäude</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=5">Plantage</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=6">Forschungszentrum</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=7">Bioladen</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=8">Büro</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=9">Bank</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=10">Verträge</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=11">Marktplatz</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=12">Mafia</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=13">Nachrichten</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=14">Notizblock</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=15">Einstellungen</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=16">Chefbox</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=17">Rangliste</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=18">Serverstatistik</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=19">Regeln</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=20">Changelog</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=21">Impressum</a></li>
    <li><a href="/?p=hilfe&amp;mod=1&amp;cat=23">Gruppen</a></li>
</ol>
