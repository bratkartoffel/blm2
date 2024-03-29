<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$offset = getOrDefault($_GET, 'o', function () {
    return floor((Database::getInstance()->getPlayerRankById($_SESSION['blm_user']) - 1) / Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
});
$offset_gr = getOrDefault($_GET, 'o_gr', 0);
$offset_ep = getOrDefault($_GET, 'o_ep', 0);
$q = getOrDefault($_GET, 'q');

$lastPoints = formatTime(Config::getInt(Config::SECTION_DBCONF, 'lastpoints'));
$nextPoints = formatTime(Config::getInt(Config::SECTION_DBCONF, 'lastpoints') + Config::getInt(Config::SECTION_BASE, 'points_interval') * 3600);
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/Login_Manager.webp" alt=""/>
    <span>Rangliste<?= createHelpLink(1, 17); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier werden die Besten der Besten aufgelistet, sortiert nach ihren Punkten.
    Die Punkte wurden zuletzt um <?= $lastPoints; ?> berechnet.
    Das nächste Update erfolgt um <?= $nextPoints; ?>.
</p>

<?php
if ($q !== null) {
    $playerRankByName = Database::getInstance()->getPlayerRankByName($q) - 1;
    $offset = floor($playerRankByName / Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
}
$playerCount = Database::getInstance()->getPlayerCount();
$offset = verifyOffset($offset, $playerCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
?>
<h2>Spieler:</h2>
<table class="Liste Rangliste" id="User">
    <tr>
        <th>Platz</th>
        <th>Name</th>
        <th>Punkte</th>
        <th>Aktion</th>
    </tr>
    <?php
    $myself = Database::getInstance()->getPlayerPointsAndMoneyAndNextMafiaAndGroupById($_SESSION['blm_user']);
    $entries = Database::getInstance()->getRanglisteUserEntries($offset, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        $groupDiplomacy = null;
        if ($myself['Gruppe'] !== null && $row['GruppeID'] !== null) {
            if ($row['BenutzerID'] != $_SESSION['blm_user']) {
                $groupDiplomacy = Database::getInstance()->getGroupDiplomacyTypeById($myself['Gruppe'], $row['GruppeID']);
            }
            if ($myself['Gruppe'] === $row['GruppeID']) {
                $groupDiplomacy = group_diplomacy_bnd;
            }
        }
        if ($row['BenutzerID'] == $_SESSION['blm_user'] || ($q !== null && strtolower($row['BenutzerName']) == strtolower($q))) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset * Config::getInt(Config::SECTION_BASE, 'ranking_page_size')) + $i + 1; ?></td>
            <td>
                <?php
                echo createProfileLink($row['BenutzerID'], $row['BenutzerName']);
                if ($row['IstAdmin']) {
                    echo '<div class="UserRank Administrator"></div>';
                }
                if ($row['IstBetatester']) {
                    echo '<div class="UserRank BetaTester"></div>';
                }
                if ($row['GruppeID'] !== null) {
                    printf(' (<a href="./?p=gruppe&id=%d">%s</a>)',
                        $row['GruppeID'],
                        $row['GruppeName']);
                }
                ?>
            </td>
            <td><?= formatPoints($row['Punkte']); ?></td>
            <td><?php
                if ($row['BenutzerID'] != $_SESSION['blm_user']) {
                    printf('(<a href="./?p=nachrichten_schreiben&receiver=%s">IGM</a> | <a href="./?p=vertraege_neu&empfaenger=%s">Vertrag</a>',
                        escapeForOutput($row['BenutzerName']), escapeForOutput($row['BenutzerName']));
                    if (mafiaRequirementsMet($row['Punkte']) && mafiaRequirementsMet($myself['Punkte']) && maybeMafiaOpponents($row['Punkte'], $myself['Punkte'], $groupDiplomacy)) {
                        printf(' | <a href="./?p=mafia&amp;opponent=%d">Mafia</a>', $row['BenutzerID']);
                    }
                    echo ')';
                }
                ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?= createPaginationTable("pages_users", sprintf('/?p=rangliste&o_gr=%d&o_ep=%d', $offset_gr, $offset_ep), $offset, $playerCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'), 'o', 'User'); ?>

<div>
    <form action="/" method="get">
        <input type="hidden" name="p" value="rangliste"/>
        <label for="q">Spielersuche:</label>
        <input type="text" name="q" id="q" value="<?= escapeForOutput($q); ?>" size="24"/>
        <input type="submit" value="Suchen"/>
    </form>
</div>

<?php
$groupCount = Database::getInstance()->getGroupCount();
$offset_gr = verifyOffset($offset_gr, $groupCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
?>
<h2>Gruppen:</h2>
<table class="Liste Rangliste" id="Gruppe">
    <tr>
        <th>Platz</th>
        <th>Name</th>
        <th>Kürzel</th>
        <th>Mitglieder</th>
        <th>Punkte</th>
        <th>Durchschnitt</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getRanglisteGroupEntries($offset_gr, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        if (strstr($row['Mitglieder'], ';' . $_SESSION['blm_user'] . ';')) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset_gr * Config::getInt(Config::SECTION_BASE, 'ranking_page_size')) + $i + 1; ?></td>
            <td><?= createGroupLink($row['GruppeID'], $row['GruppeName']); ?></td>
            <td><?= escapeForOutput($row['GruppeKuerzel']); ?></td>
            <td><?= $row['AnzMitglieder']; ?></td>
            <td><?= formatPoints($row['Punkte']); ?></td>
            <td><?= formatPoints((int)($row['Punkte'] / $row['AnzMitglieder'])); ?></td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="6" class="center"><i>Bisher wurden noch keine Gruppen erstellt.</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable("pages_groups", sprintf('/?p=rangliste&o=%d&o_ep=%d', $offset, $offset_ep), $offset_gr, $groupCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'), 'o_gr', 'Gruppe'); ?>

<?php
$epCount = Database::getInstance()->getEwigePunkteCount();
$offset_ep = verifyOffset($offset_ep, $epCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
?>
<h2>Ewige Punkte</h2>
<table class="Liste Rangliste" id="EwigePunkte">
    <tr>
        <th>Platz</th>
        <th>Name</th>
        <th>Punkte</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getEwigePunkteEntries($offset_ep, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'));
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        if ($row['ID'] == $_SESSION['blm_user'] || ($q !== null && strtolower($row['Name']) == strtolower($q))) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset_ep * Config::getInt(Config::SECTION_BASE, 'ranking_page_size')) + $i + 1; ?></td>
            <td><?= createProfileLink($row['ID'], $row['Name']); ?></td>
            <td><?= formatPoints($row['EwigePunkte']); ?></td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="3" class="center"><i>Bisher sind noch keine ewigen Punkte vergeben worden.</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable("pages_immortal", sprintf('/?p=rangliste&o=%d&o_gr=%d', $offset, $offset_gr), $offset_ep, $epCount, Config::getInt(Config::SECTION_BASE, 'ranking_page_size'), 'o_ep', "EwigePunkte"); ?>

<h2>Verschiedenes</h2>
<table class="Liste Rangliste">
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=0">Der Bioladenfreak:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderOnlineTime()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit ' . formatDuration($data['Onlinezeit']);
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=1">Der Pate:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderMafia()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenMafia']) . ' für die Mafia';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=2">Der Händlerkönig:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderMarket()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenMarkt']) . ' auf dem freien Markt';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=3">Der Baumeister:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderBuildings()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenGebaeude']) . ' für Gebäude';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=4">Das Genie:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderResearch()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenForschung']) . ' für die Forschung';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=5">Der Top-Bauer:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderProduction()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenProduktion']) . ' für die Produktion';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=6">Der Kapitalist:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderInterest()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Einnahmen von ' . formatCurrency($data['EinnahmenZinsen']) . ' durch Zinsen';
            ?></td>
    </tr>
    <tr>
        <th><a href="./?p=rangliste_spezial&amp;type=7">Der Mitteilungsbedürftige:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderIgmSent()[0];
            echo createProfileLink($data['ID'], $data['Name']) . ' mit ' . formatPoints($data['IgmGesendet']) . ' gesendeten Nachrichten';
            ?></td>
    </tr>
</table>
