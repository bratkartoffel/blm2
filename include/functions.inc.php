<?php
/**
 * Das "Herz" des Programms, hier stehen alle wichtigen Funktionen des Programms.
 *
 * @version 1.0.3
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */
/*
Changelog:

[1.0.3]
    Registrieren()		Serverpfad durch Konstante ersetzt

*/
session_start();        // Die Sitzung beim Einbinden der Datei sofort starten, werden immer benötigt.

/**
 * @ignore
 **/
@include 'geshi/geshi.php';

/**
 * Hilfsfunktion: Liefert die Anzahl der Angebote auf dem Markt zurück
 *
 * @param string $filter
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function AngeboteMarkt($filter = 1)
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	marktplatz
WHERE
	" . $filter . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $markt = mysql_fetch_object($sql_ergebnis);

    return intval($markt->anzahl);
}

/**
 * Hilfsfunktion: Liefert die Anzahl der angemeldeten Gruppen zurück
 *
 * @return int
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function AnzahlGruppen()
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	gruppe
WHERE
	ID>0;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $gruppen = mysql_fetch_object($sql_ergebnis);

    return intval($gruppen->anzahl);        // Zurück damit :)
}

/**
 * Hilfsfunktion: Liefert die Anzahl der angemeldeten Spieler zurück
 *
 * @return int
 **@version 1.0.1
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function AnzahlSpieler($filter = "%")
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	mitglieder
WHERE
	ID>0
AND
	Name LIKE '" . $filter . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $spieler = mysql_fetch_object($sql_ergebnis);

    return intval($spieler->anzahl);        // Zurück damit :)
}

/**
 * Hilfsfunktion: Liefert anhand der AuftragsBezeichnung (MySQL-Spalte `Was` in der Tabelle `auftraege`) einen Text zurück, was das ist
 *
 * @param int $auftrag_nummer
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function AuftragText($auftrag_nummer)
{
    if (intval($auftrag_nummer) > 100 && intval($auftrag_nummer) < 200) {        // Aufträge zwischen 100 und 200 sind Gebäude
        $zurueck = "G: " . GebaeudeName($auftrag_nummer - 100);
    }

    if (intval($auftrag_nummer) > 200 && intval($auftrag_nummer) < 300) {        // Aufträge zwischen 200 und 300 sind Produktionen
        $zurueck = "A: " . WarenName($auftrag_nummer - 200);
    }

    if (intval($auftrag_nummer) > 300 && intval($auftrag_nummer) < 400) {        // Aufträge zwischen 300 und 400 sind Forschungen
        $zurueck = "F: " . WarenName($auftrag_nummer - 300);
    }

    if (strlen($zurueck) > 14) {                // Wenn der Text zu lange ist
        $zurueck = substr($zurueck, 0, 14) . "...";    // Dann wird er gekürzt und mit 3 Punkten versehen
    }

    return $zurueck;
}

/**
 * Hilfsfunktion: Gibt den Bildnamen einer Ware zurück (im Ordner pics/obst)
 *
 * @param int $waren_id
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function BildVonWare($waren_id)
{
    switch ($waren_id) {
        case 1:
            return "kartoffeln.jpg";
        case 2:
            return "karotten.jpg";
        case 3:
            return "tomaten.jpg";
        case 4:
            return "salat.jpg";
        case 5:
            return "apfel.jpg";
        case 6:
            return "birnen.jpg";
        case 7:
            return "kirschen.jpg";
        case 8:
            return "bananen.jpg";
        case 9:
            return "gurken.jpg";
        case 10:
            return "trauben.jpg";
        case 11:
            return "tabak.jpg";
        case 12:
            return "ananas.jpg";
        case 13:
            return "erdbeeren.jpg";
        case 14:
            return "orangen.jpg";
        case 15:
            return "kiwi.jpg";
    }
}

/**
 * Kernfunktion. Überprüft ob für einen Benutzer fertige Aufträge da sind, und bearbeitet diese auch gleich
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function CheckAllAuftraege()
{
    $sql_abfrage = "SELECT 
	* 
FROM 
	(
		(mitglieder NATURAL JOIN gebaeude) 
		NATURAL JOIN 
			(forschung NATURAL JOIN lagerhaus)
	)
	NATURAL JOIN
		punkte p;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Holt sich alle Spieler aus der Datenbank, mit allen wichtigen Daten
    $_SESSION['blm_queries']++;

    while ($benutzer = mysql_fetch_object($sql_ergebnis)) {        // Läuft alle Spieler durch
        CheckAuftraege($benutzer);        // Überprüft, ob fertige Aufträge vorliegen, bearbeitet diese
    }
}

/**
 * Kernfunktion. Bearbeitet alle Aufträge, welche beendet sind und reagiert dementsprechend.
 *
 * @param array $benutzer
 *
 * @return void
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function CheckAuftraege($benutzer)
{
    $sql_abfrage = "SELECT
	*
FROM
	auftrag
WHERE
	Von='" . $benutzer->ID . "'
AND
	(Start+Dauer)<=" . time() . ";";
    $sql_ergebnis2 = mysql_query($sql_abfrage);        // Zuerst werden alle Aufträge, welche beendet sind, abgefragt
    $_SESSION['blm_queries']++;

    $aenderung = false;        // Zeigt an, ob Änderungen vorgenommen wurden und die Tabellen aktualisiert werden müssen

    while ($auftrag = mysql_fetch_object($sql_ergebnis2))    // Solange Aufträge vorliegen, wird gearbeitet...
    {
        switch (intval($auftrag->Was / 100))        // Handle je nach der Auftragsnummer
            /*
                Ergänzung:
                    1:		Gebäude
                    2:		Produktion
                    3:		Forschung
            */ {
            case 1:
                $temp = "Gebaeude" . ($auftrag->Was - 100);        // Welche Spalte in der DB ist betroffen?
                $benutzer->$temp++;        // Das entsprechende Gebäude um ein Level hochsetzen
                $benutzer->Punkte += $auftrag->Punkte;
                $benutzer->GebaeudePlus += $auftrag->Punkte;

                $aenderung = true;
                break;
            case 2:
                $temp = "Lager" . ($auftrag->Was - 200);        // Welche Spalte in der DB ist betroffen?
                $benutzer->$temp += $auftrag->Menge;        // Die im Auftrag festgelegte Menge in das Lager schreiben
                $benutzer->Punkte += $auftrag->Punkte;
                $benutzer->ProduktionPlus += $auftrag->Punkte;

                $aenderung = true;
                break;
            case 3:
                $temp = "Forschung" . ($auftrag->Was - 300);        // Welche Spalte in der DB ist betroffen?
                $benutzer->$temp++;        // Die entsprechende Forschung um 1 hochsetzen
                $benutzer->Punkte += $auftrag->Punkte;
                $benutzer->ForschungPlus += $auftrag->Punkte;

                $aenderung = true;
                break;
        }
    }

    if ($aenderung)        // Nur wenn eine Änderung vorgenommen wurde, dann soll das ganze zurückgeschrieben werden.
    {
        $sql_abfrage = "UPDATE lagerhaus SET ";        // Das ist der Anfangstext der SQL-Abfrage

        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
            $temp = "Lager" . $i;
            $sql_abfrage .= $temp . "='" . $benutzer->$temp . "', ";        // Setzt die SQL-Abfrage Stück für Stück zusammen
        }

        $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer->ID . "';";            // zum Schluss muss noch das letzte Komma der Abfrage gelöscht werden und die WHERE-Klausel hinzugefügt werden.
        mysql_query($sql_abfrage);        // Dann die Query abschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE forschung SET ";        // Das ist der Anfangstext der SQL-Abfrage

        for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
            $temp = "Forschung" . $i;
            $sql_abfrage .= $temp . "='" . $benutzer->$temp . "', ";        // Setzt die SQL-Abfrage Stück für Stück zusammen
        }

        $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer->ID . "';";            // zum Schluss muss noch das letzte Komma der Abfrage gelöscht werden und die WHERE-Klausel hinzugefügt werden.
        mysql_query($sql_abfrage);        // Dann die Query abschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE gebaeude SET ";        // Das ist der Anfangstext der SQL-Abfrage

        for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
            $temp = "Gebaeude" . $i;
            $sql_abfrage .= $temp . "='" . $benutzer->$temp . "', ";        // Setzt die SQL-Abfrage Stück für Stück zusammen
        }

        $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer->ID . "';";            // zum Schluss muss noch das letzte Komma der Abfrage gelöscht werden und die WHERE-Klausel hinzugefügt werden.
        mysql_query($sql_abfrage);        // Dann die Query abschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN punkte p
SET
    m.Punkte=" . $benutzer->Punkte . ",
    p.GebaeudePlus=" . $benutzer->GebaeudePlus . ",
    p.ForschungPlus=" . $benutzer->ForschungPlus . ",
    p.ProduktionPlus=" . $benutzer->ProduktionPlus . "
WHERE
    m.ID='" . $benutzer->ID . "';";
        mysql_query($sql_abfrage);        // Dann die Query abschicken
        $_SESSION['blm_queries']++;

        $sql_abfrage = "DELETE FROM
    auftrag
WHERE
    Von='" . $benutzer->ID . "'
AND
    (Start+Dauer)<=" . time() . ";";
        mysql_query($sql_abfrage);        // Dann die Query abschicken
        $_SESSION['blm_queries']++;
    }
}

/**
 * Hilfsfunktion: Prüft, ob eine angegebene Mailadresse von der Syntax her gültig ist
 *
 * @param string $email
 *
 * @return boolean
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.1
 *
 */
function CheckEMail($email)
{
    return eregi("^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}\$", $email);
}

/**
 * Hilfsfunktion: Schaut, ob das Spiel gesperrt ist
 *
 * @return boolean
 **@version 1.0.1
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function CheckGameLock()
{
    return (LAST_RESET >= time());
}

/**
 * Hilfsfunktion: Liefert den Fehlertext zu einer Fehlernummer zurück
 *
 * @param int $meldung
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.1
 *
 */
function CheckMessage($meldung)
{
    if (intval($meldung) == 0) {            // Naja, Meldung 0 ist keine Meldung, also abbrechen
        return "";
    }

    if ($meldung > 100 && $meldung < 200)        // Fehlermeldung
        $zurueck[] = '<table id="m_' . $meldung . '" class="Meldung" border="0" cellspacing="0">
					<tr>
						<td style="width: 40px;">
							<img src="pics/small/error.png" alt="Fehler" style="margin: 4px;" />
						</td>
						<td>
							<span class="MeldungR">
								';        // HEADER für die Fehlermeldung

    if ($meldung > 200 && $meldung < 300)        // Hinweis
        $zurueck[] = '<table id="m_' . $meldung . '"  class="Meldung" border="0" cellspacing="0">
					<tr>
						<td style="width: 40px;">
							<img src="pics/small/ok.png" alt="Hinweis" style="margin: 4px;" />
						</td>
						<td>
							<span class="MeldungG">
								';        // HEADER für die Fehlermeldung

    if ($meldung <= 100 || $meldung == 200 || $meldung >= 300)        // Fehlermeldung
        $zurueck[] = '<table id="m_' . $meldung . '"  class="Meldung" border="0" cellspacing="0">
					<tr>
						<td style="width: 40px;">
							<img src="pics/small/error.png" alt="Fehler" style="margin: 4px;" />
						</td>
						<td>
							<span class="MeldungR">
								';        // HEADER für die Fehlermeldung

    switch ($meldung)        // Überprüft die Fehlernummer
    {
        case 101:
            $zurueck[] = 'Seite konnte nicht gefunden werden!';
            break;
        case 102:
            $zurueck[] = 'Sie sind nicht angemeldet. Bitte melden Sie sich erst an.';
            break;
        case 103:
            $zurueck[] = 'Das Bild ist zu gro&szlig;. Die maximale Gr&ouml;&szlig;e des Bildes ist 64 KB.';
            break;
        case 104:
            $zurueck[] = 'Bitte f&uuml;llen Sie alle Felder aus.';
            break;
        case 105:
            $zurueck[] = 'Bitte geben Sie Ihr gew&uuml;nschtes Passwort 2x ein, um Tipfehler zu vermeiden.';
            break;
        case 106:
            $zurueck[] = 'Der Benutzername ist bereits vergeben. Bitte w&auml;hlen Sie einen anderen.';
            break;
        case 107:
            $zurueck[] = 'Die hochgeladene Datei ist kein Bild vom Typ jpg, gif oder png!';
            break;
        case 108:
            $zurueck[] = 'Unbekannter Benutzername und / oder falsches Passwort!';
            break;
        case 109:
            $zurueck[] = 'Sie haben Ihr Kreditlimit schon erreicht oder ein zu gro&szlig;er Betrag wurde ausgew&auml;hlt!';
            break;
        case 110:
            $zurueck[] = 'Ung&uuml;ltiger Betrag!';
            break;
        case 111:
            $zurueck[] = 'Sie haben nicht gen&uuml;gend Geld!';
            break;
        case 112:
            $zurueck[] = 'Das darfst du nicht!';
            break;
        case 113:
            $zurueck[] = 'Der Auftrag wurde bereits erteilt!';
            break;
        case 114:
            $zurueck[] = 'Ihr Account wurde soeben resettet, da Sie Ihren Kredit bei der Bank nicht decken konnten und die Bank alles gepf&auml;ndet hat.';
            break;
        case 115:
            $zurueck[] = 'Bitte geben Sie eine g&uuml;ltige Menge ein.';
            break;
        case 116:
            $zurueck[] = 'Sie haben gar nicht so viel Waren auf Lager!';
            break;
        case 117:
            $zurueck[] = 'Ung&uuml;ltige Angaben oder Angaben nicht vollst&auml;dig.';
            break;
        case 118:
            $zurueck[] = 'Der Benutzer konnte nicht gefunden werden.';
            break;
        case 119:
            $zurueck[] = 'Das Angebot mit der ID konnte nicht gefunden werden. Vermutlich war einer schneller als Sie.';
            break;
        case 120:
            $zurueck[] = 'Bitte geben Sie eine Menge und einen Preis gr&ouml;&szlig;er 1 ein!';
            break;
        case 121:
            $zurueck[] = 'Das alte Kennwort ist nicht korrekt!';
            break;
        case 122:
            $zurueck[] = 'Sie haben keine Waren auf Lager, was wollen Sie da verkaufen?';
            break;
        case 123:
            $zurueck[] = 'Das Spiel ist zur Zeit pausiert. Die neue Runde startet am ' . date("d.m.Y \u\m H:i", LAST_RESET);
            break;
        case 124:
            $zurueck[] = 'Die angegebene Nachricht konnte nicht gefunden werden!';
            break;
        case 125:
            $zurueck[] = 'Ungültige Menge eingegeben!';
            break;
        case 126:
            $zurueck[] = 'Es exisitiert bereits eine Gruppe mit diesem Namen oder K&uuml;rzel!';
            break;
        case 127:
            $zurueck[] = 'Entweder existiert die eingegebene Gruppe nicht, oder das eingegebene Passwort ist falsch!';
            break;
        case 128:
            $zurueck[] = 'Bitte geben Sie eine Nachricht ein!';
            break;
        case 129:
            $zurueck[] = 'Es besteht bereits eine Beziehung mit dieser Gruppe!';
            break;
        case 130:
            $zurueck[] = 'Der eingegebene Sicherheitscode ist nicht korrekt!';
            break;
        case 131:
            $zurueck[] = 'Der Kontostand des Mitglieds ist bereits auf dem Maximum, die Bank weigert sich die Überweisung anzunehmen!';
            break;
        case 132:
            $zurueck[] = 'Bei einem Krieg muss der Betrag, um welchen gekämpft wird, größer als 100000 ' . $Currency . ' sein!';
            break;
        case 133:
            $zurueck[] = "Bitte geben Sie eine Dauer zwischen 1 und 12 Stunden ein!";
            break;
        case 134:
            $zurueck[] = "Bitte geben Sie eine gültige EMail-Adresse ein!";
            break;
        case 135:
            $zurueck[] = "Ihr Account ist noch nicht aktiviert.<br />Bitte klicken Sie auf den Link, den Sie per EMail erhalten haben.<br />Falls Sie keinen Link erhalten haben, so wenden Sie sich bitte mit Ihrem Benutzernamen und <br />der registrierten EMailadresse als Absender an die im Impressum angegebene Adresse.";
            break;
        case 136:
            $zurueck[] = "Bitte geben Sie ein Sitterpasswort ein!";
            break;
        case 137:
            $zurueck[] = "Es ist Weihnachten. Die Mafiabosse haben untereinander bis zum Ende der Feiertage einen Waffenstillstand geschlossen.";
            break;
        case 138:
            $zurueck[] = "Sitter dürfen beim Spiel nicht teilnehmen, tut mir Leid...";
            break;
        case 139:
            $zurueck[] = "Ihr Account wurde von einem Administrator gesperrt. Bitte kontaktieren Sie einen Administrator im Forum für weitere Informationen. Falls diese Sperre dauerhaft ist, dann wird Ihr Account zwei Wochen nach Beginn der Sperre gelöscht.";
            break;
        case 140:
            $zurueck[] = "Die Gruppe wurde gefunden und das Passwort ist korrekt, jedoch hat die Gruppe die maximale Mitgliederzahl schon erreicht.";
            break;


        case 201:
            $zurueck[] = 'Der neue Benutzer wurde erfolgreich erstellt. Sobald Sie Ihre EMail-Adresse bestätigt haben, können Sie sich einloggen.';
            break;
        case 202:
            $zurueck[] = 'Sie haben sich erfolgreich angemeldet.';
            if ($_SESSION['blm_sitter']) {
                $zurueck[] = ' (Sitterzugang)';
            }
            break;
        case 203:
            $zurueck[] = 'Sie haben sich erfolgreich abgemeldet.';
            break;
        case 204:
            $zurueck[] = 'Nachricht wurde gesendet.';
            break;
        case 205:
            $zurueck[] = 'Ihr Account wurde gel&ouml;scht. Ich hoffe, das Spiel hat Ihnen gefallen!';
            break;
        case 206:
            $zurueck[] = 'Die Beschreibung wurde gespeichert.';
            break;
        case 207:
            $zurueck[] = 'Der Auftrag wurde erteilt.';
            break;
        case 208:
            $zurueck[] = 'Die Waren wurden verkauft.';
            break;
        case 209:
            $zurueck[] = 'Das Bild wurde gel&ouml;scht.';
            break;
        case 210:
            $zurueck[] = 'Das Bild wurde erfolgreich hochgeladen.';
            break;
        case 211:
            $zurueck[] = 'Die Nachricht wurde gel&ouml;scht.';
            break;
        case 212:
            $zurueck[] = 'Alle Nachrichten wurden gel&ouml;scht.';
            break;
        case 213:
            $zurueck[] = 'Der Notizblock wurde gespeichert.';
            break;
        case 214:
            $zurueck[] = 'Der Vertrag wurde versandt.';
            break;
        case 215:
            $zurueck[] = 'Der Vertrag wurde angenommen. Sie finden die Waren in Ihrem Lager.';
            break;
        case 216:
            $zurueck[] = 'Der Vertrag wurde abgelehnt.';
            break;
        case 217:
            $zurueck[] = 'Das Angebot wurde gekauft.';
            break;
        case 218:
            $zurueck[] = 'Das Angebot wurde eingestellt.';
            break;
        case 219:
            $zurueck[] = 'Das Passwort wurde erfolgreich ge&auml;ndert.';
            break;
        case 220:
            $zurueck[] = 'Der Account wurde wieder auf Standardeinstellungen zur&uuml;ckgesetzt.';
            break;
        case 221:
            $zurueck[] = 'Das Angebot wurde zur&uuml;ckgezogen.';
            break;
        case 222:
            $zurueck[] = 'Der Auftrag wurde gel&ouml;scht.';
            break;
        case 223:
            $zurueck[] = 'Die Gruppe wurde erstellt!';
            break;
        case 224:
            $zurueck[] = 'Sie sind der Gruppe erfolgreich beigetreten!';
            break;
        case 225:
            $zurueck[] = 'Sie haben die Gruppe nun verlassen.';
            break;
        case 226:
            $zurueck[] = 'Die Rechte wurden gespeichert.';
            break;
        case 227:
            $zurueck[] = 'Das Mitglied wurde aus der Gruppe verwiesen.';
            break;
        case 228:
            $zurueck[] = 'Die Gruppe wurde gel&ouml;scht!';
            break;
        case 229:
            $zurueck[] = 'Die Beziehung wurde eingetragen!';
            break;
        case 230:
            $zurueck[] = 'Der Vertrag wurde aufgelöst.';
            break;
        case 231:
            $zurueck[] = 'Der Vertrag wurde angenommen.';
            break;
        case 232:
            $zurueck[] = 'Der Vertrag wurde abgelehnt.';
            break;
        case 233:
            $zurueck[] = 'Das Angebot wurde gelöscht.';
            break;
        case 234:
            $zurueck[] = 'Das Angebot wurde bearbeitet.';
            break;
        case 235:
            $zurueck[] = 'Das Geld wurde in die Gruppenkasse überwiesen.';
            break;
        case 236:
            $zurueck[] = 'Das Geld wurde an das Mitglied überwiesen.';
            break;
        case 237:
            $zurueck[] = 'Der Krieg wurde beendet. Leider war kein Sieg mehr in Aussicht.';
            break;
        case 238:
            $zurueck[] = 'Die EMail-Adresse wurde geändert.';
            break;
        case 239:
            $zurueck[] = 'Der Sitterzugang wurde gelöscht.';
            break;
        case 240:
            $zurueck[] = 'Der Sitterzugang wurde erfolgreich bearbeitet.';
            break;


        case 999:
            $zurueck[] = 'Das Spiel ist zur Zeit pausiert.<br />Die neue Runde startet am ' . date("d.m.Y \u\m H:i", LAST_RESET);
            break;
        default:
            $zurueck[] = 'Meldungsnummer konnte nicht gefunden werden: &quot;' . $meldung . '&quot;';
            break;
    }

    $zurueck[] = '
							</span>
						</td>
						<td style="vertical-align: top; text-align :right;">
							<a href="#">
								<img src="./pics/small/error.png" style="margin: 0; padding: 0; border: none;" alt="Fenster schlie&szlig;en" onclick="MeldungAusblenden(\'m_' . $meldung . '\'); return false;" />
							</a>
						</td>
					</tr>
				</table>
				';        // FOOTER für die Fehlermeldung

    return implode($zurueck);        // Den String mit der Fehlermeldung zurückgeben...
}

/**
 * Hilfsfunktion: Schaut, ob die Runde schon zu Ende ist
 *
 * @return boolean
 **@version 1.0.1
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function CheckRundenEnde()
{
    return (LAST_RESET + RUNDEN_DAUER <= time());
}

/**
 * Kernfunktion: Baut die Verbindung mit der Datenbank auf
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function ConnectDB()
{
    $_SESSION['blm_link'] = mysql_connect(DB_SERVER, DB_BENUTZER, DB_PASSWORT);        // Stellt die Verbindung her
    if (mysql_errno() > 0) {
        die("<h2>Die Verbindung mit dem Datenbankserver konnte nicht hergestellt werden. Wahrscheinlich handelt es sich nur um ein vorrübergehendes Problem. Bitte versuchen Sie es später nochmal.</h2>");
    }

    mysql_select_db(DB_DATENBANK);        // Wählt die Datenbank aus
    if (mysql_errno() > 0) {
        die("<h2>Die VDatenbank konnte nicht ausgewählt werden. Wahrscheinlich handelt es sich nur um ein vorrübergehendes Problem. Bitte versuchen Sie es später nochmal.</h2>");
    }

    $sql_abfrage = "SET CHARACTER SET utf8;";            //
    $sql_ergebnis = mysql_query($sql_abfrage);        // Wählt als Übertragungskodierung UTF-8
    $_SESSION['blm_queries']++;

    $sql_abfrage = "SET NAMES utf8;";                            // Alle Daten von und in die Datenbank haben diese.
    $sql_ergebnis = mysql_query($sql_abfrage);        //
    $_SESSION['blm_queries']++;

    return true;
}

/**
 * Hilfsfunktion: Löscht einen Account
 *
 * @param int $benutzer_id
 *
 * @return void
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.1
 *
 */
function DeleteAccount($benutzer_id)
{
    $sql_abfrage = "DELETE FROM
	mitglieder
WHERE
	ID='" . intval($benutzer_id) . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;
}

/**
 * Kernfunktion: Trennt die Verbindung mit der Datenbank wieder
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function DisconnectDB()
{
    mysql_close($_SESSION['blm_link']);        // Verbindung trennen
    unset($_SESSION['blm_link']);                    // SESSION-Variable löschen, ist jetzt ungültig

    return true;
}

/**
 * Hilfsfunktion: Liefert die Bezeichnung eines Gebäudes anhand einer ID zurück
 *
 * @param int $gebaeude_id
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GebaeudeName($gebaeude_id)
{
    switch (intval($gebaeude_id)) {
        case 1:
            return "Plantage";
        case 2:
            return "Forschungszentrum";
        case 3:
            return "Bioladen";
        case 4:
            return "Dönerstand";
        case 5:
            return "Bauhof";
        case 6:
            return "Verkäuferschule";
        case 7:
            return "Zaun";
        case 8:
            return "Pizzeria";
        default:        // Unbekannte waren_id
            return "<i>Unbekannt</i>";
    }
}

/**
 * Hilfsfunktion: Liefert den Namen einer Gruppe mit einer bestimmten ID zurück
 *
 * @param int $gruppe_id
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetGroupName($gruppe_id)
{
    $sql_abfrage = "SELECT
	Name
FROM
	gruppe
WHERE
	ID=" . intval($gruppe_id) . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $name = mysql_fetch_object($sql_ergebnis);

    if ($name->Name == "")        // Wenn der Name nicht gefunden werden konnte, dann sag einfach, es war das System :)
        $name->Name = "-System-";

    return $name->Name;        // Den Namen zurückgeben.
}

/**
 * Hilfsfunktion: Liefert den den Platz eines Spielers mit einer bestimmten ID zurück (fürs Profil)
 *
 * @param int $benutzer_id
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetPlatz($benutzer_id)
{
    $sql_abfrage = "SELECT
	ID
FROM
	mitglieder
WHERE
	ID>0
ORDER BY
	Punkte DESC,
	Name ASC;";
    $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft alle Spieler ab, nach Punkten sortiert
    $_SESSION['blm_queries']++;

    $platz = 0;

    while ($benutzer = mysql_fetch_object($sql_ergebnis)) {
        $platz++;
        if ($benutzer->ID == intval($benutzer_id)) {        // Ruft alle Spieler ab, und schaut ob die aktuelle ID die des gesuchten Spielers ist
            break;
        }
    }

    return ($platz);        // Gibt den Platz zurück :)
}

/**
 * Hilfsfunktion: Liefert den Namen eines Spielers anhand seiner Position in der Rangliste ab
 *
 * @param int $platz
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetSpielerNachPlatz($platz)
{
    $sql_abfrage = "SELECT
	Name AS n,
	Punkte AS p
FROM
	mitglieder
WHERE
	ID>0
ORDER BY
	Punkte DESC,
	Name ASC
LIMIT " . (intval($platz) - 1) . ", 1;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $benutzer = mysql_fetch_object($sql_ergebnis);

    return stripslashes($benutzer->n);        // Name geht ungefiltert, nur ohne Backticks raus!
}

/**
 * Hilfsfunktion: Liefert die Punkte eines Spielers zurück (fürs Profil)
 *
 * @param int $benutzer_id
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetSpielerPunkte($benutzer_id)
{
    $sql_abfrage = "SELECT
	Punkte
FROM
	mitglieder
WHERE
	ID='" . $benutzer_id . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $benutzer = mysql_fetch_object($sql_ergebnis);

    return round($benutzer->Punkte);        // Wir haben die Punkte, also zurück damit :)
}

/**
 * Hilfsfunktion: Gibt die ID eines Users anhand seines Namens zurück
 *
 * @param int $benutzer_name
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetUserID($benutzer_name)
{
    $sql_abfrage = "SELECT
	ID
FROM
	mitglieder
WHERE
	Name='" . mysql_real_escape_string($benutzer_name) . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $benutzer = mysql_fetch_object($sql_ergebnis);        // Die ID abholen

    return intval($benutzer->ID);        // Die ID zurückgeben
}

/**
 * Hilfsfunktion: Liefert den Namen eines Benutzers mit einer bestimmten ID zurück
 *
 * @param int $benutzer_id
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function GetUserName($benutzer_id)
{
    $sql_abfrage = "SELECT
	Name
FROM
	mitglieder
WHERE
	ID=" . intval($benutzer_id) . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $name = mysql_fetch_object($sql_ergebnis);

    if ($name->Name == "")        // Wenn der Name nicht gefunden werden konnte, dann sag einfach, es war das System :)
        $name->Name = "-System-";

    return stripslashes($name->Name);        // Den Namen zurückgeben.
}

/**
 * Hilfsfunktion: Überprüft, ob der Spieler ein Administrator ist
 *
 * @param boolean $aus_db
 * @param string $db_username
 *
 * @return boolean
 **@version 1.0.1
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function IstAdmin($aus_db = false, $db_username = "")
{
    if ($aus_db) {
        $sql_abfrage = "SELECT
    Admin
FROM
    mitglieder
WHERE
    Name LIKE '" . mysql_real_escape_string($db_username) . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $temp = mysql_fetch_assoc($sql_ergebnis);

        return (intval($temp["Admin"]) > 0);
    } else {
        return (intval($_SESSION['blm_admin']) > 0);
    }
}

/**
 * Hilfsfunktion: Überprüft, ob der Spieler angemeldet ist
 *
 * @return boolean
 **@version 1.0.1
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function IstAngemeldet()
{
    return ($_SESSION['blm_user'] != "");
}

/**
 * Hilfsfunktion: Überprüft, ob der Spieler ein Betatester ist
 *
 * @param boolean $aus_db
 * @param string $db_username
 *
 * @return boolean
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function IstBetatester($aus_db = false, $db_username = "")
{
    if ($aus_db) {
        $sql_abfrage = "SELECT
    Betatester
FROM
    mitglieder
WHERE
    Name LIKE '" . mysql_real_escape_string($db_username) . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $temp = mysql_fetch_assoc($sql_ergebnis);

        return ($temp["Betatester"] > 0);
    } else {
        return (intval($_SESSION['blm_betatester']) > 0);
    }
}

/**
 * Hilfsfunktion: Macht aus TRUE ein "Ja", aus FALSE ein "Nein"
 *
 * @param boolean $zustand
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function JaNein($zustand)
{
    if (intval($zustand) == 0)
        return "Nein";
    else
        return "Ja";
}

/**
 * Hilfsfunktion: Gibt das Datum des letzten Eintrags im Changelog zurück
 *
 * @return int
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function LetzteAenderung()
{
    $sql_abfrage = "SELECT
	UNIX_TIMESTAMP(Datum) AS Datum
FROM
	changelog
ORDER BY
	Datum DESC
LIMIT 0,1;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $datum = mysql_fetch_object($sql_ergebnis);

    return $datum->Datum;
}

/**
 * Kernfunktion: Lädt alle relevanten Daten des Benutzers
 *
 * @return object
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function LoadSettings()
{
    $sql_abfrage = "SELECT 
	m.*,
	g.*,
	f.*,
	l.*,
	p.*,
	s.*,
	m.ID AS mID
FROM
	(
			mitglieder m 
		NATURAL JOIN 
			gebaeude g
	) 
	NATURAL JOIN 
	(
			forschung f 
		NATURAL JOIN 
			lagerhaus l
	)
	NATURAL JOIN
	(
			punkte p 
		NATURAL JOIN 
			statistik s
	)
WHERE
	m.ID='" . $_SESSION['blm_user'] . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $benutzer = mysql_fetch_object($sql_ergebnis);        // holt sich die Daten

    $sql_abfrage = "SELECT 
	An
FROM
	gruppe_diplomatie
WHERE
	Von='" . $benutzer->Gruppe . "'
AND
	Seit IS NOT NULL
AND
	Typ='3';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    while ($kriege = mysql_fetch_object($sql_ergebnis)) {
        $benutzer->GruppeKriege[] = $kriege->An;
    }

    $sql_abfrage = "SELECT 
	An
FROM
	gruppe_diplomatie
WHERE
	Von='" . $benutzer->Gruppe . "'
AND
	Seit IS NOT NULL
AND
	Typ=2;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    while ($bnd = mysql_fetch_object($sql_ergebnis)) {
        $benutzer->GruppeBND[] = $bnd->An;
    }

    $sql_abfrage = "SELECT 
	An
FROM
	gruppe_diplomatie
WHERE
	Von='" . $benutzer->Gruppe . "'
AND
	Seit IS NOT NULL
AND
	Typ=1;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    while ($nap = mysql_fetch_object($sql_ergebnis)) {
        $benutzer->GruppeNAP[] = $nap->An;
    }

    $benutzer->RanglisteOffset = intval((GetPlatz($benutzer->ID) - 1) / RANGLISTE_OFFSET);

    return $benutzer;        // Daten gefunden und vorhanden, also geben wir diese zurück
}

/**
 * Kernfunktion: Lädt die individuellen Einstellungen eines Users, was der Sitter darf und was nicht
 *
 * @return object
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function LoadSitterSettings()
{
    $sql_abfrage = "SELECT
	*
FROM
	sitter
WHERE
	ID='" . $_SESSION['blm_user'] . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);

    return mysql_fetch_object($sql_ergebnis);
}

/**
 * Hilfsfunktion: Liefert einen passenden Auftragstext für die IGM an das Opfer eines Mafia Angriffs
 *
 * @param int $aktion
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function MafiaAuftragsText($aktion)
{
    switch ($aktion) {
        case 1:
            return 'Wir sollten Sie ausspionieren und unseren Auftragsgeber alles über Sie erzählen...';
            break;
        case 2:
            return 'Wir sollten Ihr Lager ausräumen...';
            break;
        case 3:
            return 'Ihr Konkurrent machte sich richtig Sorgen um Sie, deshalb sollten wir Ihre Plantage ein bisschen modernisieren...';
            break;
        case 4:
            return 'Sie haben doch so viel Geld, da wollten wir Ihre Geldbörse ein bisschen leichter machen...';
            break;
        default:
            return 'Wir sollten Ihnen das Leben zur Hölle machen!';
            break;
    }
}

/**
 * Hilfsfunktion: Sendet eine Nachricht an alle Spieler
 *
 * @param string $betreff
 * @param string $nachricht
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function NachrichtAnAlle($betreff, $nachricht)
{
    $sql_abfrage = "SELECT
	ID
FROM
	mitglieder
WHERE
	ID>0;";
    $sql_ergebnis = mysql_query($sql_abfrage);    // Zuerst mal alle Benutzer abrufen
    $_SESSION['blm_queries']++;

    $sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    An,
    Von,
    Zeit,
    Betreff,
    Nachricht,
    Gelesen
)
VALUES
";

    while ($benutzer = mysql_fetch_object($sql_ergebnis)) {        // Durch die WHILE-Schleife werden alle Benutzer der Reihe nach durchlaufen
        $sql_abfrage .= "
(
	NULL,
	'" . $benutzer->ID . "',
	'0',
	'" . time() . "',
	'RUNDMAIL: " . mysql_real_escape_string($betreff) . "',
	'" . mysql_real_escape_string($nachricht) . "',
	'0'
),";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -1) . ";";
    mysql_query($sql_abfrage);        // Nachricht einfügen
    $_SESSION['blm_queries']++;
}

/**
 * Hilfsfunktion: Gibt die Anzahl der austehenden diplomatischen Anträge zurück
 *
 * @param object $ich
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function NeueGruppenDiplomatie($ich)
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	gruppe_diplomatie
WHERE
	Seit IS NULL
AND
	An=" . $ich->Gruppe . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $anfragen = mysql_fetch_object($sql_ergebnis);        // holt sich das Ergebnis ab

    return intval($anfragen->anzahl);        // Gibt die Anzahl zurück
}

/**
 * Hilfsfunktion: Gibt die Anzahl der neuen Gruppennachrichten für den User zurück
 *
 * @param object $ich
 *
 * @return int
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function NeueGruppenNachrichten($ich)
{
    if ($ich->Gruppe) {
        $sql_abfrage = "SELECT
    COUNT(*) AS anzahl
FROM
    gruppe_nachrichten
WHERE
    Zeit>" . $ich->GruppeLastMessageZeit . "
AND
    Gruppe=" . $ich->Gruppe . "
AND
    Gruppe IS NOT NULL;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $nachrichten = mysql_fetch_object($sql_ergebnis);        // holt sich das Ergebnis ab

        return intval($nachrichten->anzahl);        // Gibt die Anzahl zurück
    } else {
        return 0;
    }
}

/**
 * Hilfsfunktion: Gibt die Anzahl der neuen Nachrichten für den User zurück
 *
 * @return int
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function NeueNachrichten()
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	nachrichten
WHERE
	An='" . $_SESSION['blm_user'] . "'
AND
	Gelesen=0
AND
	ID!='" . intval($_GET['nid']) . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $nachrichten = mysql_fetch_object($sql_ergebnis);        // holt sich das Ergebnis ab

    return intval($nachrichten->anzahl);        // Gibt die Anzahl zurück
}

/**
 * Hilfsfunktion: Schlüsselt die Rechte eines Users in einer Gruppe auf
 *
 * @param int $benutzer
 * @param boolean $rechte_aus_db
 * @param int $rechte_b
 *
 * @return object
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.0
 *
 */
function RechteGruppe($benutzer, $rechte_aus_db = true, $rechte_b = 0)
{
    if ($rechte_aus_db) {
        $sql_abfrage = "SELECT
    GruppeRechte
FROM
    mitglieder
WHERE
    ID='" . intval($benutzer) . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);        // Legt den Benutzer in der Mitgliedertabelle an
        $_SESSION['blm_queries']++;

        $rechte = mysql_fetch_assoc($sql_ergebnis);
        $rechte = intval($rechte["GruppeRechte"]);
    } else {
        $rechte = intval($rechte_b);
    }

    $back->GruppeLoeschen = false;
    $back->MitgliederRechte = false;
    $back->GruppePasswort = false;
    $back->MitgliedKicken = false;
    $back->GruppeBeschreibung = false;
    $back->GruppeBild = false;
    $back->NachrichtLoeschen = false;
    $back->NachrichtSchreiben = false;
    $back->Diplomatie = false;
    $back->Chef = false;
    $back->GruppeKasse = false;

    if ($rechte - 1024 >= 0) { // Gruppenkasse verwalten
        $back->GruppeKasse = true;
        $rechte -= 1024;
    }

    if ($rechte - 512 >= 0) { // Chef :)
        $back->Chef = true;
        $rechte -= 512;
    }

    if ($rechte - 256 >= 0) { // Gruppe löschen
        $back->Diplomatie = true;
        $rechte -= 256;
    }

    if ($rechte - 128 >= 0) { // Gruppe löschen
        $back->GruppeLoeschen = true;
        $rechte -= 128;
    }

    if ($rechte - 64 >= 0) { // Mitgliederrechte ändern
        $back->MitgliederRechte = true;
        $rechte -= 64;
    }

    if ($rechte - 32 >= 0) { // Beitrittspasswort ändern
        $back->GruppePasswort = true;
        $rechte -= 32;
    }

    if ($rechte - 16 >= 0) { // Mitglied kicken
        $back->MitgliedKicken = true;
        $rechte -= 16;
    }

    if ($rechte - 8 >= 0) { // Beschreibung ändern
        $back->GruppeBeschreibung = true;
        $rechte -= 8;
    }

    if ($rechte - 4 >= 0) { // Gruppenbild ändern
        $back->GruppeBild = true;
        $rechte -= 4;
    }

    if ($rechte - 2 >= 0) { // Gruppennachricht löschen
        $back->NachrichtLoeschen = true;
        $rechte -= 2;
    }

    if ($rechte - 1 >= 0) { // Gruppennachricht schreiben
        $back->NachrichtSchreiben = true;
        $rechte -= 1;
    }

    return $back;
}

/**
 * Hilfsfunktion: Legt einen neuen Spieler an
 *
 * @param array $Start
 * @param string $name
 * @param string $passwort
 * @param string $email
 *
 * @return boolean
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function Registrieren($Start, $name, $passwort, $email)
{
    $code = sha1(time() . $name . DB_PASSWORT . $email);

    $sql_abfrage = "INSERT INTO
	mitglieder
(
	ID,
	Name,
	EMail,
	EMailAct,
	Passwort,
	RegistriertAm,
	Geld,
	LastAction,
	LastLogin
)
VALUES
(
	NULL,
	'" . mysql_real_escape_string(trim($name)) . "',
	'" . mysql_real_escape_string(trim($email)) . "',
	'" . $code . "',
	'" . sha1($passwort) . "',
	'" . time() . "',
	'" . $Start["geld"] . "',
	'0',
	'0'
);";
    mysql_query($sql_abfrage);        // Legt den Benutzer in der Mitgliedertabelle an
    $_SESSION['blm_queries']++;

    if (mysql_error() > 0) {
        return false;
    }

    $id = mysql_insert_id();        // Welche Nummer hat unser Neuling?

    $sql_abfrage = "INSERT INTO
	punkte
(ID)
	VALUES
('" . $id . "');";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $sql_abfrage = "INSERT INTO
	statistik
(ID)
	VALUES
('" . $id . "');";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $sql_abfrage = "INSERT INTO
	gebaeude
VALUES 
(
	" . $id . ",";
    for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
        $sql_abfrage .= $Start["gebaeude"][$i] . ",";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -1);
    $sql_abfrage .= ");";
    mysql_query($sql_abfrage);        // Die Gebäude des Neuen anlegen
    $_SESSION['blm_queries']++;

    $sql_abfrage = "INSERT INTO
	forschung
VALUES 
(
	" . $id . ",";
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $sql_abfrage .= $Start["forschung"][$i] . ",";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -1);
    $sql_abfrage .= ");";
    mysql_query($sql_abfrage);        // Die Gebäude des Neuen anlegen
    $_SESSION['blm_queries']++;

    $sql_abfrage = "INSERT INTO
	lagerhaus
VALUES 
(
	" . $id . ",";
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $sql_abfrage .= $Start["lager"][$i] . ",";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -1);
    $sql_abfrage .= ");";
    mysql_query($sql_abfrage);        // Die Gebäude des Neuen anlegen
    $_SESSION['blm_queries']++;

    $empfaenger = $email;
    $betreff = "Bioladenmanager 2: Aktivierung Ihres Accounts";
    $nachricht = 'Willkommen beim Bioladenmanager 2,<br />
<br />
doch bevor Sie Ihr eigenes Imperium aufbauen können, müssen Sie Ihren Account aktivieren. Klicken Sie hierzu bitte auf folgenden Link:<br />
<br />
<a href="' . AJAX_SERVER_PFAD . '/actions/activate.php?user=' . htmlentities(stripslashes($name), ENT_QUOTES, "utf-8") . '&amp;code=' . $code . '">' . AJAX_SERVER_PFAD . '/actions/activate.php?user=' . htmlentities(stripslashes($name), ENT_QUOTES, "utf-8") . '&amp;code=' . $code . '</a><br />
<br />
Falls Sie sich nicht bei diesem Spiel registriert haben, so leiten Sie die EMail bitte ohne Bearbeitung weiter an:<br />
' . ADMIN_EMAIL . '<br />
<br />
MfG
' . SPIEL_BETREIBER;
    $header = "From: " . ADMIN_EMAIL . "
Reply-To: " . ADMIN_EMAIL . "
X-Mailer: PHP
MIME-Version: 1.0
Content-type: text/html; charset=utf-8";

    if (!@mail($empfaenger, $betreff, $nachricht, $header)) {
        die('Der Aktivierungscode konnte nicht versendet werden! Wahrscheinlich gibt es ein Problem mit dem Mailserver. Bitte senden Sie folgenden Code an <a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a>, um Ihren Account manuell aktivieren zu lassen:<br />
			<br />
			<pre>' . substr(base64_encode($name . "|" . $code), 0, -2) . "</pre>");
    }
    return true; // Alles fehlerfrei verlaufen
}

/**
 * Hilfsfunktion: Sucht und ersetzt BBCode in einem Text. Sorgt ausserdem für die sichere Ausgabe des Textes.
 *
 * @param string $text
 * @param void $dummy
 *
 * @return string
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function ReplaceBBCode($text, $dummy = null)
{
    $text = stripslashes($text);    // Entfernt die Escapezeichen (Backslashes)

    // Dann rufen wir die PHP Codeblöcke ab (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[php\](.*?)\[\/php\]/Suis', $text, $php);
    $anzPHP = count($php[1]);

    // Dann brauchen wir die HTML-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[html\](.*)\[\/html\]/Uuis', $text, $html);
    $anzHTML = count($html[1]);

    // Dann brauchen wir die SQL-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[sql\](.*)\[\/sql\]/Uuis', $text, $sql);
    $anzSQL = count($sql[1]);

    // Dann brauchen wir die CSS-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[css\](.*)\[\/css\]/Uuis', $text, $css);
    $anzCSS = count($css[1]);

    // Dann brauchen wir die Shell-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[bash\](.*)\[\/bash\]/Uuis', $text, $bash);
    $anzBASH = count($bash[1]);

    // Dann brauchen wir die C++-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[cpp\](.*)\[\/cpp\]/Uuis', $text, $cpp);
    $anzCPP = count($cpp[1]);

    // Dann brauchen wir die Java-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[java\](.*)\[\/java\]/Uuis', $text, $java);
    $anzJAVA = count($java[1]);

    // Dann brauchen wir die JavaScript-Codeblöcke (Keine Absicherung, da Sonderzeichen vokommen können und wichtig sind!)
    preg_match_all('/\[js\](.*)\[\/js\]/Uuis', $text, $js);
    $anzJS = count($js[1]);

    $colors[] = "aqua";                    // 
    $colors[] = "black";                // 
    $colors[] = "blue";                    // 
    $colors[] = "fuchsia";            // 
    $colors[] = "gray";                    // 
    $colors[] = "green";                // 
    $colors[] = "lime";                    // CSS-Farbwerte
    $colors[] = "maroon";                // können bei [color=XXX]
    $colors[] = "navy";                    // direkt eingegeben werden
    $colors[] = "olive";                // 
    $colors[] = "orange";                // 
    $colors[] = "purple";                // 
    $colors[] = "red";                    // 
    $colors[] = "silver";                // 
    $colors[] = "teal";                    // 
    $colors[] = "white";                // 
    $colors[] = "yellow";                // 

    /*
        Wie werden Zitate eingeschlossen?
    */
    $header['quote'] = '<div class="ZITAT">';
    $footer['quote'] = '</div>';

    /*
        Wie wird der Loginname in Zitaten eingeschlossen?
    */
    $header['quote_loginname'] = '<span class="ZITAT_KOPF">Zitat von &quot;';
    $footer['quote_loginname'] = '&quot;:</span>';

    /*
        Wie werden Codeteile eingeschlossen?
    */
    $header['code'] = '<div class="CODE"><span class="CODE_KOPF">Code:</span>';
    $footer['code'] = '</div>';

    /*
        Wie werden SQL-Teile eingeschlossen?
    */
    $header['sql'] = '<div class="SQL"><span class="SQL_KOPF">SQL-Anweisungen:</span>';
    $footer['sql'] = '</div>';

    /*
        Wie werden CSS-Teile eingeschlossen?
    */
    $header['css'] = '<div class="CSS"><span class="CSS_KOPF">CSS-Style:</span>';
    $footer['css'] = '</div>';

    /*
        Wie werden PHP-Teile eingeschlossen?
    */
    $header['php'] = '<div class="PHP"><span class="PHP_KOPF">PHP-Code:</span>';
    $footer['php'] = '</div>';

    /*
        Wie werden Shellscript-Teile eingeschlossen?
    */
    $header['bash'] = '<div class="BASH"><span class="BASH_KOPF">Shellscript:</span>';
    $footer['bash'] = '</div>';

    /*
        Wie werden C++-Teile eingeschlossen?
    */
    $header['cpp'] = '<div class="CPP"><span class="CPP_KOPF">C++ Code:</span>';
    $footer['cpp'] = '</div>';

    /*
        Wie werden Java-Teile eingeschlossen?
    */
    $header['java'] = '<div class="JAVA"><span class="JAVA_KOPF">Java-Code:</span>';
    $footer['java'] = '</div>';

    /*
        Wie werden JavaScript-Teile eingeschlossen?
    */
    $header['js'] = '<div class="JS"><span class="JS_KOPF">Javascript-Code:</span>';
    $footer['js'] = '</div>';

    /*
        Wie werden HTML-Teile eingeschlossen?
    */
    $header['html'] = '<div class="HTML"><span class="HTML_KOPF">HTML-Code:</span>';
    $footer['html'] = '</div>';

    $text = sichere_ausgabe($text, "UTF-8");        // dann escapen wir den String
    $quote = '&quot;';        // Und setzen den Trenner " auf sein HTML-Pendant

    for ($i = 0; $i < $anzPHP; $i++) {
        $php_['search'] = $php[1][$i];

        $geshi = new Geshi($php_['search'], 'php');
        $php_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[php]' . sichere_ausgabe($php_['search'], $encoding) . '[/php]', $header['php'] . $php_['highlight'] . $footer['php'], $text);
    }        // OK

    for ($i = 0; $i < $anzJAVA; $i++) {
        $java_['search'] = $java[1][$i];

        $geshi = new Geshi($java_['search'], 'java');
        $java_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[java]' . sichere_ausgabe($java_['search'], $encoding) . '[/java]', $header['java'] . $java_['highlight'] . $footer['java'], $text);
    }        // OK

    for ($i = 0; $i < $anzJS; $i++) {
        $js_['search'] = $js[1][$i];

        $geshi = new Geshi($js_['search'], 'javascript');
        $js_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[js]' . sichere_ausgabe($js_['search'], $encoding) . '[/js]', $header['js'] . $js_['highlight'] . $footer['js'], $text);
    }        // OK

    for ($i = 0; $i < $anzHTML; $i++) {
        $html_['search'] = $html[1][$i];

        $geshi = new Geshi($html_['search'], 'html4strict');
        $html_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[html]' . sichere_ausgabe($html_['search'], $encoding) . '[/html]', $header['html'] . $html_['highlight'] . $footer['html'], $text);
    }        // OK

    for ($i = 0; $i < $anzSQL; $i++) {
        $sql_['search'] = $sql[1][$i];

        $geshi = new Geshi($sql_['search'], 'mysql');
        $sql_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[sql]' . sichere_ausgabe($sql_['search'], $encoding) . '[/sql]', $header['sql'] . $sql_['highlight'] . $footer['sql'], $text);
    }        // OK

    for ($i = 0; $i < $anzCSS; $i++) {
        $css_['search'] = $css[1][$i];

        $geshi = new Geshi($css_['search'], 'css');
        $css_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[css]' . sichere_ausgabe($css_['search'], $encoding) . '[/css]', $header['css'] . $css_['highlight'] . $footer['css'], $text);
    }        // OK

    for ($i = 0; $i < $anzBASH; $i++) {
        $bash_['search'] = $bash[1][$i];

        $geshi = new Geshi($bash_['search'], 'bash');
        $bash_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[bash]' . sichere_ausgabe($bash_['search'], $encoding) . '[/bash]', $header['bash'] . $bash_['highlight'] . $footer['bash'], $text);
    }        // OK

    for ($i = 0; $i < $anzCPP; $i++) {
        $cpp_['search'] = $cpp[1][$i];

        $geshi = new Geshi($cpp_['search'], 'cpp');
        $cpp_['highlight'] = $geshi->parse_code();

        $text = str_ireplace('[cpp]' . sichere_ausgabe($cpp_['search'], $encoding) . '[/cpp]', $header['cpp'] . $cpp_['highlight'] . $footer['cpp'], $text);
    }        // OK

    $text = preg_replace(
        array(
            '/\[center\](.*)\[\/center\]/Uis',
            "/\[size=(1|2?){1}([0-9]{1})\](.*)\[\/size\]/Uis",
            "/\[url=&quot;(http\:\/\/|www.|http\:\/\/www.){1}([a-z0-9\-\_\.]{3,32}\.[a-z]{2,4})&quot;\](.*)\[\/url\]/SiU",
            "/\[img=&quot;(http\:\/\/[a-z0-9\-\_\.\/]{3,32}\.[a-z]{3,4})&quot;\](.*)\[\/img\]/SiU",
            "/\[email=&quot;([a-z0-9\-\_\.]{3,32}@[a-z0-9\-\_\.]{3,32}\.[a-z]{2,4})&quot;\](.*)\[\/email\]/SiU",
            "/\[emoticon=&quot;([a-z0-9\-\_\.\/]{3,64})&quot; \/\]/Si",
            "/\[code\](.*)\[\/code\]/Uism"
        ),
        array(
            '<div style="text-align: center;">\1</div>',
            '<span style="font-size: \1\2px;">\3</span>',
            '<a href="http://\2">\3</a>',
            '<a href="\1" target="_blank"><img src="\1" alt="\2" style="border: none;"/></a>',
            '<a href="mailto:\1">\2</a>',
            '<img src="\1" alt="\2" style="border: none;"/>',
            $header['code'] . '\1' . $footer['code']
        ),
        $text
    );        // Textformatierungen

    $last_text = "";
    while ($last_text != $text) {    // Diese Tags können geschachtelt werden, deshalb eine Schleife
        $last_text = $text;

        $text = preg_replace(
            array(
                "/\[color=(\#[0-9a-f]{6}|" . implode("|", $colors) . ")\](.*)\[\/color\]/is",
                "/\[(b|u|i)\](.*)\[\/\\1\]/Uis",
                '/\[quote](.*)\[\/quote\]/Uism'
            ),
            array(
                '<span style="color: \1;">\2</span>',
                '<\1>\2</\1>',
                $header['quote'] . '\1' . $footer['quote']
            ),
            $text);        // Farbige Schrift (Hexwerte / Namen), Tags welche geschachtelt werden können
    }

    /*
        Nun die Emoticons ersetzen
    */
    $text = str_ireplace(" ;p", '<img src="pics/emoticons/kopete006.png" alt=" ;p" />', $text);
    $text = str_ireplace(" $)", '<img src="pics/emoticons/kopete007.png" alt=" $)" />', $text);
    $text = str_ireplace(" 8)", '<img src="pics/emoticons/kopete008.png" alt=" 8)" />', $text);
    $text = str_ireplace(" ^^", '<img src="pics/emoticons/kopete010.png" alt=" ^^" />', $text);
    $text = str_ireplace(" :0", '<img src="pics/emoticons/kopete011.png" alt=" :0" />', $text);
    $text = str_ireplace(" :((", '<img src="pics/emoticons/kopete012.png" alt=" :((" />', $text);
    $text = str_ireplace(" ;)", '<img src="pics/emoticons/kopete013.png" alt=" ;)" />', $text);
    $text = str_ireplace(" :~", '<img src="pics/emoticons/kopete014.png" alt=" :~" />', $text);
    $text = str_ireplace(" :|", '<img src="pics/emoticons/kopete015.png" alt=" :|" />', $text);
    $text = str_ireplace(" :p", '<img src="pics/emoticons/kopete016.png" alt=" :p" />', $text);
    $text = str_ireplace(" :D", '<img src="pics/emoticons/kopete017.png" alt=" :D" />', $text);
    $text = str_ireplace(" :ö", '<img src="pics/emoticons/kopete018.png" alt=" :ö" />', $text);
    $text = str_ireplace(" :(", '<img src="pics/emoticons/kopete019.png" alt=" :(" />', $text);
    $text = str_ireplace(" :)", '<img src="pics/emoticons/kopete020.png" alt=" :)" />', $text);


    // Text zurükgeben
    return $text;
}

/**
 * Hilfsfunktion: Resettet einen bestimmten Account, meist vom User selbst beauftragt.
 *
 * @param int $benutzer_id
 * @param array $start_werte
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function ResetAccount($benutzer_id, $start_werte)
{
    $sql_abfrage = "UPDATE forschung SET ";
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $sql_abfrage .= "Forschung" . $i . "='" . $start_werte["forschung"][$i] . "', ";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);    // Zuerst die Forschung resetten
    $_SESSION['blm_queries']++;

    $sql_abfrage = "UPDATE gebaeude SET ";
    for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
        $sql_abfrage .= "Gebaeude" . $i . "='" . $start_werte["gebaeude"][$i] . "', ";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);    // Dann die Gebäude resetten
    $_SESSION['blm_queries']++;

    $sql_abfrage = "UPDATE lagerhaus SET ";
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
        $sql_abfrage .= "Lager" . $i . "='" . $start_werte["lager"][$i] . "', ";
    }
    $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);    // Und zum Schluss das Lagerhaus
    $_SESSION['blm_queries']++;

    $sql_abfrage = "UPDATE
    mitglieder m 
NATURAL JOIN 
    (
    punkte p 
        NATURAL JOIN 
    statistik s
    )
SET
	m.Bank=0,
	m.Geld=" . $start_werte["geld"] . ",
	m.Punkte=0,
	m.LastAction=0,
	m.LastLogin=0,
	m.LastMafia=" . time() . ",
	m.OnlineZeit=0,
	m.Gruppe=NULL,
	m.GruppeRechte=NULL,
	m.GruppeLastMessageZeit=NULL,
	p.GebaeudePlus=0,
	p.ForschungPlus=0,
	p.ProduktionPlus=0,
	p.MafiaPlus=0,
	p.MafiaMinus=0,
	s.AusgabenGebaeude=0,
	s.AusgabenForschung=0,
	s.AusgabenZinsen=0,
	s.AusgabenProduktion=0,
	s.AusgabenMarkt=0,
	s.AusgabenVertraege=0,
	s.AusgabenMafia=0,
	s.AusgabenSonstiges=0,
	s.EinnahmenGebaeude=0,
	s.EinnahmenVerkauf=0,
	s.EinnahmenZinsen=0,
	s.EinnahmenMarkt=0,
	s.EinnahmenVertraege=0,
	s.EinnahmenMafia=0
WHERE
	m.ID='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    /*
        Alle Aufträge, ausstehende Marktangebote und Verträge löschen
    */
    $sql_abfrage = "DELETE FROM
	auftrag
WHERE
	Von='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $sql_abfrage = "DELETE FROM
	vertraege
WHERE
	Von='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $sql_abfrage = "DELETE FROM
	marktplatz
WHERE
	Von='" . $benutzer_id . "';";
    mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;
    /*
        Alles ist gelöscht, Fertig.
    */
}

/**
 * Kernfunktion: Resettet alle Accounts (z.B beim Rundenende)
 *
 * @param boolean $RundeZuEnde
 * @param array $start_werte
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function ResetAll($RundeZuEnde, $start_werte)
{
    if (!$RundeZuEnde) {        // Die Runde ist nicht zuende, das ist also ein Reset direkt vom Admin. Keine Ahnung warum, also muss der Admin
        // manuell eine Rundmail mit einer Begründung verfassen
        /*
            Wenn ja, dann leere gleich mal die Tabellen, die nur für eine Runde wichtig sind
        */
        $sql_abfrage = "TRUNCATE TABLE auftrag;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "TRUNCATE TABLE marktplatz;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "TRUNCATE TABLE vertraege;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "TRUNCATE TABLE gruppe;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "TRUNCATE TABLE gruppe_nachrichten;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "TRUNCATE TABLE gruppe_diplomatie;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "SELECT
    ID
FROM
    mitglieder
ORDER BY
    ID;";
        $sql_ergebnis = mysql_query($sql_abfrage);    // Dann hole dir der Reihe nach alle Benutzer
        $_SESSION['blm_queries']++;

        while ($benutzer = mysql_fetch_object($sql_ergebnis)) {        // Solange es Benutzer gibt,
            ResetAccount($benutzer->ID, $start_werte);                        // werden diese resettet
        }
    } else {        // Die Runde ist zu Ende, automatischer Reset
        $sql_abfrage = "SELECT
    ID,
    Name AS n,
    Punkte AS p
FROM
    mitglieder
WHERE
    ID>1
ORDER BY
    Punkte DESC
LIMIT
    0, 5";
        $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft alle Spieler mit deren Punkten ab
        $_SESSION['blm_queries']++;

        $platz = 0;        // Platzhalter für die Platzierung

        while ($benutzer = mysql_fetch_object($sql_ergebnis)) {
            // Erzeugt ein Assoziatives Array für die ganzen Platzierungen, jedoch sind nur die ersten 3 interessant
            $platz++;
            $Platz[$platz]["Name"] = $benutzer->n;
            $Platz[$platz]["Punkte"] = number_format($benutzer->p, 0, ",", ".");

            $sql_abfrage = "UPDATE
    mitglieder
SET
    EwigePunkte=EwigePunkte+" . (6 - $platz) . "
WHERE
    ID='" . $benutzer->ID . "';";
            mysql_query($sql_abfrage);                        // Gibt dem Spieler Punkte in der ewigen Highscoreliste
            $_SESSION['blm_queries']++;
        }

        $tag = date("d");            // Der aktuelle Tag
        $monat = date("m");        // ... Monat
        $jahr = date("Y");        // Das aktuelle Jahr

        $std = 20;                // Wann soll die neue Runde starten (Stunde)
        $min = 0;                    // '' (Minute)
        $sek = 0;                    // '' (Sekunde)

        $rundenstart = (mktime($std, $min, $sek, $monat, $tag, $jahr) + RUNDEN_PAUSE);        // Rechnet den Start der neuen Runde aus. (604800:	Startzeitpunkt ist 7 Tage in der Zukunft (in Sekunden))

        $betreff = "Rundenende!";        // Betreff der Rundmail
        $nachricht = "Hallo,

Die Runde ist vorbei, und wir haben den König der Biobauern gefunden! Herzlichen Glückwunsch an die Gewinner, nachfolgend die Top 5 mit ihren Punktzahlen:
[b]
1. " . $Platz[1]["Name"] . " mit " . $Platz[1]["Punkte"] . " Punkten,
2. " . $Platz[2]["Name"] . " mit " . $Platz[2]["Punkte"] . " Punkten,
3. " . $Platz[3]["Name"] . " mit " . $Platz[3]["Punkte"] . " Punkten,
4. " . $Platz[4]["Name"] . " mit " . $Platz[4]["Punkte"] . " Punkten und
5. " . $Platz[5]["Name"] . " mit " . $Platz[5]["Punkte"] . " Punkten
[/b]
Das Spiel wurde schon resettet, die neue Runde startet am " . date("d.m.Y \u\m H:i", $rundenstart) . ". Viel Spaß weiterhin :)

[i]-System-[/i]";        // die Nachricht mit den 5 Erstplatzierten an alle Spieler

        /*
            Dann muss noch irgendwie der Startzeitpunkt der Runde festgehalten werden.
            Ich habe mich da für ne einfache Datei entschieden, die ne Konstante definiert.
            
            Schreibt den Zeitpunkt des letzten Resets in eine Datei
        */
        $datei = fopen("include/last_reset.inc.php", "w");

        fwrite($datei, '<?php define("LAST_RESET","' . $rundenstart . '"); ?>');
        fclose($datei);
        if (!$datei) {
            die("möp");
        }

        // Erst wenn alles erledigt ist, werden die Accounts resettet
        ResetAll(false, $start_werte);

        // Und abschließend geht die Rundmail raus.
        NachrichtAnAlle($betreff, $nachricht);
    }
}

/**
 * Hilfsfunktion: Liefert die Anzahl der gerade aktiven Spieler zurück
 *
 * @return int
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function SpielerOnline()
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	mitglieder
WHERE
	LastAction>" . (time() - 300) . "
AND
	ID>0;";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $spieler = mysql_fetch_object($sql_ergebnis);

    return intval($spieler->anzahl);        // Anzahl zurückgeben
}

/**
 * Hilfsfunktion: Aktualisiert die letzte Aktion in der DB
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function UpdateLastAction()
{
    $sql_abfrage = "UPDATE
	mitglieder
SET
	LastAction='" . time() . "'
WHERE
	ID='" . $_SESSION['blm_user'] . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);    // Zeitstempel der letzten Aktion auf jetzt setzen.
    $_SESSION['blm_queries']++;
}

/**
 * Hilfsfunktion: Liefert die Anzahl der sich im Posteingang befindenden Verträge zurück
 *
 * @return int
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function Vertraege()
{
    $sql_abfrage = "SELECT
	COUNT(*) AS anzahl
FROM
	vertraege
WHERE
	An='" . $_SESSION['blm_user'] . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $vertraege = mysql_fetch_object($sql_ergebnis);

    return intval($vertraege->anzahl);
}

/**
 * Hilfsfunktion: Liefert den Warenname einer WarenID zurück
 *
 * @param int $waren_id
 * @param boolean $igm
 *
 * @return string
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function WarenName($waren_id, $igm = false)
{
    switch (intval($waren_id)) {
        case 0:
            return "Geld";
        case 1:
            return "Kartoffeln";
        case 2:
            return "Karotten";
        case 3:
            return "Tomaten";
        case 4:
            return "Salat";
        case 5:
            if ($igm)
                return "Äpfel";
            else
                return "&Auml;pfel";
        case 6:
            return "Birnen";
        case 7:
            return "Kirschen";
        case 8:
            return "Bananen";
        case 9:
            return "Gurken";
        case 10:
            return "Weintrauben";
        case 11:
            return "Tabak";
        case 12:
            return "Ananas";
        case 13:
            return "Erdbeeren";
        case 14:
            return "Orangen";
        case 15:
            return "Kiwi";
        default:        // Unbekannte waren_id
            return "<i>Unknown</i>";
    }
}

/**
 * Escaped einen String zur sicheren Ausgabe in einer HTML-Anwendung (verhindert XSS). Ein optionaler Parameter gibt an, welche Kodierung der String hat
 *
 * @param string $text
 * @param string $encoding
 *
 * @return string
 **@version 1.0.3
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function sichere_ausgabe($text, $encoding = 'UTF-8')
{
    return nl2br(htmlentities(stripslashes($text), ENT_QUOTES, $encoding));
    // Escaped alle HTML-Steuerzeichen, damit der String ohne Bedenken
    // ausgegeben werden kann.
}

/**
 * Hilfsfunktion: Ruft den Namen eines Spielers ab
 *
 * @param int $id
 *
 * @return string
 **@author Simon Frankenberger <simonfrankenberger@web.de>
 * @version 1.0.3
 *
 */
function getSpielerName($id)
{
    $sql_abfrage = "SELECT
	Name
FROM
	mitglieder
WHERE
	ID='" . intval($id) . "'
;";
    $sql_ergebnis = mysql_query($sql_abfrage);

    $temp = mysql_fetch_object($sql_ergebnis);

    return sichere_ausgabe($temp->Name);
}
