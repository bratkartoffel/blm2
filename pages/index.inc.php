<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/kontact.webp" alt=""/>
        <span>Startseite<?= createHelpLink(1, 3); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

    <h2>Willkommen beim Bioladenmanager 2!</h2>
    <p>
        Legen Sie sich in die Sonne und schaun Sie den Pflanzen beim Wachsen zu!<br/>
        Bauen und Forschen Sie <?= count_wares; ?> verschiedene Gemüsesorten und
        feilschen Sie mit Ihren
        Mitspielern um den besten Preis.
    </p>
    <p>
        Bauen Sie Ihre Plantage, Ihr Forschungszentrum oder noch
        weitere <?= (count_buildings - 2); ?> Gebäude aus.<br/>
        Hetzen Sie die Mafia auf Ihre Mitspieler und klauen Sie Ihr gelagertes Gemüse oder bomben Sie Ihre Plantagen
        nieder.
    </p>
    <p>
        Werden Sie zum <em>König</em> der Biobauern!
    </p>

    <h3>Infos zur aktuellen Runde:</h3>
<?php
$data = Database::getInstance()->getRanglisteUserEntries(0, 3);
while (count($data) < 3) {
    $data[] = array('BenutzerName' => 'niemand');
}

if (isGameLocked()) {
    echo 'Die letzte Runde ist beendet, die neue Runde beginnt am <b>' . formatDateTime(Config::getInt(Config::SECTION_BASE, 'roundstart')) . '</b>.<br />
					Die Rundengewinner stehen in der Rundmail, welche versandt wurde.';
} else if (isRoundOver()) {
    echo 'Die letzte Runde ist beendet, die neue Runde beginnt voraussichtlich am <b>' . formatDateTime(Config::getInt(Config::SECTION_BASE, 'roundstart') + Config::getInt(Config::SECTION_BASE, 'game_round_duration') + Config::getInt(Config::SECTION_BASE, 'game_pause_duration')) . '</b>.';
} else {
    echo 'Die aktuelle Runde läuft seit dem <b>' . formatDate(Config::getInt(Config::SECTION_BASE, 'roundstart')) . '</b> und dauert somit <b>bis zum ' . formatDateTime(Config::getInt(Config::SECTION_BASE, 'roundstart') + Config::getInt(Config::SECTION_BASE, 'game_round_duration')) . '.</b><br />
					Der Erstplatzierte ist im Moment <b>' . escapeForOutput($data[0]['BenutzerName']) . '</b>, 
					gefolgt von <b>' . escapeForOutput($data[1]['BenutzerName']) . '</b> und 
					<b>' . escapeForOutput($data[2]['BenutzerName']) . '.</b>';
}
