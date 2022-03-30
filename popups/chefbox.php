<?php
/**
 * Die Chefbox bietet viele Informationen zum Spiel auf einen Blick an
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
header('Content-type: text/html; charset="utf-8"');        // Das Dokument ist UTF-8 kodiert...

/*
    Einbinden aller wichtigen Datein, wie die Einstellungen, den Zeitpunkt des letzten Resets und die Funktionen
*/
include("../include/config.inc.php");
include("../include/functions.inc.php");
include("../include/database.class.php");

ConnectDB();        // Verbindung mit der Datenbank aufbauen

if (istAdmin()) {
    // Zeige Fehler / Warnungen nur den Admins
    error_reporting(E_ALL);
    ini_set('display_errors', 'true');
}

ignore_user_abort(true);        // Ignoriert den Abbruch des Benutzers

if (!IstAngemeldet()) {        // Wenn der Benutzer nicht angemeldet ist, dann....
    DisconnectDB();        // ... trennen wir die Verbindung mit der Datenbank, und...
    echo '<script type="text/javascript">self.close();</script>';    // ...schließen das Fenster, da er das ja nicht mehr braucht...
    die();        // ... und brechen dann ganz ab.
}

if (CheckRundenEnde()) {        // Wenn die Runde abgelaufen ist, dann...
    ResetAll(true, $Start);        // ... mache einen Reset
}

$ich = LoadSettings();        // Eigene Einstellugnen laden
CheckAllAuftraege();        // die Aufträge abarbeiten
$ich = LoadSettings();            // Meine Daten nochmals laden, vielleicht hat sich ja was geändert...
$Einkommen = (EINKOMMEN_BASIS + ($ich->Gebaeude3 * EINKOMMEN_BIOLADEN_BONUS) + ($ich->Gebaeude4 * EINKOMMEN_DOENERSTAND_BONUS));        // Das Einkommen berechnen

if ($ich->LastAction + TIMEOUT_INAKTIV < time()) {
    DisconnectDB();
    session_unset();
    session_destroy();
    echo '<script type="text/javascript">self.close();</script>';    // ...schließen das Fenster, da er das ja nicht mehr braucht...
}
?><!DOCTYPE html>
<!--
	Site generated:   <?= date("r", time()) . "\n"; ?>
	Client:           <?= sichere_ausgabe($_SERVER['REMOTE_ADDR']) . "\n"; ?>
	Server:           <?= sichere_ausgabe($_SERVER['SERVER_ADDR']) . "\n"; ?>
	Script:           <?= sichere_ausgabe($_SERVER['PHP_SELF']) . "\n"; ?>
	Query-String:     <?= sichere_ausgabe($_SERVER['QUERY_STRING']) . "\n"; ?>
	User-Agent:       <?= sichere_ausgabe($_SERVER['HTTP_USER_AGENT']) . "\n"; ?>
	Referer:          <?= sichere_ausgabe($_SERVER['HTTP_REFERER']) . "\n"; ?>
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <link rel="stylesheet" type="text/css" href="../styles/style.css"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="creator" content="Simon Frankenberger"/>
    <meta http-equiv="refresh" content="60; url=./chefbox.php"/>
    <title>BLM2 - Chefbox</title>
    <style type="text/css">
        table.Liste td, table.Liste th {
            font-size: 11px;
        }

        tr.Kategorie1 td {
            font-weight: bold;
            color: #cc0000;
        }

        tr.Kategorie2 td {
            font-weight: bold;
        }

        tr.Kategorie3 td {
            font-weight: bold;
            color: #0000ee;
        }
    </style>
    <script type="text/javascript">
        <!--
        function BLMzeigen(link) {
            // Bringt das Hauptfenster in den Vordergrund, oder macht ein neues Fenster auf, falls das alte geschlossen wurde.
            if (opener) {
                opener.focus();
            } else {
                const blm = window.open(link, 'blm', 'fullscreen=yes,location=yes,resizable=yes,menubar=yes,scrollbars=yes,status=yes,toolbar=yes');
                blm.focus();
            }
        }

        function BLMEnde() {
            // Schließt das Popup, und loggt den User aus, falls das Hauptfenster wschon zu ist.
            if (opener) {
                opener.focus();
                self.close();
            } else {
                document.location.href = "../actions/logout.php?popup=true";
            }
        }

        function BLMNavigation(link) {
            // Lädt im Hauptfenster eine andere Seite oder macht ein neues Fenster mit der Hauptseite auf, falls noch nicht vorhanden.
            if (opener) {
                opener.document.location.href = link;
                opener.focus();
            } else {
                BLMzeigen(link);
            }
        }

        // -->
    </script>
</head>
<body>
<div style="text-align: center;">
    <h1 style="margin: -8px auto 0;width: 200px; height: 35px; background-image: url('../pics/style/bg_chefbox_h1.png');">
        BLM 2</h1>
    <h3 style="margin: 5px 5px 20px;text-decoration: underline;">Chefbox
        <a href="../?p=hilfe&amp;mod=1&amp;cat=16"
           onclick="BLMNavigation(this.href); return false;"> <img src="../pics/help.gif" alt="Hilfe"
                                                                   style="border: none;"/></a>
    </h3>
</div>

<table class="Liste" cellspacing="0">
    <tr>
        <th>Auftrag</th>
        <th>Restzeit</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
    *
FROM
    auftrag
WHERE
    Von='" . $_SESSION['blm_user'] . "'
AND
    Start+Dauer>" . time() . "
ORDER BY
    (Start+Dauer) ASC;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Alle Aufträge abrufen
    $_SESSION['blm_queries']++;

    $eintrag = false;        // Bisher haben wir noch keinen Auftrag gefunden...

    while ($auftrag = mysql_fetch_object($sql_ergebnis)) {        // Holt sich nun die Auftragsliste
        echo '<tr class="Kategorie' . intval($auftrag->Was / 100) . '">
									<td>' . AuftragText($auftrag->Was) . '</td>
									<td style="font-weight: normal; text-align: right; padding-right: 3px;">' . (date("d", $auftrag->Start + $auftrag->Dauer - 3600 - time()) - 1) . ' T <span id="a_' . $auftrag->ID . '">' . date("H:i:s", $auftrag->Start + $auftrag->Dauer - 3600 - time()) . '</span> h</td>
								</tr>';    // gibt die Infos zum Auftrag aus.

        $eintrag = true;        // ja, wir haben einen Auftrag

        $js_dauer_rechnen[] = "'a_" . $auftrag->ID . "'";        // enthält das Array für das JavaScript, welches die Zeit runterzählt.
    }


    if (!$eintrag)    // Wenn kein Auftrag gefunden wurde, dann gib ne entsprechende Meldung aus.
        echo '<tr><td colspan="2"><i>Keine aktiven Aufträge gefunden!</td></tr>';
    ?>
</table>
<table class="Liste" style="margin-top: 15px;">
    <tr>
        <th>Kategorie</th>
        <th>Dauer / Wert</th>
    </tr>
    <tr>
        <td style="font-weight: bold;">Nächstes Einkommen:</td>
        <td id="a_000"><?php
            if ($LetztesEinkommen + EINKOMMEN_DAUER - time() < 0) {    // Wann bekommt der User sein nächstes Einkommen?
                echo date("H:i:s", $LetztesEinkommen + EINKOMMEN_DAUER - time() - date_offset_get(new DateTime()));
            } else {
                echo '00:00:00';
            }
            ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Nächste Zinsen:</td>
        <td id="a_001"><?php
            if ($LetztesEinkommen + EINKOMMEN_DAUER - time() < 0) {        // Wie lange dauert es noch bis zu den nächsten Zinsen?
                echo date("H:i:s", $LetztesEinkommen + EINKOMMEN_DAUER - time() - date_offset_get(new DateTime()));
            } else {
                echo '00:00:00';
            }
            ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Nächste Mafia:</td>
        <td id="a_002"><?php
            if ($ich->LastMafia + MAFIA_SPERRZEIT_SPIONAGE - time() > 0) {        // Wann kann die nächste Mafiaaktion ausgeführt werden?
                echo date("H:i:s", $ich->LastMafia + MAFIA_SPERRZEIT_SPIONAGE - time() - date_offset_get(new DateTime()));
            } else {
                echo '00:00:00';
            }
            ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Logout wegen Inaktivität:</td>
        <td id="a_003"><?php
            echo date("H:i:s", $ich->LastAction + TIMEOUT_INAKTIV - time());
            ?></td>
    </tr>
</table>
<table style="margin-top: 15px; width: 100%;" cellspacing="0">
    <tr>
        <td><a style="font-weight: normal;" href="../?p=nachrichten_liste"
               onclick="BLMNavigation(this.href); return false;">Neue Nachrichten:</a></td>
        <td><?= NeueNachrichten(); ?></td>
    </tr>
    <tr>
        <td><a style="font-weight: normal;" href="../?p=vertraege_liste"
               onclick="BLMNavigation(this.href); return false;">Neue Verträge:</a></td>
        <td><?= Vertraege(); ?></td>
    </tr>
    <tr>
        <td><a style="font-weight: normal;" href="../?p=marktplatz_liste"
               onclick="BLMNavigation(this.href); return false;">Marktangebote:</a></td>
        <td><?= Database::getInstance()->getMarktplatzCount(); ?></td>
    </tr>
    <tr>
        <td><a style="font-weight: normal;" href="../?p=rangliste"
               onclick="BLMNavigation(this.href); return false;">Spieler online:</a></td>
        <td><?= SpielerOnline(); ?></td>
    </tr>
    <tr>
        <td><a style="font-weight: normal;" href="../?p=bank"
               onclick="BLMNavigation(this.href); return false;">Bargeld:</a></td>
        <td style="white-space: nowrap;"><?= number_format($ich->Geld, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
    <tr>
        <td><a style="font-weight: normal;" href="../?p=bank"
               onclick="BLMNavigation(this.href); return false;">Bank-Guthaben:</a></td>
        <td style="white-space: nowrap;"><?= number_format($ich->Bank, 2, ",", ".") . " " . $Currency; ?></td>
    </tr>
</table>
<div style="text-align: center; margin-top: 20px;">
    <a href="../?p=startseite" onclick="BLMzeigen(this.href); return false;">
        BLM anzeigen / öffnen
    </a><br/>
    <a href="../?p=startseite" onclick="BLMEnde(); return false;">
        Fenster schliessen
    </a>
</div>
<script type="text/javascript">
    <!--
    /*
        Diese Funktion steht hier unten, weil ich das Array mit den Zeiten erst habe, wenn die Aufträge ausgegeben sind...
        Ansonsten würde ich sie in den HERAD schreiben...
    */
    <?php
    $js_dauer_rechnen[] = "'a_000'";        // Die 4 Felder mit den Zeiten für das Einkommen,
    $js_dauer_rechnen[] = "'a_001'";        // nächsten Zinsen, Verfügbarkeit der Mafia
    $js_dauer_rechnen[] = "'a_002'";        // und Zeit bis zum Logout wegen Inaktivität
    $js_dauer_rechnen[] = "'a_003'";        // sollen auch automatisch runterzählen
    ?>
    function DauerRechnen() {		// Rechnet alle Aufträge die Zeit jede Sekunde runter
        const Felder = new Array(<?=implode(", ", $js_dauer_rechnen); ?>);		// Hier stehen die IDs aller Aufträge

        let ZeitAlt = "";		// Enthält den String der alten Zeit

        let Stunden = 0;		//
        let Minuten = 0;		// Enthalten die Zeit aufgeteilt in Stunden, Minuten und Sekunden
        let Sekunden = 0;		//

        let ZeitNeu = "";

        for (let i = 0; i < Felder.length; i++) {		// geht alle Aufträge durch
            const Feld = document.getElementById(Felder[i]);		// Zeiger auf das aktuelle Feld
            ZeitAlt = Feld.innerHTML;		// Holt sich die alte Zeit direkt aus dem HTML-Code

            Stunden = ZeitAlt.split(":")[0];		// Dann werden die Teile des Array aufgeteilt
            Minuten = ZeitAlt.split(":")[1];		// in Stunden, Minuten und
            Sekunden = ZeitAlt.split(":")[2];		// Sekunden zum Berechnen

            Sekunden = Sekunden - 1;										// Die Funktion ruft sich jede Sekudne rekursiv selbst auf, als ist seit dem letzten Aufruf 1 Sekunde vergangen.

            if (Sekunden == -1) {						// Wenn die Sekunden unten überlaufen würden, dann wird
                Minuten = Minuten - 1;									// die Minute um eins gesenkt
                Sekunden = 59;								// und die Sekunden wieder auf 59 gesetzt.
            }

            if (Minuten == -1) {			// Wenn die Minuten unten überlaufen, dann
                Stunden = Stunden - 1;					// wird ne Stunde abgezogen
                Minuten = 59					// und die Minuten wieder auf 59 gesetzt.
            }

            if (Stunden == -1) {			// Wenn dann auch noch die Stunden unten überlaufen, dann ist der Auftrag fertig...
                Feld.innerHTML = "00:00:00";		// und wir geben ein leere Dauer aus
            } else		// Wenn der Auftrag jedoch noch nicht fertig ist, dann
            {
                if (Number(Stunden) < 10)
                    ZeitNeu = "0" + Number(Stunden) + ":";
                else
                    ZeitNeu = Number(Stunden) + ":";

                if (Number(Minuten) < 10)
                    ZeitNeu += "0" + Number(Minuten) + ":";
                else
                    ZeitNeu += Number(Minuten) + ":";

                if (Number(Sekunden) < 10)
                    ZeitNeu += "0" + Number(Sekunden);
                else
                    ZeitNeu += Number(Sekunden);

                Feld.innerHTML = ZeitNeu;		// Zum Schluss schreiben wir das ganze wieder zurück
            }
        }

    }

    window.setInterval('DauerRechnen();', 1000);		// Diese Zeile sorgt dafür, dass die Funktion alle 1000 ms (also jede Sekunde) aufgerufen wird.
    -->
</script>
</body>
</html>
<?php
DisconnectDB();        // Verbindung mit der Datenbank trennen, brauchen wir jetzt nicht mehr :)
