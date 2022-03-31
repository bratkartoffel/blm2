<?php
/**
 * Die "DNA" des Programms, hier stehen alle Konstanten wichtigen Variablen des Spiels
 *
 * @version 1.0.5
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.includes
 */

/*

Changelog:

[1.0.5]
    - ADMIN_EMAIL: hinzugefügt
    - SPIEL_BETREIBER: hinzugefügt

*/

/*
    Allgemeine Konstanten (wichtiger Teil, bedürfen Anpassung!
*/
define('VERSION', "1.9.4+" . substr(file_get_contents(basename(__FILE__) . "../.git/HEAD"), 0, 8));

if (!file_exists(dirname(__FILE__) . "/config_live.inc.php")) {
    /*
     * Sollte folgende defines drin haben:

define("DB_SERVER", "localhost");    // Datenbankserver
define("DB_BENUTZER", "");           // Benutzername, sollte niemals 'root' sein!
define("DB_PASSWORT", "");           // Passwort
define("DB_DATENBANK", "");          // Datenbank Name

define("SERVER_PFAD", "https://blm2.fraho.eu");     // Definiert den Serverpfad für die eMails
define("ADMIN_EMAIL", "simon-blm2@fraho.eu");       // Definiert die EMailadresse des Admins
define("SPIEL_BETREIBER", "Simon Frankenberger");   // Mit diesem Namen wird die Registrierungsmail unterschrieben
define("WARTUNGS_ARBEITEN", false);                 // Sperrt jeglichen Zugriff auf den Server
define("WARTUNGS_TEXT", "Das Spiel befindet sich gerade im Wartungsmodus (Einspielen von Updates und Bugfixes). Bitte versuchen Sie es in ein paar Minuten erneut.");
define("SPECIAL_STYLE", true);                      // Wird ein Spezialstyle verwendet?
define("SPECIAL_RUNNING", false);                   // Läuft gerade ein Special?

// strtotime("2022-03-29 10:00:00")
define("LAST_RESET", 1648540800);                   // Wann war der letzte Reset?
    */
    die(dirname(__FILE__) . "config_live.inc.php not found");
}
require_once(dirname(__FILE__) . "/config_live.inc.php");


/*
 * Folgende Werte können geändert werden, müssen aber nicht ;)
*/
const SERVER_KOSTEN = 0.000006;                                // Serverkosten pro Sekunde, Standard: ~16 € / Monat
const TIMEOUT_INAKTIV = 3600;                                    // Wann wird der Benutzer automatisch ausgeloggt, wenn er nichts unternimmt? (in Sekunden, Standard: 3600)
const ANZAHL_WAREN = 15;
const ANZAHL_GEBAEUDE = 8;

const RUNDEN_DAUER = 7776000;    // Die Dauer einer Runde in Sekunden (Standard: 7776000 Sekunden = 3 Monate)
const RUNDEN_PAUSE = 259200;        // Gibt die Dauer der Pause zwischen 2 Runden in Sekunden an (Standard: 259200 = 3 Tage)

const EINKOMMEN_DAUER = 1800;                        // Was ist das Einkommens-Interval? (in Sekunden, vom CronJob abhängig!)
const EINKOMMEN_BASIS = 30;                            // Wieviel ist die Einkommensbasis?
const EINKOMMEN_BIOLADEN_BONUS = 5;            // Wieviel Bonus bringt eine Stufe des Bioladens?
const EINKOMMEN_DOENERSTAND_BONUS = 8;        // Wieviel Bonus bringt eine Stufe des Dönerstands?

const BONUS_FAKTOR_FORSCHUNGSZENTRUM = 0.037;        // Zeitbonus absolut für Forschungen je Stufe des Forschungszentrums
const BONUS_FAKTOR_BAUHOF = 0.036;                            // Zeitbonus absolut für Gebäude je Stufe des Bauhofs
const BONUS_FAKTOR_ZAUN = 2.5;                                        // Bonus des Zauns in % gegen die Mafia
const BONUS_FAKTOR_PIZZERIA = 2.5;                                // Bonus des Zauns in % für die Mafia

const ZINSEN_DAUER = 1800;                // Was ist das Zins-Interval? (in Sekunden, vom CronJob abhängig!)
const ZINSEN_HABEN_MIN = 150;        // die minimalen Habenzinsen
const ZINSEN_HABEN_MAX = 180;        // die maximalen Habenzinsen
const ZINSEN_SOLL_MIN = 200;            // die minimalen Sollzinsen
const ZINSEN_SOLL_MAX = 250;            // die maximalen Sollzinsen

const DISPO_LIMIT = -35000;            // Der maximale Dispo, jeder größer oder gleich dem Betrag wird resettet!!

const WAREN_KURS_MIN = 75;                                        // Der minimale Warenkurs
const WAREN_KURS_MAX = 115;                                    // der maximale Warenkurs
const WAREN_PREIS_GRUND = 1.35;                            // Grundverkaufspreis der Waren
const WAREN_PREIS_FORSCHUNG = 0.15;                    // Um wieviel erhöht eine Stufe der Forschung den Verkaufspreis?
const WAREN_PREIS_BIOLADEN = 0.08;                        // Um wieviel erhöht eine Stufe des Bioladens den Verkaufspreis?
const WAREN_PREIS_VERKAEUFERSCHULE = 0.12;        // Um wieviel erhöht eine Stufe der Verkäuferschule den Verkaufspreis?
const WAREN_PREIS_WARE = 0.40;                                // Wieviel ist eine Ware mehr wert als die vorherige?

const PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE = 8;            // Wieviel kg bringt eine Stufe des Gemüses?
const PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN = 4;        // Wieveil muss man pro Stufe Forschung des Gemüses mehr zahlen?
const PRODUKTIONS_WAREN_FAKTOR_MENGE = 20;                    // Wieviel bringt eine Ware Bonus zur Vorherigen in kg?
const PRODUKTIONS_PLANTAGE_FAKTOR_MENGE = 10;            // Wieviel wird pro Stufe der Plantage mehr produziert?

const RANGLISTE_OFFSET = 15;                        // Wieviele Einträge sollen in der Rangliste pro Seite stehen?
const GRUPPE_OFFSET = 10;                            // Wieviele Gruppennachrichten sollten pro Seite angezeigt werden?
const ADMIN_LOG_OFFSET = 25;                      // Wieviele Einträge sollten pro Seite im Adminlog angezeigt werden?
const MARKTPLATZ_OFFSET = 25;                    // Wieviele Marktplatzangebote sollen pro Seite angezeigt werden?

const BILD_GROESE_MAXIMAL = 131072;            // Die maximale Bildgröße fürs Profil in Byte (128 kb)

const MAFIA_DIEBSTAHL_MIN_RATE = 40;        // Die minimale und maximale Rate was bei
const MAFIA_DIEBSTAHL_MAX_RATE = 75;        // einem Bargeldangriff gestohlen werden kann
const MAFIA_FAKTOR_MIN_PUNKTE = 1.5;    // Ihre Punkte / FAKTOR = Minimale Punktezahl, welche angegriffen werden kann
const MAFIA_FAKTOR_MAX_PUNKTE = 1.5;    // Ihre Punkte * FAKTOR = Maximale Punktezahl, welche angegriffen werden kann

const MARKT_ZURUECKZIEH_FAKTOR = 0.90;    // Wieviel (als Faktor) bekommt der Benutzer beim rückziehen eines Angebotes zurück?
const MARKT_PROVISION_FAKTOR = 0.98;        // Wieviel (als Faktor) bekommt der Benutzer beim Markt abzüglich Provision?

const VERTRAEGE_PROVISION_FAKTOR = 1.00;        // Wieviel (als Faktor) bekommt der Verkäufer bei einem Vertrag?

const MAFIA_SPERRZEIT_SPIONAGE = 300;            //
const MAFIA_SPERRZEIT_DIEBSTAHL = 1800;        // Wie lange ist die Mafia
const MAFIA_SPERRZEIT_ANGRIFF = 900;                // nach einer bestimmten Aktion
const MAFIA_SPERRZEIT_BOMBEN = 14400;                //	gesperrt?

const MAFIA_PUNKTE_SPIONAGE = 25;                //
const MAFIA_PUNKTE_DIEBSTAHL = 150;            // Wieviel Punkte gibt ein erfolgreicher
const MAFIA_PUNKTE_ANGRIFF = 75;                    //	Angriff per Mafia?
const MAFIA_PUNKTE_BOMBEN = 1000;                    //
const MAFIA_PUNKTE_SUB = 1.25;                        // Wieviele Punkte verliert der Gegner bei erfolgreichen Bomben der Plantage? (in % von den Punkten der aktuellen Stufe)

const AUFTRAG_RUECKZIEH_RETURN = 0.75;        // Wieviel bekommt der User beim abbrechen eines Auftrag zurück?

const FORSCHUNG_MIN_DAUER = 7200;            // Wie lange dauert jede Forschung mindestens?

const MAX_ANZAHL_GRUPPENMITGLIEDER = 15;    // Wie viele Mitglieder kann eine Gruppe maximal haben?

/*
    Nachfolgend kommen die Startwerte für die Spieler als assoziatives Array
*/
$Start["geld"] = 5000;

$Start["gebaeude"][1] = 1;
$Start["gebaeude"][2] = 0;
$Start["gebaeude"][3] = 1;
$Start["gebaeude"][4] = 0;
$Start["gebaeude"][5] = 0;
$Start["gebaeude"][6] = 0;
$Start["gebaeude"][7] = 0;
$Start["gebaeude"][8] = 0;

$Start["forschung"][1] = 1;
$Start["forschung"][2] = 0;
$Start["forschung"][3] = 0;
$Start["forschung"][4] = 0;
$Start["forschung"][5] = 0;
$Start["forschung"][6] = 0;
$Start["forschung"][7] = 0;
$Start["forschung"][8] = 0;
$Start["forschung"][9] = 0;
$Start["forschung"][10] = 0;
$Start["forschung"][11] = 0;
$Start["forschung"][12] = 0;
$Start["forschung"][13] = 0;
$Start["forschung"][14] = 0;
$Start["forschung"][15] = 0;

$Start["lager"][1] = 100;
$Start["lager"][2] = 0;
$Start["lager"][3] = 0;
$Start["lager"][4] = 0;
$Start["lager"][5] = 0;
$Start["lager"][6] = 0;
$Start["lager"][7] = 0;
$Start["lager"][8] = 0;
$Start["lager"][9] = 0;
$Start["lager"][10] = 0;
$Start["lager"][11] = 0;
$Start["lager"][12] = 0;
$Start["lager"][13] = 0;
$Start["lager"][14] = 0;
$Start["lager"][15] = 0;
/*
    Das waren die Startwerte für die Spieler
*/

// Hier kommt die Währung des Spiels in 2 verschiedenen Formen.
$Currency = "€";                // Währung als direktes Zeichen

// Hier wird der Titel der Seite anhand des Namens der Unterseite generiert
if ($_GET['p'] != "")
    $Titel = explode("_", $_GET['p']);
else
    $Titel[] = "index";

// Der folgende Block ist für die Großschreibung des ersten Buchstabens des Titels verantwortlich.
if (count($Titel) == 1)        // Titel hat nur ein Wort
    $Titel = ucfirst($Titel[0]);
else    // Titel hat 2 Worte (maximum!)
    $Titel = ucfirst($Titel[0]) . " " . ucfirst($Titel[1]);
$Titel = "Der Bioladenmanager 2 (" . $Titel . ")";        // Setzt den Titel so wie er auf der Seite steht zusammen

/*
    Nachfolgend alle Basisdaten der Gebäude
*/
$Plantage = new stdClass();
$Plantage->BasisKosten = 260;
$Plantage->BasisDauer = 890;
$Plantage->BasisPunkte = 120;
$Plantage->KostenFaktor = 1.35;
$Plantage->DauerFaktor = 1.25;
$Plantage->PunkteFaktor = 1.23;

$Forschungszentrum = new stdClass();
$Forschungszentrum->BasisKosten = 320;
$Forschungszentrum->BasisDauer = 950;
$Forschungszentrum->BasisPunkte = 105;
$Forschungszentrum->KostenFaktor = 1.37;
$Forschungszentrum->DauerFaktor = 1.28;
$Forschungszentrum->PunkteFaktor = 1.20;

$Bioladen = new stdClass();
$Bioladen->BasisKosten = 260;
$Bioladen->BasisDauer = 900;
$Bioladen->BasisPunkte = 90;
$Bioladen->KostenFaktor = 1.35;
$Bioladen->DauerFaktor = 1.27;
$Bioladen->PunkteFaktor = 1.20;

$Doenerstand = new stdClass();
$Doenerstand->BasisKosten = 310;
$Doenerstand->BasisDauer = 1100;
$Doenerstand->BasisPunkte = 115;
$Doenerstand->KostenFaktor = 1.38;
$Doenerstand->DauerFaktor = 1.27;
$Doenerstand->PunkteFaktor = 1.21;

$Bauhof = new stdClass();
$Bauhof->BasisKosten = 620;
$Bauhof->BasisDauer = 1100;
$Bauhof->BasisPunkte = 235;
$Bauhof->KostenFaktor = 1.40;
$Bauhof->DauerFaktor = 1.29;
$Bauhof->PunkteFaktor = 1.22;

$Schule = new stdClass();
$Schule->BasisKosten = 300;
$Schule->BasisDauer = 1000;
$Schule->BasisPunkte = 110;
$Schule->KostenFaktor = 1.39;
$Schule->DauerFaktor = 1.29;
$Schule->PunkteFaktor = 1.19;

$Zaun = new stdClass();
$Zaun->BasisKosten = 650;
$Zaun->BasisDauer = 1400;
$Zaun->BasisPunkte = 285;
$Zaun->KostenFaktor = 1.45;
$Zaun->DauerFaktor = 1.33;
$Zaun->PunkteFaktor = 1.17;

$Pizzeria = new stdClass();
$Pizzeria->BasisKosten = 650;
$Pizzeria->BasisDauer = 1400;
$Pizzeria->BasisPunkte = 285;
$Pizzeria->KostenFaktor = 1.45;
$Pizzeria->DauerFaktor = 1.33;
$Pizzeria->PunkteFaktor = 1.17;

/*
    Ende der Basisdaten für die Gebäude
*/

$Produktion = new stdClass();
$Produktion->BasisMenge = 350;        // in kg	 \
$Produktion->BasisKosten = 200;        // in €			=> Die ganzen Basisdaten für die Produktion
$Produktion->BasisDauer = 3600;        // in sek	 /

$Forschung = new stdClass();
$Forschung->BasisKosten = 230;        // in Sekunden	\
$Forschung->BasisDauer = 1800;        // in €				 \	 =>Die Forschungsbasisdaten
$Forschung->BasisPunkte = 80;            //						/
$Forschung->KostenFaktor = 1.29;    //
$Forschung->DauerFaktor = 1.26;        //
$Forschung->PunkteFaktor = 1.13;    // in Prozent		/

$KursDatum = date("dmY", time());        // Seed für den Zufallsgenerator der Zinssätze (wechselt täglich)
$KursWaren = date("ymdH", time());        // Seed für den Zufallsgenerator der Warensätze (wechselt stündlich)

srand($KursDatum);        // Zuerst werden die Zinssätze berechnet

$ZinsenKredit = round(1 + (rand(ZINSEN_SOLL_MIN, ZINSEN_SOLL_MAX) / 10000), 4);        // Zinsen der Kredite
$ZinsenAnlage = round(1 + (rand(ZINSEN_HABEN_MIN, ZINSEN_HABEN_MAX) / 10000), 4);        // Zinsen für Anlagen

srand($KursWaren);        // Dann kommen die Kurse der Waren dran

for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Wir haben (im Moment) 15 Waren, also brauchen wir 15 Kurse
    $KursWare[$i] = rand(WAREN_KURS_MIN, WAREN_KURS_MAX) / 100;        // Der Kurs wird per Zufall berechnet ;)
}

// Dann berechnen wir den Zeitpunkt des letzten Einkommens:

if (date("i") >= 30) {
    $LetztesEinkommen = mktime(date("H"), 30, 0, 1, 1, 2008);
} else {
    $LetztesEinkommen = mktime(date("H"), 0, 0, 1, 1, 2008);
}


$vorlage_admin[1] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p5"]Punkt 5[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass die Zugriffe auf die Accounts __ACCOUNT1__ und __ACCOUNT2__ stets von der selben IP-Adresse aus erfolgten. 

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Bitte lösche umgehend einen der beiden Accounts. Falls wir weitere Verstöße gegen die Regeln feststellen, werden wir weitere Maßnahmen (z.B. einen Ban) einleiten.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;

$vorlage_admin[2] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p9"]Punkt 9[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass auf deinen Account innerhalb kürzester Zeit mehrere Zugriffe von verschiedenen IP-Adressen erfolgten.

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Bitte ändere umgehend dein Passwort, um weitere Verwarnungen zu vermeiden. Falls wir weitere Verstöße gegen die Regeln feststellen, werden wir weitere Maßnahmen (z.B. einen Ban) einleiten.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;

$vorlage_admin[3] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p7"]Punkt 7[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass du dir auf Grund von Spielfehlern einen unfairen Vorteil gegenüber den anderen Spielern verschafft hast.

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Bitte melde solche Spielfehler das nächste Mal umgehend einen Admin, sodass diese behoben werden können.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;

$vorlage_admin[4] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p4"]Punkt 4[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass du durch dieses Verhalten eine enorme Datenmenge verursacht hast.

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Bitte höre auf, solche Datenmenge zu erzeugen, da dadurch die Serverlast nur unnötigt steigt.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;

$vorlage_admin[5] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p10"]Punkt 10[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass du einen Spieler oder eine Gruppe ausgenutzt hast. Folgender Auszug aus dem Logbuch zeigt den verursachten Schaden.

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Fair-Play ist bei Spielen sehr wichtig. Wir akzeptieren solch ein Verhalten, wie du es gezeigt hast, nicht und im Wiederholungsfall werden wir weitere Maßnahmen ergreifen.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;

$vorlage_admin[6] = <<<EODATA
Hallo,

laut unseren Logbüchern hast du gegen [url="http://localhost/blm2/?p=regeln#p12"]Punkt 12[/url] der Regeln verstoßen. Wir konnten dabei feststellen, dass du einen Spieler ausgenutzt und durch erhöhte Preise unspielbar gemacht.

Auszug Logbuch:
[code]
........
[/code]

Dadurch hast du eine Verwarnung erhalten. Wir akzeptieren solch ein Verhalten, wie du es gezeigt hast, nicht und im Wiederholungsfall werden wir weitere Maßnahmen ergreifen.

MfG
__NAME__, im Auftrag von [url="http://localhost/blm2/?p=profil&uid=1"]Bratkartoffel[/url]
EODATA;
