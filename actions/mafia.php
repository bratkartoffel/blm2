<?php
/**
 * Führt die Aktionen des Benutzers bei der Mafia aus
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.actions
 */

// Zuerst mal die Konfigurationsdateien und die Funktionen einbinden
include("../include/config.inc.php");
include("../include/functions.inc.php");

if (!IstAngemeldet()) {        // Wer nicht ngemeldet ist, hat auch keine Mafia ;=)
    header("location: ../?p=index&m=102");
    die();
}

if (SPECIAL_RUNNING) {            // Das Weihnachtsspecial läuft, keine Angriffe möglich
    header("location: ../?p=index&m=137");
    die();
}

ConnectDB();        // Verbindung mit der Datenbank aufbauen

/*
    Nachfolgend die Kosten für die verschiedenen Aktionen und Stufen
*/
$kosten[1][1] = 200;
$kosten[1][2] = 400;
$kosten[1][3] = 600;
$kosten[1][4] = 800;

$kosten[2][1] = 400;
$kosten[2][2] = 800;
$kosten[2][3] = 1200;
$kosten[2][4] = 1600;

$kosten[3][1] = 1000;
$kosten[3][2] = 2500;
$kosten[3][3] = 4000;
$kosten[3][4] = 6500;

$kosten[4][1] = 300;
$kosten[4][2] = 600;
$kosten[4][3] = 900;
$kosten[4][4] = 1200;
/*
    Ende der Kosten
*/

$gegner = intval($_GET['gegner']);        // Wer ist das Opfer?
$a = intval($_GET['a']);                            // Was soll ihm angetan werden?
$ich = LoadSettings();                                // Alle Daten des Users abrufen (Geld, Gebäudelevel, Forschungslevel...)

if ($_SESSION['blm_sitter']) {
    $ich->Sitter = LoadSitterSettings();
}

if (!$ich->Sitter->Mafia && $_SESSION['blm_sitter']) {
    DisconnectDB();
    header("location: ../?p=mafia&m=112&" . time());
    die();
}

$sql_abfrage = "
SELECT
    ID,
    Name,
    Gebaeude7,
    Gruppe
FROM
    (mitglieder m NATURAL JOIN gebaeude) NATURAL JOIN punkte p
WHERE
(
        m.ID = '" . $gegner . "'
    AND
        Punkte > 7000
    AND
        Punkte >= " . ($ich->Punkte / MAFIA_FAKTOR_MIN_PUNKTE) . "
    AND
        Punkte <= " . ($ich->Punkte * MAFIA_FAKTOR_MAX_PUNKTE) . "
    AND
        Gruppe IS NULL
)
UNION SELECT
    ID,
    Name,
    Gebaeude7,
    Gruppe
FROM
    mitglieder m NATURAL JOIN gebaeude
WHERE
(
        m.ID = '" . $gegner . "'
    AND
        Punkte > 7000
    AND
        Punkte >= " . ($ich->Punkte / MAFIA_FAKTOR_MIN_PUNKTE) . "
    AND
        Punkte <= " . ($ich->Punkte * MAFIA_FAKTOR_MAX_PUNKTE) . "
    AND
        Gruppe IS NOT NULL
";

if ($ich->Gruppe != NULL) {
    $sql_abfrage .= "AND
		Gruppe <> " . $ich->Gruppe . "
	";
}

if (count($ich->GruppeBND) > 0) {
    foreach ($ich->GruppeBND as $bnd) {
        $sql_abfrage .= "AND
		Gruppe <> " . $bnd . "
	";
    }
}

if (count($ich->GruppeNAP) > 0) {
    foreach ($ich->GruppeNAP as $nap) {
        $sql_abfrage .= "AND
		Gruppe <> " . $nap . "
	";
    }
}

$sql_abfrage .= "
)
";

if (count($ich->GruppeKriege) > 0) {
    $sql_abfrage .= "OR
	Gruppe IN (" . implode(", ", $ich->GruppeKriege) . ")
;";
}

$sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());
while ($opfer = mysql_fetch_object($sql_ergebnis)) {
    if ($opfer->ID == $gegner) {
        break;
    }
}

if (intval($opfer->ID) == 0) {
    DisconnectDB();
    header("location: ../?p=mafia&m=112&" . intval(time()));
    die();
}

srand(1337 + time() + microtime() * 1000000);    // Zufallszahlengenerator anwerfen
$rand = rand(1, 100);                                            // Zahl zwischen 1 und 100 generieren, entscheidet über Erfolg / Misserfolg

switch ($a)        // Was will der Benutzer, dementsprechend das Level des jeweiligen Angriffs abrufen
{
    case 1:        // Spionage
        $w = intval($_GET['spionage']);
        break;
    case 2:        // Diebstahl
        $w = intval($_GET['diebstahl']);
        break;
    case 3:        // Bomben
        $w = intval($_GET['angriff']);
        break;
    case 4:        // Angriff
        $w = intval($_GET['bargeld_angriff']);
        break;
}

if ($ich->Geld < $kosten[$a][$w]) {        // Der Benutzer hat nicht genügend Geld...
    DisconnectDB();
    header("location: ../?p=mafia&m=111&" . intval(time()));
    die();
}

if ($ich->LastMafia + 600 > time()) {        // Der Timeout für Angriffe ist noch nicht abgelaufen!
    DisconnectDB();
    header("location: ../?p=mafia&m=112&" . intval(time()));
    die();
}

$ChancenBonus = $ich->Gebaeude8 * BONUS_FAKTOR_PIZZERIA - $opfer->Gebaeude7 * BONUS_FAKTOR_ZAUN;

if ($a != 3) {        // Bei allen Angriffen, ausser dem Bomben gelten folgende Chancen:
    switch ($w) {
        case 1:        // 20 %
            if ($rand <= (20 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 2:        // 30 %
            if ($rand <= (30 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 3:        // 40 %
            if ($rand <= (40 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 4:        // 50 %
            if ($rand <= (50 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        default:
            $success = false;
            break;
    }
} else {        // Die Erfolgschancen für das Bomben
    switch ($w) {
        case 1:        // 10 %
            if ($rand <= (10 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 2:        // 18 %
            if ($rand <= (18 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 3:        // 26 %
            if ($rand <= (26 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        case 4:        // 32 %
            if ($rand <= (32 + $ChancenBonus)) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        default:
            $success = false;
            break;
    }
}

switch ($a)        // Wieder mal schauen, was der Angreifer will
{
    case 1:        // Spionage
        $punkte = MAFIA_PUNKTE_SPIONAGE;
        $betreff = "Mafia: Spionage";        // Betreff für die IGM an den Angreifer
        $betreff .= " gegen " . htmlentities(stripslashes($opfer->Name), ENT_QUOTES, "UTF-8");

        if ($success) {        // Ja, der Angriff war erfolgreich!
            $sql_abfrage = "SELECT
    m.Geld AS Geld,
    l.*,
    g.*
FROM
    (lagerhaus l NATURAL JOIN mitglieder m) NATURAL JOIN gebaeude g
WHERE
    m.ID = '" . $gegner . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Den Inhalt des Lagerhauses und des Geldbeutels ermitteln
            $_SESSION['blm_queries']++;

            $lager = mysql_fetch_object($sql_ergebnis);        // Hier stehen alle wichtigen Daten über den Gegner drinnen

            /*
                Nachfolgend die Nachricht, wie sie der Angreifer erhält mit allen wichtigen Daten des Gegners
            */
            $nachricht = "Die Spionage war erfolgreich. Hier die Daten des Gegners:\n\n";
            $nachricht .= "Geld: " . number_format($lager->Geld, 2, ", ", ".") . " " . $CurrencyC . "\n\n";
            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                $temp = "Lager" . $i;
                $nachricht .= Warenname($i, true) . ": " . $lager->$temp . " kg\n";
            }
            for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
                $temp = "Gebaeude" . $i;
                $nachricht .= GebaeudeName($i) . ": " . $lager->$temp . "\n";
            }
            $nachricht .= "\n[i]Ihre Mafia[/i]";
            /*
                Das war die Nachricht
            */
        } else { // Die Spionage war leider ein Fehlschlag... :(
            $nachricht = "Hallo Chef,\n\nUnsere Spionage war leider [b]nicht[/b] erfolgreich... Die gegnerischen Wachen haben unsere Spitzel erkannt bevor diese irgendwelche relevanten Daten sammeln konnten...\n\n[i]Ihre Mafia[/i]";        // Nachricht an den Angreifer, dass was nicht funktioniert hat wie es sollte ;)
        }

        $sperr_dauer = MAFIA_SPERRZEIT_SPIONAGE;

        if (is_array($ich->GruppeKriege)) {
            if (in_array($opfer->Gruppe, $ich->GruppeKriege)) {
                $sperr_dauer = 0.5 * MAFIA_SPERRZEIT_SPIONAGE;
            }
        }

        break;
    case 2:        // Diebstahl
        $punkte = MAFIA_PUNKTE_DIEBSTAHL;
        $betreff = "Mafia: Diebstahl";        // Betreffzeile der Nachricht an den Angreifer
        $betreff .= " gegen " . htmlentities(stripslashes($opfer->Name), ENT_QUOTES, "UTF-8");

        if ($success) {        // Hehe, der Diebstahl wird erfolgreich sein... ;)
            $sql_abfrage = "SELECT
    m.Geld AS Geld,
    l.*,
    g.*
FROM
    (lagerhaus l NATURAL JOIN mitglieder m) NATURAL JOIN gebaeude g
WHERE
    m.ID = '" . $gegner . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);        // Den Inhalt des Lagerhauses und des Geldbeutels ermitteln
            $_SESSION['blm_queries']++;

            $lager = mysql_fetch_object($sql_ergebnis);        // Hier stehen alle wichtigen Daten über den Gegner drinnen

            /*
                Nachfolgend die Nachricht, wie sie der Angreifer erhält mit allen wichtigen Daten des Gegners
            */
            $nachricht = "Hallo Chef,\n\nDer Diebstahl war [b]erfolgreich[/b], es wurden alle Waren gestohlen. Nachfolgend eine Liste:\n\n";
            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                $temp = "Lager" . $i;
                $nachricht .= str_replace("&Auml;", "Ä", Warenname($i)) . ": " . $lager->$temp . " kg\n";
            }
            $nachricht .= "\nDie Waren befinden sich schon in Ihrem Lager.\n\n";
            $nachricht .= "[i]Ihre Mafia[/i]";

            $sql_abfrage = "UPDATE lagerhaus SET ";
            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                $temp = "Lager" . $i;
                $sql_abfrage .= $temp . "='0', ";
            }
            $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $gegner . "';";
            mysql_query($sql_abfrage);        // Dem Gegner alle Waren klauen......
            $_SESSION['blm_queries']++;

            $sql_abfrage = "UPDATE lagerhaus SET ";
            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                $temp = "Lager" . $i;
                $sql_abfrage .= $temp . "=" . $temp . "+" . $lager->$temp . ", ";
            }
            $sql_abfrage = substr($sql_abfrage, 0, -2) . " WHERE ID='" . $_SESSION['blm_user'] . "';";
            mysql_query($sql_abfrage);        // ...... und uns zu Gute schreiben :)
            $_SESSION['blm_queries']++;
        } else {    // Der Diebstahl schlug fehl... :(
            $nachricht = "Hallo Chef,\n\nWir konnten Ihren Auftrag leider [b]nicht[/b] erfolgreich abschließen...\nDie gegnerischen Wachen haben unsere Diebe erkannt und sofort festgenommen.\n\n[i]Ihre Mafia[/i]";
        }

        $sperr_dauer = MAFIA_SPERRZEIT_DIEBSTAHL;

        if (is_array($ich->GruppeKriege)) {
            if (in_array($opfer->Gruppe, $ich->GruppeKriege)) {
                $sperr_dauer = 0.5 * MAFIA_SPERRZEIT_DIEBSTAHL;
            }
        }
        break;
    case 3:        // Angriff - Bomben
        $punkte = MAFIA_PUNKTE_BOMBEN;
        $betreff = "Mafia: Bomben";        // Betreff für die IGM an den Angreifer
        $betreff .= " gegen " . htmlentities(stripslashes($opfer->Name), ENT_QUOTES, "UTF-8");
        if ($success) {        // Jaaaa, Feuer frei......
            $sql_abfrage = "SELECT
    g.Gebaeude1 AS Plantage
FROM
    gebaeude g NATURAL JOIN mitglieder m
WHERE
    ID='" . $gegner . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);    // Das Level der gegnerischen Plantage abrufen
            $_SESSION['blm_queries']++;

            $lager = mysql_fetch_object($sql_ergebnis);        // Ja, "lager" ist vielleicht ein unpassender Variablenname, aber Copy-und-Paste geht so einfach ;=)

            if ($lager->Plantage == 1) {        // Ist die Plantage schon auf dem niedrigsten Level?
                $nachricht = "Hallo Chef,\n\nUnser Angriff war leider [b]nicht[/b] erfolgreich...\nUnsere Söldner haben zwar die Plantage in Schutt und Asche gelegt, jedoch befindet sich diese schon auf dem niedrigsten Level...";    // Böser Junge, lass ihm doch wenigstens noch eine Stufe der Plantage ;)
            } else {
                $PunkteSub = MAFIA_PUNKTE_SUB * ($Plantage->BasisPunkte * (pow($Plantage->PunkteFaktor, $lager->Plantage)));        // So viele Punkte werden dem Gegner abgezogen

                $nachricht = "Hallo Chef,\n\nUnser Angriff auf die Plantage des Konkurrenten war [b]erfolgreich[/b]!\nWir konnten das Gebäude von Level " . $lager->Plantage . " auf Level " . ($lager->Plantage - 1) . " bomben. Dadurch hat Ihr Konkurent [b]" . number_format($PunkteSub, 0, "", ".") . "[/b] Punkte verloren!"; // Nachricht verfassen an den Angreifer...

                $sql_abfrage = "UPDATE
    (mitglieder m NATURAL JOIN gebaeude g) NATURAL JOIN punkte p
SET
    g.Gebaeude1=g.Gebaeude1-1,
    m.Punkte=m.Punkte-" . $PunkteSub . ",
    p.MafiaMinus=p.MafiaMinus+" . $PunkteSub . "
WHERE
    m.ID='" . $gegner . "';";
                mysql_query($sql_abfrage);        // Änderungen zurückschreiben
                $_SESSION['blm_queries']++;

                if (is_array($ich->GruppeKriege)) {
                    if (in_array($opfer->Gruppe, $ich->GruppeKriege)) {
                        $sql_abfrage = "UPDATE
    gruppe_diplomatie
SET
    PunktePlus=PunktePlus+" . $PunkteSub . "
WHERE
    (
        Von='" . $ich->Gruppe . "'
    AND
        An='" . $opfer->Gruppe . "'
    AND
        Typ=3
    );";
                        mysql_query($sql_abfrage);
                        $_SESSION['blm_queries']++;

                        $sql_abfrage = "UPDATE
    gruppe_diplomatie
SET
    PunkteMinus=PunkteMinus+" . $PunkteSub . "
WHERE
    (
        An='" . $ich->Gruppe . "'
    AND
        Von='" . $opfer->Gruppe . "'
    AND
        Typ=3
    );";
                        mysql_query($sql_abfrage);
                        $_SESSION['blm_queries']++;
                    }
                }
            }

            $nachricht .= "\n\n[i]Ihre Mafia[/i]";        // Die Nachricht muss ja auch noch nen "Absender" haben ;)
        } else {    // Der Zufallsgenerator sagt nein...
            $nachricht = "Hallo Chef,\n\nUnser Angriff war leider [b]nicht[/b] erfolgreich...\n\nDie gegnerischen Plantage konnte nicht beschädigt werden, da die Wachen unsere Saboteure finden und die Bomben entschärfen konnten.\n\n[i]Ihre Mafia[/i]";
        }

        $sperr_dauer = MAFIA_SPERRZEIT_BOMBEN;

        if (is_array($ich->GruppeKriege)) {
            if (in_array($opfer->Gruppe, $ich->GruppeKriege)) {
                $sperr_dauer = 0.5 * MAFIA_SPERRZEIT_BOMBEN;
            }
        }
        break;
    case 4:        // Bargeldangriff
        $punkte = MAFIA_PUNKTE_ANGRIFF;
        $betreff = "Mafia: Angriff";        // Den Betreff für die IGM an den Angreifer schreiben
        $betreff .= " gegen " . htmlentities(stripslashes($opfer->Name), ENT_QUOTES, "UTF-8");

        if ($success) {    // Jawoll, der Angriff ist erfolgreich
            $diebstahlrate = rand(MAFIA_DIEBSTAHL_MIN_RATE, MAFIA_DIEBSTAHL_MAX_RATE) / 100;        // Wieviel (in %) soll dem Gegner geklaut werden?

            $sql_abfrage = "SELECT
    Geld
FROM
    mitglieder
WHERE
    ID='" . $gegner . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            $lager = mysql_fetch_object($sql_ergebnis);        // Ja, wieder der unpassende Variablenname, ich weiß... Aber da steht jetzt das Geld des Gegners drinnen...

            $GeldGestohlen = round($lager->Geld * $diebstahlrate, 2);        // Wieviel wird dem Gegner nun gestohlen?

            $nachricht = "Hallo Chef,\n\nUnser Diebstahl war [b]erfolgreich[/b]!\n\nAls Ihr Konkurrent mal nicht aufpasste, konnte unser Dieb seine Geldbörse erbeuten. Dabei konnte er " . number_format($GeldGestohlen, 2, ",", ".") . " " . $CurrencyC . " (" . number_format($diebstahlrate * 100, 2, ",", ".") . "%) stehlen!\n";        // Nachricht schreiben

            $nachricht .= "\nWir haben das Geld schon auf Ihr Konto überwiesen.\n\n[i]Ihre Mafia[/i]";    // Ein paar abschließende Worte ;)

            $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld-" . $GeldGestohlen . "
WHERE
    m.ID='" . $gegner . "';";
            mysql_query($sql_abfrage);        // Zuerst dem Gegner das Geld aus der Tasche ziehen...
            $_SESSION['blm_queries']++;

            $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Geld=m.Geld+" . $GeldGestohlen . ",
    s.EinnahmenMafia=s.EinnahmenMafia+" . $GeldGestohlen . "
WHERE
    m.ID=" . $_SESSION['blm_user'] . ";";
            mysql_query($sql_abfrage);            // ... und uns gut schreien :)
            $_SESSION['blm_queries']++;
        } else {        // Satz mit X, das war wohl nichts...
            $nachricht = "Hallo Chef,\n\nDer Diebstahl war leider [b]nicht[/b] erfolgreich...\n\nDer Gegner hat uns beim Stehlen der Brieftasche ertappt...\n\n[i]Ihre Mafia[/i]";
        }

        $sperr_dauer = MAFIA_SPERRZEIT_ANGRIFF;

        if (is_array($ich->GruppeKriege)) {
            if (in_array($opfer->Gruppe, $ich->GruppeKriege)) {
                $sperr_dauer = 0.5 * MAFIA_SPERRZEIT_ANGRIFF;
            }
        }
        break;
}

$sql_abfrage = "UPDATE
    (mitglieder m NATURAL JOIN punkte p) NATURAL JOIN statistik s
SET
    m.Geld=m.Geld-" . $kosten[$a][$w] . ",
    s.AusgabenMafia=s.AusgabenMafia+" . $kosten[$a][$w] . ",
    m.LastMafia='" . (time() + $sperr_dauer - 600) . "',
    m.Punkte=m.Punkte+" . $punkte . ",
    p.MafiaPlus=p.MafiaPlus+" . $punkte . "
WHERE
    m.ID='" . $_SESSION['blm_user'] . "';";
mysql_query($sql_abfrage) or die(mysql_error());        // So, die Ergebnisse der Angriffe sind schon erledigt und gebucht, nun will die Mafia ihr Geld...
$_SESSION['blm_queries']++;

$sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Betreff,
    Nachricht,
    Zeit
)
VALUES
(
    NULL,
    '0',
    '" . $_SESSION['blm_user'] . "',
    '" . $betreff . "',
    '" . $nachricht . "',
    '" . time() . "'
);";
mysql_query($sql_abfrage);        // Hier wird die vorbereitete Nachricht an den Angreifer abgeschickt
$_SESSION['blm_queries']++;

$betreff = "Sicherheitskräfte: Wir wurden angegriffen!";        // Das Opfer will auch noch wissen, dass es angegriffen wurde...
if ($success) {        // Leider war der Angriff erfolgreich
    $nachricht = "Hallo Chef,\n\n[b]wir wurden angegriffen![/b]\nDie Söldner wurden von [b]" . GetUsername($_SESSION['blm_user']) . "[/b] geschickt, und hatten den Auftrag:\n\n[b]" . MafiaAuftragsText($a) . "[/b]\n\nImmerhin haben wir einen von Ihnen fassen können, doch leider ist es uns nicht gelungen, den Angriff zu verhindern... Jedoch konnten wir eine Kopie der Nachricht an den Auftraggeber erlangen.\n[quote][i][b]Nachricht an " . GetUsername($_SESSION['blm_user']) . "[/b][/i]\n" . $nachricht . "\n[/quote]\n\nWir hoffen dass Ihnen oder den Behörden die Nachricht helfen wird.\n\n[i]Ihre Sicherheitsmannschaft[/i]";
} else {        // der Angriff war nicht erfolgreich
    $nachricht = "Hallo Chef,\n\nwir wurden angegriffen! Die Söldner sind von der Mafia. Nach längerem Verhör hat einer der Angreifer ausgepackt und uns den Auftraggeber genannt:\n[b]" . GetUserName($_SESSION['blm_user']) . "[/b].\n\nEr verriet uns auch seinen Auftrag:\n\n[b]" . MafiaAuftragsText($a) . "[/b]\n\nDank unserer hervoragend ausgebildeten Sicherheitskräfte konnten wir den Angriff jedoch abwehren und es konnte [b]kein Schaden[/b] verursacht werden.\n\n[i]Ihre Sicherheitsmannschaft[/i]";
}

$sql_abfrage = "INSERT INTO
    nachrichten
(
    ID,
    Von,
    An,
    Betreff,
    Nachricht,
    Zeit
)
VALUES
(
    NULL,
    '0',
    '" . $gegner . "',
    '" . $betreff . "',
    '" . $nachricht . "',
    '" . time() . "'
);";
mysql_query($sql_abfrage);        // Zum Schluss erfährt auch noch das Opfer, dass es ein Opfer ist ;)
$_SESSION['blm_queries']++;

$sql_abfrage = "INSERT INTO
    log_mafia
(
    Wer,
    Wen,
    Wann,
    Wie,
    Erfolgreich
)
VALUES
(
    '" . $_SESSION['blm_user'] . "',
    '" . $gegner . "',
    NOW(),
    '" . $a . "',
    '" . ($success ? '1' : '0') . "'
);";
mysql_query($sql_abfrage);        // Logbuch
$_SESSION['blm_queries']++;

// Puh, fertig... :)
DisconnectDB();
header("location: ../?p=nachrichten_liste&" . intval(time()));
die();
