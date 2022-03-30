<?php
/**
 * Dies ist die Hauptseite des Spiels. Diese stellt ein Grundgerüst zur Verfügung, in das die angeforderte Unterseite eingebunden wird.
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2
 */
/*
Changelog:

[1.0.1]
    - Serverpfad durch Konstante ersetzt

*/

ini_set("url_rewriter.tags", "");        // Parameter werden nun nicht mehr automatisch an jeden Link angehängt. (Leider Standard auf meinem Server...)
header('Content-type: text/html; charset="utf-8"');        // Wir teilen dem Browser mit, dass dieses Dokument als UTF-8 kodiert ist.
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
ignore_user_abort(true);        // Ignoriert den Abbruch des Benutzers

$start = time() + microtime(true);    // Die Startzeit des Scripts für den Benchmark

/*
    Dann werden alle wichtigen Dateien mit den Konstanten, Variablen und Funktionen eingebunden.
*/
include("./include/config.inc.php");                // bindet die Konfigurationsdatei ein
include("./include/functions.inc.php");            // bindet die Funktionen ein
include("./include/captcha_class/captcha.php");            // bindet die Funktionen ein

if (WARTUNGS_ARBEITEN) {
    session_unset();
    session_destroy();
    die('<img src="pics/pylone.png" alt="Wartungsarbeiten..." style="float: left; margin-right: 20px;" /><h2 style="padding-top: 190px;">' . WARTUNGS_TEXT . "</h2><br /><br /><h3>Hier gehts zum <a href=\"forum/\">Forum</a></h3>");
}

// error_reporting(0);		// Dann schalten wir das Error-Reporting-Feature aus, das stört den normalen Benutzer nur ;)
ConnectDB();        // So, dann bauen wir mal die Verbindung mit der Datenbank auf.

if (CheckRundenEnde()) {        // Wenn die aktuelle Runde zu Ende ist, dann...
    ResetAll(true, $Start);            // ... wird ein Reset durchgeführt.
}

if (istAngemeldet()) {        // Ist der Benutzer angemeldet? Wenn ja, dann...
    $ich = LoadSettings();        // Alle Daten des Users laden
    CheckAllAuftraege();        // Die Auftragsliste abarbeiten
    $ich = LoadSettings();        // Meine Daten nochmal laden, denn es könnte sich ja was geändert haben ;)
    $ich->Sitter = LoadSitterSettings();

    if ($ich->LastAction + TIMEOUT_INAKTIV < time() && !$_SESSION['blm_sitter']) {
        DisconnectDB();
        session_unset();
        session_destroy();
        header("location: ./?p=index&m=102");
    }

    $ich->Rechte = RechteGruppe(0, false, $ich->GruppeRechte);

    $Einkommen = (EINKOMMEN_BASIS + ($ich->Gebaeude3 * EINKOMMEN_BIOLADEN_BONUS) + ($ich->Gebaeude4 * EINKOMMEN_DOENERSTAND_BONUS));        // Dann das Einkommen ausrechnen

    if ($ich->Punkte < 100000) {
        if ($ich->Bank <= DISPO_LIMIT) {            // Wurde der Kreditrahmen überschritten?
            ResetAccount($ich->ID, $Start);            // Wenn ja, dann resette den Account
            header("location: ./?p=index&m=114");        // Und gib ihm eine entsprechende Meldung aus
            die();        // Das wars soweit.
        }
    } else {
        if ($ich->Bank <= (-0.33 * $ich->Punkte)) {            // Wurde der Kreditrahmen überschritten?
            ResetAccount($ich->ID, $Start);            // Wenn ja, dann resette den Account
            header("location: ./?p=index&m=114");        // Und gib ihm eine entsprechende Meldung aus
            die();        // Das wars soweit.
        }
    }
    switch ($_GET['p']) {        // dann wird überprüft, ob die Seite überhaupt eingebunden werden darf anhand einer Whitelist
        case "admin":        // Diese Seite darf er nur als Admin sehen
        case "admin_test":
        case "admin_markt":
        case "admin_vertrag":
        case "admin_vertrag_einstellen":
        case "admin_vertrag_bearbeiten":
        case "admin_markt_einstellen":
        case "admin_markt_bearbeiten":
        case "admin_changelog":
        case "admin_log_bank":
        case "admin_log_bioladen":
        case "admin_log_gruppenkasse":
        case "admin_log_login":
        case "admin_log_mafia":
        case "admin_log_vertraege":
            /** @noinspection PhpMissingBreakStatementInspection */
        case "admin_vorlage_verwarnungen":
            if (!istAdmin()) {
                header("location: ./?p=index&m=101");
                header("HTTP/1.0 404 Not Found");
                die();
            }
        case "bank":
        case "bioladen":
        case "buero":
        case "forschungszentrum":
        case "gebaeude":
        case "marktplatz_liste":
        case "marktplatz_verkaufen":
        case "plantage":
        case "vertraege_liste":
        case "vertrag_neu":
        case "mafia":
        case "statistik":
        case "gruppe":
        case "gruppe_einstellungen":
        case "gruppe_mitgliederverwaltung":
        case "gruppe_diplomatie":
        case "gruppe_kasse":
        case "gruppe_logbuch":
        case "gruppe_krieg_details":
        case "rangliste":
        case "rangliste_spezial":
        case "index":
        case "impressum":
        case "regeln":
        case "changelog":
        case "einstellungen":
        case "nachrichten_lesen":
        case "nachrichten_liste":
        case "nachrichten_schreiben":
        case "notizblock":
        case "hilfe":
        case "profil":
        case "special":
            $Seite = $_GET['p'];        // Die angeforderte Seite ist in der Liste und darf somit angesehen werden.
            break;
        default:
            $Seite = "index";            // Wenn keine Seite angefordert wurde, dann einfach die Startseite anzeigen.
            header("HTTP/1.0 404 Not Found");
            break;
    }
} else {    // Der Benutzer ist nicht angemeldet, also darf er nur bestimmte Seiten sehen
    switch ($_GET['p']) {        // Überprüft die angeforderte Seite
        case "anmelden":
        case "registrieren":
        case "index":
        case "regeln":
        case "impressum":
            $Seite = $_GET['p'];        // Diese Seiten darf er sehen
            break;
        case "":
            $Seite = "index";            // Wenn keine Seite angefordert wurde, dann einfach die Startseite anzeigen.
            break;
        default:
            $Seite = "index";            // Wenn keine Seite angefordert wurde, dann einfach die Startseite anzeigen.
            header("HTTP/1.0 404 Not Found");
            break;
    }
}

$m = CheckMessage($_GET['m']);        // Falls im Parameter der Seite ein Meldungscode steckt, schon mal den Meldungstext abrufen

switch ($Seite) {        // Gibt eine Beschreibung der Seite je nach Unterseite aus. Dies ist vorwiegend für die Beschreibung bei den Suchmaschinen gedacht.
    case "registrieren":
        $Beschreibung = "Melden Sie sich noch heute beim Bioladenmanager 2 an und zeigen Sie Ihren Mitspieleren, wer der Boss ist!";
        break;
    case "anmelden":
        $Beschreibung = "Starten Sie hier Ihr eigenes Bio-Imperium, Forschen und Pflanzen Sie verschiedenste Obst- und Gemüsesorten. Und das ganze in Echtzeit!";
        break;
    case "regeln":
        $Beschreibung = "Wie jedes Spiel hat auch der Bioladenamanger 2 bestimmte Regeln, welche eingehalten werden müssen. Diese sind dringend notwendig, um einen schönen und reibungslosen Spielverlauf gewährlisten zu können.";
        break;
    case "impressum":
        $Beschreibung = "Der Bioladenamanger 2 wurde programmiert von Simon Frankenberger und steht kostenlos zum Download bereit. Das Spiel wurde unter der \"Creative Commons Namensnennung-NichtKommerziell-Weitergabe unter gleichen Bedingungen 2.0 Deutschland\" veröffentlicht.";
        break;
    default:
        $Beschreibung = "Willkommen beim Bioladenmanager 2, ein Browsergame um Gemüse, Obst und Macht... Werden Sie der König der Biobauern!";
        break;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
	Site generated:   <?= date("r", time()) . "\n"; ?>
	Client:           <?= htmlentities($_SERVER['REMOTE_ADDR']) . "\n"; ?>
	Server:           <?= htmlentities($_SERVER['SERVER_ADDR']) . "\n"; ?>
	Script:           <?= htmlentities($_SERVER['PHP_SELF']) . "\n"; ?>
	Query-String:     <?= htmlentities($_SERVER['QUERY_STRING']) . "\n"; ?>
	User-Agent:       <?= htmlentities($_SERVER['HTTP_USER_AGENT']) . "\n"; ?>
	Referer:          <?= htmlentities($_SERVER['HTTP_REFERER']) . "\n"; ?>
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <?php
    if (SPECIAL_STYLE) {
        ?>
        <link rel="stylesheet" type="text/css" href="styles/special.css"/>
        <?php
    } else {
        ?>
        <link rel="stylesheet" type="text/css" href="styles/style.css"/>
        <?php
    }
    ?>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="creator" content="Simon Frankenberger"/>
    <meta name="description" content="<?= $Beschreibung; ?>"/>
    <meta name="keywords" content="Bioladenmanager, Evil Eye Productions, Browsergame, Simon Frankenberger"/>
    <meta name="date" content="<?= date("c"); ?>"/>
    <meta name="language" content="de"/>
    <title><?= $Titel ?></title>
    <script type="text/javascript" src="js/functions.js?<?= VERSION; ?>"></script>
    <script type="text/javascript" src="js/ajax.js?<?= VERSION; ?>"></script>
    <script type="text/javascript">
        <!--
        function MarkActiveLink() {
            // Markiert den aktuellen Menüpunkt (Standort) des Users
            const z_links = document.getElementById("Navigation").getElementsByTagName("a");		// Zeigt auf alle Links der NAvigation
            const Seite = '<?=$Seite; ?>';		// Beinhaltet die aktuelle Seite

            try {
                for (let i = 0; i < z_links.length; i++) {		// Läuft alle Links der Navigation durch
                    if (z_links[i].target != "_blank") {
                        let aktLink = z_links[i].href.split("/");		// Zerteilt den Link in seine Bestandteile
                        aktLink = aktLink[aktLink.length - 1];				// Holt sich nur den Teil nach dem letzten "/"

                        aktLink = aktLink.split("?p=");		// Dann holen wir uns die Seite raus
                        aktLink = aktLink[1].split("&");	// ohne den abschließenden Trenner "&"

                        if (aktLink[0] == Seite) {		// Wenn der aktuelle Link der Standort des Users ist, ...
                            z_links[i].innerHTML = '→ <i>' + z_links[i].innerHTML + "</i>";		// Dann verändere ihn
                            z_links[i].style.color = "#555555";		// und gib ihm eine andere Farbe
                        }
                    }
                }
            } catch (e) {
                // nothing to do
            }
        }

        // -->
    </script>
    <!-- Kleiner Hack für den IE -->
    <!--[if IE]>
			<link rel="stylesheet" type="text/css" href="styles/style_ie.css?<?= time(); ?>" />
		<![endif]-->
</head>
<body onload="MarkActiveLink();">
<div id="Wrapper">
    <div id="Inhalt">
        <?php
        if (CheckGameLock()) {        // Ist das Spiel gesperrt?
            $m = CheckMessage(999) . $m;    // Zeigt die Meldung an, dass das Spiel pausiert ist und wielange es noch dauert.
        }

        include("pages/" . $Seite . ".inc.php");        // Bindet die eigentliche Unterseite ein.
        ?>
        <div id="Footer" style="white-space: nowrap;">
            <?php
            if (SPECIAL_STYLE) {
                ?>
                <div id="weihnachtsspecial" style="position: relative; display: none; float: left;">
                </div>
                <img src="pics/tree.png" height="100" width="100" style="float: left; margin-left: 30px;"
                     alt="Weihnachtsbaum"/>
                <?php
            }
            ?>
            Bioladenmanager 2 Version <?= VERSION; ?><br/>
            <a href="./?p=impressum">
                &copy; 2007-2008, Simon Frankenberger.
            </a><br/>
            Letzte Änderung: <a href="?p=changelog"><?= date("d.m.Y", LetzteAenderung()); ?></a>
        </div>
    </div>
    <div id="Navigation">
        <img src="./pics/logo.png" alt="BLM 2"
             style="padding: 0; margin: 0 0 7px 30px;"/>
        <div class="NaviOben">&nbsp;</div>
        <div class="NaviMitte">
            <div class="NaviHeader">Navigation</div>
            <?php
            if (IstAngemeldet()) {        // Folgenden Block (Navigationsleiste) nur für angemeldete Benutzer anzeigen
                ?>
                <div class="NaviLink" onclick="Navigation(this);">
                    <a href="./?p=index&amp;<?= time(); ?>">Startseite</a>
                </div>
                <div class="NaviLinkHeader">Gebäude:</div>
                <?php
                if ($ich->Sitter->Gebaeude || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=gebaeude&amp;<?= time(); ?>">Gebäude</a></div>
                    <?php
                }

                if ($ich->Sitter->Produktion || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=plantage&amp;<?= time(); ?>">Plantage</a></div>
                    <?php
                }

                if ($ich->Sitter->Forschung || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=forschungszentrum&amp;<?= time(); ?>">Forschungszentrum</a></div>
                    <?php
                }

                if ($ich->Sitter->Bioladen || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=bioladen&amp;<?= time(); ?>">Bioladen</a></div>
                    <?php
                }
                ?>

                <br/>
                <div class="NaviLinkHeader">Finanzen:</div>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=buero&amp;<?= time(); ?>">Büro</a>
                </div>
                <?php
                if ($ich->Sitter->Bank || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=bank&amp;<?= time(); ?>">Bank</a></div>
                    <?php
                }

                if ($ich->Sitter->Vertraege || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=vertraege_liste&amp;<?= time(); ?>">Verträge
                            (<?= Vertraege(); ?>)</a></div>
                    <?php
                }

                if ($ich->Sitter->Marktplatz || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=marktplatz_liste&amp;<?= time(); ?>">Marktplatz
                            (<?= AngeboteMarkt(); ?>)</a></div>
                    <?php
                }

                if ($ich->Sitter->Mafia || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=mafia&amp;<?= time(); ?>">Mafia</a></div>
                    <?php
                }
                ?>

                <br/>
                <div class="NaviLinkHeader">Persönlich:</div>
                <?php
                if ($ich->Sitter->Gruppe || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=gruppe&amp;<?= time(); ?>">Gruppe
                            (<?= NeueGruppenNachrichten($ich); ?>)</a></div>
                    <?php
                }

                if ($ich->Sitter->Nachrichten || !$_SESSION['blm_sitter']) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);"><a
                                href="./?p=nachrichten_liste&amp;<?= time(); ?>">Nachrichten
                            (<?= NeueNachrichten(); ?>)</a></div>
                    <?php
                }
                ?>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=notizblock&amp;<?= time(); ?>">Notizblock</a></div>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=einstellungen&amp;<?= time(); ?>">Einstellungen</a></div>
                <div class="NaviLink" onclick="return ChefboxZeigen(this.getElementsByTagName('a')[0].href);"><a
                            href="popups/chefbox.php?<?= time(); ?>"
                            onclick="return ChefboxZeigen(this.href);" target="_blank">Chefbox</a></div>

                <br/>
                <div class="NaviLinkHeader">Allgemein:</div>
                <?php
                if (istAdmin()) {
                    ?>
                    <div class="NaviLink" onclick="Navigation(this);" style="margin-bottom: 9px;"><a
                                href="./?p=admin&amp;<?= time(); ?>">Admin-Bereich</a></div>

                    <?php
                }
                ?>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=rangliste&amp;o=<?= $ich->RanglisteOffset; ?>&amp;highlight=<?= $_SESSION['blm_user']; ?>&amp;<?= time(); ?>">Rangliste</a>
                </div>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=statistik&amp;<?= time(); ?>">Serverstatistik</a></div>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=regeln&amp;<?= time(); ?>">Regeln</a>
                </div>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=hilfe&amp;<?= time(); ?>">Hilfe</a>
                </div>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=changelog&amp;<?= time(); ?>">Changelog</a></div>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./?p=impressum&amp;<?= time(); ?>">Impressum</a></div>
                <div class="NaviLink" onclick="Navigation(this);"><a
                            href="./actions/logout.php?p=dummy&amp;<?= time(); ?>">Abmelden</a></div>
                <?php
            }    // Der letzte Block war die Navigationsleiste für angemeldete Benutzer
            else {        // Der Benutzer ist nicht angemeldet
                ?>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=index">Startseite</a></div>
                <div class="NaviLink" style="margin-top: 10px;" onclick="Navigation(this);"><a href="./?p=anmelden">Anmelden</a>
                </div>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=registrieren">Registrieren</a></div>
                <div class="NaviLink" style="margin-top: 10px;" onclick="Navigation(this);"><a
                            href="./?p=regeln">Regeln</a>
                </div>
                <div class="NaviLink" onclick="Navigation(this);"><a href="./?p=impressum">Impressum</a></div>
                <?php
            }        // Der letzte Block war die Navigationsleiste für nicht angemeldete Benutzer
            ?>
        </div>
        <div class="NaviUnten">&nbsp;</div>
        <?php
        if (IstAngemeldet()) {        // Folgenden Block nur anzeigen, wenn der Benutzer angemeldet ist (Statistiken)
            ?>
            <div class="NaviOben">&nbsp;</div>
            <div class="NaviMitte">
                <div class="NaviHeader">Statistiken</div>
                <table class="UserStatistik" cellspacing="0">
                    <tr>
                        <td>Benutzer ID:</td>
                        <td style="text-align: right"><?= $_SESSION['blm_user']; ?></td>
                    </tr>
                    <tr>
                        <td>Bargeld:</td>
                        <td style="text-align: right"><?= number_format($ich->Geld, 2, ',', '.') . ' ' . $Currency; ?></td>
                    </tr>
                    <tr>
                        <td>Bankkonto:</td>
                        <td style="text-align: right"><?= number_format($ich->Bank, 2, ',', '.') . ' ' . $Currency; ?></td>
                    </tr>
                    <tr>
                        <td>Grundeinkommen:</td>
                        <td style="text-align: right"><?= number_format($Einkommen, 2, ',', '.') . ' ' . $Currency; ?></td>
                    </tr>
                    <tr>
                        <td>Letztes Einkommen:</td>
                        <td style="text-align: right"><?= date("H:i:s", $LetztesEinkommen); ?></td>
                    </tr>
                    <tr>
                        <td>Serverzeit:</td>
                        <td style="text-align: right"><?= date("H:i:s", time()); ?></td>
                    </tr>
                    <tr>
                        <td>Nächstes Einkommen:</td>
                        <td style="text-align: right"><?= date("H:i:s", $LetztesEinkommen + EINKOMMEN_DAUER); ?></td>
                    </tr>
                </table>
            </div>
            <div class="NaviUnten" <?php
            if (SPECIAL_STYLE && istAngemeldet()) {
                echo 'style="height: 105px; background-image: url(\'./pics/style/navi_unten_s.gif\');"';
            }
            ?>>&nbsp;
            </div>
            <?php
        }        // Der letzte Block wurde nur bei angemeldeten Benutzern angezeigt.
        ?>
    </div>
</div>
</body>
</html>
<?php
if (SPECIAL_RUNNING && istAngemeldet()) {
    ?>
    <script type="text/javascript" src="js/special.js.php?<?= VERSION; ?>"></script>
    <?php
}
?>
<?php
if ($_SESSION['blm_user']) {
    UpdateLastAction();        // Die letzte Aktion in der Datenbank eintragen (quasi jetzt beim Seitenaufruf ;)
}
DisconnectDB();        // Finally; die Verbindug mit der DB kappen :)

$dauer = 1000 * (time() + microtime(true) - $start);        // Wie lange haben wir gebraucht, um die Seite zu generieren?

$queries = intval($_SESSION['blm_queries']);        // Wieviele Queries haben wir gebraucht?
$_SESSION['blm_queries'] = 0;        // Die Anzahl der Queries wieder auf 0 setzen

echo '<!-- 
	PLT:     ' . number_format($dauer, 4) . ' ms
	Queries: ' . $queries . '
-->';        // Und eine entsprechende Statistik als Kommentar zurückgeben
