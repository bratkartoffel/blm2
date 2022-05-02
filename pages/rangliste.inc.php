<?php
$offset = getOrDefault($_GET, 'o', 0);
$offset_gr = getOrDefault($_GET, 'o_gr', 0);
$offset_ep = getOrDefault($_GET, 'o_ep', 0);
$q = getOrDefault($_GET, 'q');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.png" alt=""/>
    <span>Serverstatistik<?= createHelpLink(1, 17); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier werden die Besten der Besten aufgelistet, sortiert nach ihren Punkten.
</p>

<?php
if ($q !== null) {
    $playerRankByName = Database::getInstance()->getPlayerRankByName($q) - 1;
    $offset = floor($playerRankByName / ranking_page_size);
}
$playerCount = Database::getInstance()->getPlayerCount();
$offset = verifyOffset($offset, $playerCount, ranking_page_size);
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
    $entries = Database::getInstance()->getRanglisteUserEntries($offset, ranking_page_size);
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        if ($row['BenutzerID'] != $_SESSION['blm_user'] && $myself['Gruppe'] !== null && $row['GruppeID'] !== null) {
            $groupDiplomacy = Database::getInstance()->getGroupDiplomacyTypeById($myself['Gruppe'], $row['GruppeID']);
        } else {
            $groupDiplomacy = -1;
        }

        if ($row['BenutzerID'] == $_SESSION['blm_user'] || strtolower($row['BenutzerName']) == strtolower($q)) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset * ranking_page_size) + $i + 1; ?></td>
            <td>
                <?php
                if (strtotime($row['LastAction']) + 1800 >= time()) {
                    $status_image = '/pics/small/gadu.png';
                } else {
                    $status_image = '/pics/small/home.png';
                }
                ?>
                <img src="<?= $status_image; ?>" alt=""/>
                <?php
                echo createProfileLink($row['BenutzerID'], $row['BenutzerName']);
                if ($row['IstAdmin']) {
                    echo '<img src="/pics/small/bookmark.png" alt="" title="Admin"/>';
                }
                if ($row['IstBetatester']) {
                    echo '<img src="/pics/small/bookmark_Silver.png" alt="" title="Betatester"/>';
                }
                if ($row['GruppeID'] !== null) {
                    echo sprintf(' (<a href="/?p=gruppe&id=%d">%s</a>)',
                        $row['GruppeID'],
                        $row['GruppeName']);
                }
                ?>
            </td>
            <td><?= formatPoints($row['Punkte']); ?></td>
            <td><?php
                if ($row['BenutzerID'] != $_SESSION['blm_user']) {
                    echo sprintf('(<a href="/?p=nachrichten_schreiben&receiver=%s">IGM</a> | <a href="/?p=vertraege_neu&empfaenger=%s">Vertrag</a>',
                        escapeForOutput($row['BenutzerName']), escapeForOutput($row['BenutzerName']));
                    if (mafiaRequirementsMet($row['Punkte']) && mafiaRequirementsMet($myself['Punkte']) && maybeMafiaOpponents($row['Punkte'], $myself['Punkte'], $groupDiplomacy)) {
                        echo sprintf(' | <a href="/?p=mafia&amp;opponent=%s">Mafia</a>', escapeForOutput(escapeForOutput($row['BenutzerName'])));
                    }
                    echo ')';
                }
                ?></td>
        </tr>
        <?php
    }
    ?>
</table>
<?= createPaginationTable(sprintf('/?p=rangliste&o_gr=%d&o_ep=%d', $offset_gr, $offset_ep), $offset, $playerCount, ranking_page_size, 'o', 'User'); ?>

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
$offset_gr = verifyOffset($offset_gr, $groupCount, ranking_page_size);
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
    $entries = Database::getInstance()->getRanglisteGroupEntries($offset_gr, ranking_page_size);
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        if (strstr($row['Mitglieder'], ';' . $_SESSION['blm_user'] . ';')) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset_gr * ranking_page_size) + $i + 1; ?></td>
            <td><?= createGroupLink($row['GruppeID'], $row['GruppeName']); ?></td>
            <td><?= escapeForOutput($row['GruppeKuerzel']); ?></td>
            <td><?= $row['AnzMitglieder']; ?></td>
            <td><?= formatPoints($row['Punkte']); ?></td>
            <td><?= formatPoints($row['Punkte'] / $row['AnzMitglieder']); ?></td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>Bisher wurden noch keine Gruppen erstellt.</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable(sprintf('/?p=rangliste&o=%d&o_ep=%d', $offset, $offset_ep), $offset_gr, $groupCount, ranking_page_size, 'o_gr', 'Gruppe'); ?>

<?php
$epCount = Database::getInstance()->getEwigePunkteCount();
$offset_ep = verifyOffset($offset_ep, $epCount, ranking_page_size);
?>
<h2>Ewige Punkte</h2>
<table class="Liste Rangliste" id="EwigePunkte">
    <tr>
        <th>Platz</th>
        <th>Name</th>
        <th>Punkte</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getEwigePunkteEntries($offset_ep, ranking_page_size);
    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        if ($row['ID'] == $_SESSION['blm_user'] || strtolower($row['Name']) == strtolower($q)) {
            $rowExtra = ' class="highlight"';
        } else {
            $rowExtra = null;
        }
        ?>
        <tr<?= $rowExtra; ?>>
            <td><?= ($offset_ep * ranking_page_size) + $i + 1; ?></td>
            <td><?= createProfileLink($row['ID'], $row['Name']); ?></td>
            <td><?= formatPoints($row['EwigePunkte']); ?></td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="3" style="text-align: center;"><i>Bisher sind noch keine ewigen Punkte vergeben worden.</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable(sprintf('/?p=rangliste&o=%d&o_gr=%d', $offset, $offset_gr), $offset_ep, $epCount, ranking_page_size, 'o_ep', "EwigePunkte"); ?>

<h2>Verschiedenes</h2>
<table class="Liste Rangliste">
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=0">Der Bioladenfreak:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderOnlineTime();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit ' . formatDuration($data['Onlinezeit']);
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=1">Der Pate:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderMafia();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenMafia']) . ' für die Mafia';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=2">Der Händlerkönig:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderMarket();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenMarkt']) . ' auf dem freien Markt';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=3">Der Baumeister:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderBuildings();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenGebaeude']) . ' für Gebäude';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=4">Das Genie:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderResearch();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenForschung']) . ' für die Forschung';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=5">Der Top-Bauer:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderProduction();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Ausgaben von ' . formatCurrency($data['AusgabenProduktion']) . ' für dir Produktion';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=6">Der Kapitalist:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderInterest();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit Einnahmen von ' . formatCurrency($data['EinnahmenZinsen']) . ' durch Zinsen';
            ?></td>
    </tr>
    <tr>
        <th><a href="/?p=rangliste_spezial&amp;type=7">Der Mitteilungsbedürftige:</a></th>
        <td><?php
            $data = Database::getInstance()->getLeaderIgmSent();
            echo createProfileLink($data['ID'], $data['Name']) . ' mit ' . formatPoints($data['IgmGesendet']) . ' gesendeten Nachrichten';
            ?></td>
    </tr>
</table>
