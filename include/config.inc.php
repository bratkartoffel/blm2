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
define("VERSION", "1.9.4+1");                                            // Die aktuelle Versionsnummer

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
    */
    die(dirname(__FILE__) . "config_live.inc.php not found");
}
require_once(dirname(__FILE__) . "/config_live.inc.php");



/*
 * Folgende Werte können geändert werden, müssen aber nicht ;)
*/
define("SERVER_KOSTEN", 0.000006);                                // Serverkosten pro Sekunde, Standard: ~16 € / Monat
define("TIMEOUT_INAKTIV", 3600);                                    // Wann wird der Benutzer automatisch ausgeloggt, wenn er nichts unternimmt? (in Sekunden, Standard: 3600)
define("ANZAHL_WAREN", 15);
define("ANZAHL_GEBAEUDE", 8);

define("RUNDEN_DAUER", 7776000);    // Die Dauer einer Runde in Sekunden (Standard: 7776000 Sekunden = 3 Monate)
define("RUNDEN_PAUSE", 259200);        // Gibt die Dauer der Pause zwischen 2 Runden in Sekunden an (Standard: 259200 = 3 Tage)

define("EINKOMMEN_DAUER", 1800);                        // Was ist das Einkommens-Interval? (in Sekunden, vom CronJob abhängig!)
define("EINKOMMEN_BASIS", 30);                            // Wieviel ist die Einkommensbasis?
define("EINKOMMEN_BIOLADEN_BONUS", 5);            // Wieviel Bonus bringt eine Stufe des Bioladens?
define("EINKOMMEN_DOENERSTAND_BONUS", 8);        // Wieviel Bonus bringt eine Stufe des Dönerstands?

define("BONUS_FAKTOR_FORSCHUNGSZENTRUM", 0.037);        // Zeitbonus absolut für Forschungen je Stufe des Forschungszentrums
define("BONUS_FAKTOR_BAUHOF", 0.036);                            // Zeitbonus absolut für Gebäude je Stufe des Bauhofs
define("BONUS_FAKTOR_ZAUN", 2.5);                                        // Bonus des Zauns in % gegen die Mafia
define("BONUS_FAKTOR_PIZZERIA", 2.5);                                // Bonus des Zauns in % für die Mafia

define("ZINSEN_DAUER", 1800);                // Was ist das Zins-Interval? (in Sekunden, vom CronJob abhängig!)
define("ZINSEN_HABEN_MIN", 150);        // die minimalen Habenzinsen
define("ZINSEN_HABEN_MAX", 180);        // die maximalen Habenzinsen
define("ZINSEN_SOLL_MIN", 200);            // die minimalen Sollzinsen
define("ZINSEN_SOLL_MAX", 250);            // die maximalen Sollzinsen

define("DISPO_LIMIT", -35000);            // Der maximale Dispo, jeder größer oder gleich dem Betrag wird resettet!!

define("WAREN_KURS_MIN", 75);                                        // Der minimale Warenkurs
define("WAREN_KURS_MAX", 100);                                    // der maximale Warenkurs
define("WAREN_PREIS_GRUND", 1.35);                            // Grundverkaufspreis der Waren
define("WAREN_PREIS_FORSCHUNG", 0.15);                    // Um wieviel erhöht eine Stufe der Forschung den Verkaufspreis?
define("WAREN_PREIS_BIOLADEN", 0.08);                        // Um wieviel erhöht eine Stufe des Bioladens den Verkaufspreis?
define("WAREN_PREIS_VERKAEUFERSCHULE", 0.12);        // Um wieviel erhöht eine Stufe der Verkäuferschule den Verkaufspreis?
define("WAREN_PREIS_WARE", 0.40);                                // Wieviel ist eine Ware mehr wert als die vorherige?

define("PRODUKTIONS_FORSCHUNGS_FAKTOR_MENGE", 8);            // Wieviel kg bringt eine Stufe des Gemüses?
define("PRODUKTIONS_FORSCHUNGS_FAKTOR_KOSTEN", 4);        // Wieveil muss man pro Stufe Forschung des Gemüses mehr zahlen?
define("PRODUKTIONS_WAREN_FAKTOR_MENGE", 20);                    // Wieviel bringt eine Ware Bonus zur Vorherigen in kg?
define("PRODUKTIONS_PLANTAGE_FAKTOR_MENGE", 10);            // Wieviel wird pro Stufe der Plantage mehr produziert?

define("RANGLISTE_OFFSET", 15);                        // Wieviele Einträge sollen in der Rangliste pro Seite stehen?
define("GRUPPE_OFFSET", 10);                            // Wieviele Gruppennachrichten sollten pro Seite angezeigt werden?
define("MARKTPLATZ_OFFSET", 25);                    // Wieviele Marktplatzangebote sollen pro Seite angezeigt werden?

define("BILD_GROESE_MAXIMAL", 65536);            // Die maximale Bildgröße fürs Profil in Byte

define("MAFIA_DIEBSTAHL_MIN_RATE", 40);        // Die minimale und maximale Rate was bei
define("MAFIA_DIEBSTAHL_MAX_RATE", 75);        // einem Bargeldangriff gestohlen werden kann
define("MAFIA_FAKTOR_MIN_PUNKTE", 1.5);    // Ihre Punkte / FAKTOR = Minimale Punktezahl, welche angegriffen werden kann
define("MAFIA_FAKTOR_MAX_PUNKTE", 1.5);    // Ihre Punkte * FAKTOR = Maximale Punktezahl, welche angegriffen werden kann

define("MARKT_ZURUECKZIEH_FAKTOR", 0.90);    // Wieviel (als Faktor) bekommt der Benutzer beim rückziehen eines Angebotes zurück?
define("MARKT_PROVISION_FAKTOR", 0.98);        // Wieviel (als Faktor) bekommt der Benutzer beim Markt abzüglich Provision?

define("VERTRAEGE_PROVISION_FAKTOR", 1.00);        // Wieviel (als Faktor) bekommt der Verkäufer bei einem Vertrag?

define("MAFIA_SPERRZEIT_SPIONAGE", 300);            //
define("MAFIA_SPERRZEIT_DIEBSTAHL", 1800);        // Wie lange ist die Mafia
define("MAFIA_SPERRZEIT_ANGRIFF", 900);                // nach einer bestimmten Aktion
define("MAFIA_SPERRZEIT_BOMBEN", 14400);                //	gesperrt?

define("MAFIA_PUNKTE_SPIONAGE", 25);                //
define("MAFIA_PUNKTE_DIEBSTAHL", 150);            // Wieviel Punkte gibt ein erfolgreicher
define("MAFIA_PUNKTE_ANGRIFF", 75);                    //	Angriff per Mafia?
define("MAFIA_PUNKTE_BOMBEN", 1000);                    //
define("MAFIA_PUNKTE_SUB", 1.25);                        // Wieviele Punkte verliert der Gegner bei erfolgreichen Bomben der Plantage? (in % von den Punkten der aktuellen Stufe)

define("AUFTRAG_RUECKZIEH_RETURN", 0.75);        // Wieviel bekommt der User beim abbrechen eines Auftrag zurück?

define("FORSCHUNG_MIN_DAUER", 7200);            // Wie lange dauert jede Forschung mindestens?

define("MAX_ANZAHL_GRUPPENMITGLIEDER", 15);    // Wie viele Mitglieder kann eine Gruppe maximal haben?

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
$Currency = "&euro;";        // Währung als HTML-kodiertes Zeichen
$CurrencyC = "€";                // Währung als direktes Zeichen

// Hier wird der Titel der Seite anhand des Namens der Unterseite generiert
if ($_GET['p'] != "")
    $Titel = str_replace("ae", "&auml;", explode("_", $_GET['p']));
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
$Plantage->BasisKosten = 260;
$Plantage->BasisDauer = 1780;
$Plantage->BasisPunkte = 120;
$Plantage->KostenFaktor = 1.35;
$Plantage->DauerFaktor = 1.25;
$Plantage->PunkteFaktor = 1.23;

$Forschungszentrum->BasisKosten = 320;
$Forschungszentrum->BasisDauer = 1900;
$Forschungszentrum->BasisPunkte = 105;
$Forschungszentrum->KostenFaktor = 1.37;
$Forschungszentrum->DauerFaktor = 1.28;
$Forschungszentrum->PunkteFaktor = 1.20;

$Bioladen->BasisKosten = 260;
$Bioladen->BasisDauer = 1800;
$Bioladen->BasisPunkte = 90;
$Bioladen->KostenFaktor = 1.35;
$Bioladen->DauerFaktor = 1.27;
$Bioladen->PunkteFaktor = 1.20;

$Doenerstand->BasisKosten = 310;
$Doenerstand->BasisDauer = 2150;
$Doenerstand->BasisPunkte = 115;
$Doenerstand->KostenFaktor = 1.38;
$Doenerstand->DauerFaktor = 1.27;
$Doenerstand->PunkteFaktor = 1.21;

$Bauhof->BasisKosten = 620;
$Bauhof->BasisDauer = 2250;
$Bauhof->BasisPunkte = 235;
$Bauhof->KostenFaktor = 1.40;
$Bauhof->DauerFaktor = 1.29;
$Bauhof->PunkteFaktor = 1.22;

$Schule->BasisKosten = 300;
$Schule->BasisDauer = 2050;
$Schule->BasisPunkte = 110;
$Schule->KostenFaktor = 1.39;
$Schule->DauerFaktor = 1.29;
$Schule->PunkteFaktor = 1.19;

$Zaun->BasisKosten = 650;
$Zaun->BasisDauer = 2800;
$Zaun->BasisPunkte = 285;
$Zaun->KostenFaktor = 1.45;
$Zaun->DauerFaktor = 1.33;
$Zaun->PunkteFaktor = 1.17;

$Pizzeria->BasisKosten = 650;
$Pizzeria->BasisDauer = 2800;
$Pizzeria->BasisPunkte = 285;
$Pizzeria->KostenFaktor = 1.45;
$Pizzeria->DauerFaktor = 1.33;
$Pizzeria->PunkteFaktor = 1.17;

/*
    Ende der Basisdaten für die Gebäude
*/

$Produktion->BasisMenge = 350;        // in kg	 \
$Produktion->BasisKosten = 200;        // in €			=> Die ganzen Basisdaten für die Produktion
$Produktion->BasisDauer = 3600;        // in sek	 /

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
