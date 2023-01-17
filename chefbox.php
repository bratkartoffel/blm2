<?php
/*
* MIT Licence
* Copyright (c) 2023 Simon Frankenberger
*
* Please see LICENCE.md for complete licence text.
*/

$start = microtime(true);
require_once 'include/game_version.inc.php';
require_once 'include/functions.inc.php';
require_once 'include/database.class.php';

if (!isLoggedIn() || isRoundOver() || isGameLocked() || $_SESSION['blm_lastAction'] + Config::getInt(Config::SECTION_BASE, 'session_timeout') < time()) {
    session_destroy();
    die(sprintf('<!DOCTYPE html><html lang="de"><head><title>%s - Chefbox</title><script nonce="%s">self.close(); window.location.href = "%s";</script></head></html>',
        Config::get(Config::SECTION_BASE, 'game_title'),
        getCspNonce(),
        Config::get(Config::SECTION_BASE, 'base_url'))
    );
}

Database::getInstance()->begin();
if (CheckAuftraege($_SESSION['blm_user'])) {
    Database::getInstance()->commit();
} else {
    Database::getInstance()->rollback();
}

$auftraege = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user']);
$data = Database::getInstance()->getPlayerNextMafiaAndMoneyAndBank($_SESSION['blm_user']);
sendCspHeader();
?><!DOCTYPE html>
<html lang="de">
<head>
    <?php
    printHeaderCss(array('/styles/style.min.css'));
    printHeaderJs(array('/js/functions.min.js'));
    ?>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="refresh" content="300"/>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <title><?= Config::get(Config::SECTION_BASE, 'game_title'); ?> - Chefbox</title>
</head>
<body id="Chefbox">
<div id="ChefboxHead">
    <h1>BLM 2</h1>
    <h3>Chefbox<?= createHelpLink(1, 16, 'onclick="return BLMNavigation(this.href);"'); ?></h3>
</div>

<table class="Liste">
    <tr>
        <th>Auftrag (<?= count($auftraege); ?>)</th>
        <th>Restzeit</th>
    </tr>
    <?php
    foreach ($auftraege as $auftrag) {
        $duration = strtotime($auftrag['finished']) - time();
        ?>
        <tr class="Kategorie<?= floor($auftrag['item'] / job_type_factor); ?>">
            <td><?= getOrderChefboxDescription($auftrag['item']); ?></td>
            <td class="countdown"><?= formatDuration($duration); ?></td>
        </tr>
        <?php
    }

    if (count($auftraege) == 0) {
        ?>
        <tr>
            <td colspan="2"><i>Keine aktiven Aufträge gefunden!</td>
        </tr>
        <?php
    }
    ?>
</table>
<table class="Liste">
    <tr>
        <th>Kategorie</th>
        <th>Dauer / Wert</th>
    </tr>
    <tr>
        <td>Nächstes Einkommen und Zinsen:</td>
        <td class="countdown"><?= formatDuration(getLastIncomeTimestamp() + (Config::getInt(Config::SECTION_BASE, 'cron_interval') * 60) - time()); ?></td>
    </tr>
    <tr>
        <td>Nächste Mafia:</td>
        <td class="countdown"><?= formatDuration(max(0, strtotime($data['NextMafia']) - time())); ?></td>
    </tr>
    <tr>
        <td>Logout wegen Inaktivität:</td>
        <td class="countdown"><?= formatDuration(max(0, $_SESSION['blm_lastAction'] + Config::getInt(Config::SECTION_BASE, 'session_timeout') - time())); ?></td>
    </tr>
</table>
<table id="with_nav_links">
    <tr>
        <td><a href="/?p=nachrichten_liste">Neue Nachrichten:</a></td>
        <td><?= Database::getInstance()->getUnreadMessageCount($_SESSION['blm_user']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=vertraege_liste">Neue Verträge:</a></td>
        <td><?= Database::getInstance()->getOpenContractCount($_SESSION['blm_user']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=marktplatz_liste">Marktangebote:</a></td>
        <td><?= Database::getInstance()->getMarktplatzCount(); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=rangliste">Spieler online:</a></td>
        <td><?= Database::getInstance()->getOnlinePlayerCount(); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=bank">Bargeld:</a></td>
        <td><?= formatCurrency($data['Geld']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=bank">Bank-Guthaben:</a></td>
        <td><?= formatCurrency($data['Bank']); ?></td>
    </tr>
</table>
<div id="ChefboxFooter">
    <a id="show_blm" href="/?p=startseite">BLM anzeigen / öffnen</a>
    <a id="close_popup" href="/?p=startseite">Fenster schliessen</a>
</div>
<script nonce="<?= getCspNonce(); ?>">
    reloadOnCountdown = true;
    chefboxPollJobs();

    for (let link of document.getElementById('with_nav_links').getElementsByTagName('a')) {
        link.onclick = () => BLMNavigation(link.href);
    }

    document.getElementById('show_blm').onclick = () => BLMzeigen('/?p=startseite');
    document.getElementById('close_popup').onclick = () => BLMEnde();
</script>
</body>
</html>
<?php
$dauer = 1000 * (microtime(true) - $start);
?>
<!--
PLT:     <?= number_format($dauer, 2) . "ms\n"; ?>
Queries: <?= Database::getInstance()->getQueryCount() . "\n"; ?>
<?php
$warnings = Database::getInstance()->getWarnings();
if (count($warnings) > 0) {
    echo "Warnings:\n==================\n";
    foreach ($warnings as $i => $warning) {
        printf("%d: %s\n", $i, $warning);
        error_log($warning);
    }
}
?>
-->
