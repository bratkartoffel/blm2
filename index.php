<?php
$start = microtime(true);
require_once('include/config.inc.php');
require_once('include/functions.inc.php');
require_once('include/database.class.php');

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 'true');

if (isLoggedIn()) {
    if (!isAdmin() && isRoundOver()) {
        session_destroy();
        redirectTo('/?p=anmelden', 243);
    }
    if (!isAdmin() && isGameLocked()) {
        session_destroy();
        redirectTo('/?p=anmelden', 999);
    }

    if ($_SESSION['blm_lastAction'] + session_timeout < time()) {
        session_destroy();
        redirectTo('/?p=anmelden', 102);
    }
    Database::getInstance()->begin();
    if (getOrDefault($_GET, 'rld', 0) === 0) {
        updateLastAction();
    }
    if (CheckAuftraege($_SESSION['blm_user'])) {
        Database::getInstance()->commit();
    } else {
        Database::getInstance()->rollback();
    }

    $data = Database::getInstance()->getPlayerBankAndMoneyGroupIdAndBioladenLevelAndDoenerstandLevel($_SESSION['blm_user']);
    if ($data === null) {
        session_destroy();
        redirectTo('/?p=anmelden', 102);
    }
    $data['Einkommen'] = getIncome($data['Gebaeude3'], $data['Gebaeude4']);
} else {
    $data = array();
}
?><!DOCTYPE html>
<!--
	Site generated:   <?= date("r", time()) . "\n"; ?>
	Client:           <?= escapeForOutput($_SERVER['REMOTE_ADDR']) . "\n"; ?>
	Server:           <?= escapeForOutput($_SERVER['SERVER_ADDR']) . "\n"; ?>
	Script:           <?= escapeForOutput($_SERVER['PHP_SELF']) . "\n"; ?>
	Query-String:     <?= escapeForOutput($_SERVER['QUERY_STRING']) . "\n"; ?>
	User-Agent:       <?= (array_key_exists('HTTP_USER_AGENT', $_SERVER) ? escapeForOutput($_SERVER['HTTP_USER_AGENT']) : 'none') . "\n"; ?>
	Referer:          <?= (array_key_exists('HTTP_REFERER', $_SERVER) ? escapeForOutput($_SERVER['HTTP_REFERER']) : 'none') . "\n"; ?>
-->
<html lang="de">
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.min.css?<?= game_version; ?>"/>
    <link rel="stylesheet" type="text/css" href="styles/mobile.min.css?<?= game_version; ?>"/>
    <?= (isAdmin() ? '<link rel="stylesheet" type="text/css" href="styles/admin.min.css?' . game_version . '"/>' : ''); ?>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="keywords" content="Bioladenmanager, Evil Eye Productions, Browsergame, Simon Frankenberger"/>
    <meta name="language" content="de"/>
    <meta name="viewport" content="width=device-width, initial-scale=0.60, maximum-scale=5.0, minimum-scale=0.60">
    <title><?= game_title; ?> - <?= ucfirst(getCurrentPage()); ?></title>
    <script src="/js/functions.min.js?<?= game_version; ?>"></script>
</head>
<body onload="MarkActiveLink();">
<div id="Navigation" class="<?= (isLoggedIn() ? 'online' : 'offline'); ?>">
    <div id="Logo"></div>
    <?php
    if (isLoggedIn()) {
        ?>
        <div class="NaviBox" id="Navi">
            <header>Navigation</header>
            <div class="NaviLink"><a href="/?p=index">Startseite</a></div>
            <div class="NaviBlock">
                <span>Gebäude:</span>
                <?= createNavigationLink('gebaeude', 'Gebäude', 'Gebaeude'); ?>
                <?= createNavigationLink('plantage', 'Plantage', 'Produktion'); ?>
                <?= createNavigationLink('forschungszentrum', 'Forschungszentrum', 'Forschung'); ?>
                <?= createNavigationLink('bioladen', 'Bioladen', 'Bioladen'); ?>
            </div>

            <div class="NaviBlock">
                <span>Finanzen:</span>
                <div class="NaviLink"><a href="/?p=buero">Büro</a></div>
                <?= createNavigationLink('bank', 'Bank', 'Bank'); ?>
                <?= createNavigationLink('vertraege_liste', 'Verträge (' . Database::getInstance()->getOpenContractCount($_SESSION['blm_user']) . ')', 'Vertraege'); ?>
                <?= createNavigationLink('marktplatz_liste', 'Marktplatz (' . Database::getInstance()->getMarktplatzCount() . ')', 'Marktplatz'); ?>
                <?= createNavigationLink('mafia', 'Mafia', 'Mafia'); ?>
            </div>

            <div class="NaviBlock">
                <span>Persönlich:</span>
                <?= createNavigationLink('gruppe', sprintf("Gruppe (%s)", $data['Gruppe'] === null ? '0' : Database::getInstance()->getUnreadGroupMessageCount($data['Gruppe'], $_SESSION['blm_user']) . ' / ' . Database::getInstance()->countPendingGroupDiplomacy($data['Gruppe'])), 'Gruppe'); ?>
                <?= createNavigationLink('nachrichten_liste', 'Nachrichten (' . Database::getInstance()->getUnreadMessageCount($_SESSION['blm_user']) . ')', 'Nachrichten'); ?>
                <div class="NaviLink"><a href="/?p=notizblock">Notizblock</a></div>
                <div class="NaviLink"><a href="/?p=einstellungen">Einstellungen</a>
                </div>
                <div class="NaviLink" onclick="return ChefboxZeigen(this.getElementsByTagName('a')[0].href);">
                    <a href="chefbox.php" onclick="return ChefboxZeigen(this.href);"
                       target="_blank">Chefbox</a>
                </div>
                <?= (isAdmin() ? '<div class="NaviLink"><a href="/?p=admin">Admin-Bereich</a></div>' : ''); ?>
            </div>

            <div class="NaviBlock">
                <span>Allgemein:</span>
                <div class="NaviLink">
                    <a href="/?p=rangliste&amp;o=<?= floor((Database::getInstance()->getPlayerRankById($_SESSION['blm_user']) - 1) / ranking_page_size); ?>">Rangliste</a>
                </div>
                <div class="NaviLink"><a href="/?p=statistik">Serverstatistik</a></div>
                <div class="NaviLink"><a href="/?p=regeln">Regeln</a></div>
                <div class="NaviLink"><a href="/?p=hilfe">Hilfe</a></div>
                <div class="NaviLink"><a href="/?p=impressum">Impressum</a></div>
                <div class="NaviLink"><a href="https://www.buymeacoffee.com/bratkartoffel" target="_blank">Spenden</a>
                </div>
                <div class="NaviLink"><a href="/actions/logout.php">Abmelden</a></div>
            </div>
        </div>

        <div class="NaviBox">
            <header>Statistiken:</header>
            <table id="UserStatistik">
                <tr>
                    <td>Benutzer ID:</td>
                    <td><?php
                        if ($_SESSION['blm_sitter']) {
                            echo sprintf('%d (Sitter)', $_SESSION['blm_user']);
                        } else {
                            echo sprintf('%d', $_SESSION['blm_user']);
                        }
                        ?></td>
                </tr>
                <tr>
                    <td>Bargeld:</td>
                    <td id="stat_money"><?= formatCurrency($data['Geld']); ?></td>
                </tr>
                <tr>
                    <td>Bankkonto:</td>
                    <td id="stat_bank"><?= formatCurrency($data['Bank']); ?></td>
                </tr>
                <tr>
                    <td>Grundeinkommen:</td>
                    <td id="stat_income"><?= formatCurrency($data['Einkommen']); ?></td>
                </tr>
                <tr>
                    <td>Letztes Einkommen:</td>
                    <td><?= formatTime(getLastIncomeTimestamp()); ?></td>
                </tr>
                <tr>
                    <td>Serverzeit:</td>
                    <td class="countup"><?= formatTime(time()); ?></td>
                </tr>
                <tr>
                    <td>Nächstes Einkommen:</td>
                    <td><?= formatTime(getLastIncomeTimestamp() + (cron_interval * 60)); ?></td>
                </tr>
            </table>
        </div>
        <?php
    } else {
        ?>
        <div class="NaviBox">
            <header>Navigation</header>
            <div class="NaviLink"><a href="/?p=index">Startseite</a></div>
            <div class="NaviLink"><a href="/?p=anmelden">Anmelden</a></div>
            <div class="NaviLink"><a href="/?p=registrieren">Registrieren</a></div>
            <div class="NaviLink"><a href="/?p=regeln">Regeln</a></div>
            <div class="NaviLink"><a href="/?p=impressum">Impressum</a></div>
            <div class="NaviLink"><a href="https://www.buymeacoffee.com/bratkartoffel" target="_blank">Spenden</a>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<div id="Inhalt">
    <?php
    include(sprintf('pages/%s.inc.php', getCurrentPage()));
    ?>
    <div id="Footer">
        <div>Bioladenmanager 2 Version <?= game_version; ?></div>
        <div><a href="/?p=impressum">&copy; 2007-2022, Simon Frankenberger</a></div>
        <div>Letzte Änderung: <?= date("d.m.Y H:i", filemtime('.git/HEAD')); ?></div>
    </div>
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
