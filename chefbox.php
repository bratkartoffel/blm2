<?php
$start = microtime(true);
if (!file_exists('include/config.inc.php')) {
    die('include/config.inc.php not found');
}
require_once('include/config.inc.php');
require_once('include/functions.inc.php');
require_once('include/database.class.php');

if (!isLoggedIn() || isRoundOver() || isGameLocked() || $_SESSION['blm_lastAction'] + session_timeout < time()) {
    session_destroy();
    die('<!DOCTYPE html><html lang="de"><head><title>' . game_title . ' - Chefbox</title><script>self.close(); window.location.href = "' . base_url . '";</script></head></html>');
}

Database::getInstance()->begin();
CheckAuftraege($_SESSION['blm_user']);
Database::getInstance()->commit();

$auftraege = Database::getInstance()->getAllAuftraegeByVonAndWasGreaterEqualsAndWasSmaller($_SESSION['blm_user']);
$data = Database::getInstance()->getPlayerNextMafiaAndMoneyAndBank($_SESSION['blm_user']);
?><!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" type="text/css" href="/styles/style.min.css?<?= game_version; ?>"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="refresh" content="30"/>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <title><?= game_title; ?> - Chefbox</title>
    <script src="/js/functions.min.js?<?= game_version; ?>"></script>
</head>
<body id="Chefbox">
<div id="ChefboxHead">
    <h1>BLM 2</h1>
    <h3>Chefbox<?= createHelpLink(1, 16, 'onclick="return BLMNavigation(this.href);"'); ?></h3>
</div>

<table class="Liste">
    <tr>
        <th>Auftrag</th>
        <th>Restzeit</th>
    </tr>
    <?php
    foreach ($auftraege as $auftrag) {
        $duration = strtotime($auftrag['finished']) - time();
        ?>
        <tr class="Kategorie<?= floor($auftrag['item'] / 100); ?>">
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
        <td class="countdown"><?= formatDuration(getLastIncomeTimestamp() + (cron_interval * 60) - time()); ?></td>
    </tr>
    <tr>
        <td>Nächste Mafia:</td>
        <td class="countdown"><?= formatDuration(max(0, strtotime($data['NextMafia']) + mafia_sperrzeit_spionage - time())); ?></td>
    </tr>
    <tr>
        <td>Logout wegen Inaktivität:</td>
        <td class="countdown"><?= formatDuration(max(0, $_SESSION['blm_lastAction'] + session_timeout - time())); ?></td>
    </tr>
</table>
<table>
    <tr>
        <td><a href="/?p=nachrichten_liste" onclick="return BLMNavigation(this.href);">Neue Nachrichten:</a></td>
        <td><?= Database::getInstance()->getUnreadMessageCount($_SESSION['blm_user']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=vertraege_liste" onclick="return BLMNavigation(this.href);">Neue Verträge:</a></td>
        <td><?= Database::getInstance()->getOpenContractCount($_SESSION['blm_user']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=marktplatz_liste" onclick="return BLMNavigation(this.href);">Marktangebote:</a></td>
        <td><?= Database::getInstance()->getMarktplatzCount(); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=rangliste" onclick="return BLMNavigation(this.href);">Spieler online:</a></td>
        <td><?= Database::getInstance()->getOnlinePlayerCount(); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=bank" onclick="return BLMNavigation(this.href);">Bargeld:</a></td>
        <td><?= formatCurrency($data['Geld']); ?></td>
    </tr>
    <tr>
        <td><a href="/?p=bank" onclick="return BLMNavigation(this.href);">Bank-Guthaben:</a></td>
        <td><?= formatCurrency($data['Bank']); ?></td>
    </tr>
</table>
<div id="ChefboxFooter">
    <a href="/?p=startseite" onclick="return BLMzeigen(this.href);">BLM anzeigen / öffnen</a>
    <a href="/?p=startseite" onclick="return BLMEnde();">Fenster schliessen</a>
</div>
</body>
</html>
<?php
$dauer = 1000 * (microtime(true) - $start);
?>
<!--
PLT:     <?= number_format($dauer, 2) . "ms\n"; ?>
Queries: <?= Database::getInstance()->getQueryCount() . "\n"; ?>
-->
