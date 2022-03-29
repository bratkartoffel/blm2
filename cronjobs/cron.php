<?php
/**
 * Da dieser Cronjob regelmäßig ausgeführt wird (Standard: 30 Minuten), werden hier das Einkommen, die Zinsen und die Platzierung des Spiels auf den Voteseiten abgehandelt.
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.cronjobs
 */

require("../include/config.inc.php");
require("../include/functions.inc.php");

ConnectDB();
error_reporting(0);

/**
 * Hilfsfunktion: Überprüft, ob der Benutzer sein Einkommen und Zinsen bekommt und verbucht diese auch direkt
 *
 * @param double $ZinsenKredit
 * @param double $ZinsenAnlage
 *
 * @return void
 **@version 1.0.0
 *
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 */
function EinkommenBankCheck($ZinsenKredit, $ZinsenAnlage)
{
    $sql_abfrage = "SELECT
    m.ID,
    m.Bank,
    s.AusgabenZinsen,
    s.EinnahmenZinsen,
    g.Gebaeude3,
    g.Gebaeude4,
    m.Geld,
    s.EinnahmenGebaeude,
    m.Punkte
FROM
    (
        (
            mitglieder m NATURAL JOIN gebaeude g
        ) NATURAL JOIN forschung f
    ) NATURAL JOIN statistik s
ORDER BY
    ID ASC;";
    $sql_ergebnis = mysql_query($sql_abfrage);

    while ($user = mysql_fetch_object($sql_ergebnis)) {
        /*
            Zuerst werden die Zinsen abgearbeitet
        */

        if ($user->Bank < 0) {        // Kontostand ist < 0 also muss er Kreditzinsen zahlen
            $Zinsen = $user->Bank * ($ZinsenKredit - 1);
            $user->AusgabenZinsen += ($Zinsen * -1);
            $user->Bank += $Zinsen;
        } else {        // Er hat ein positives Guthaben auf dem Konto, also bekommt er Anlagezinsen
            $Zinsen = $user->Bank * ($ZinsenAnlage - 1);
            if ($user->Punkte < 100000) {
                if ($user->Bank + $Zinsen < 100000) {
                    $user->EinnahmenZinsen += $Zinsen;
                    $user->Bank += $Zinsen;
                } else {
                    $user->EinnahmenZinsen += (100000 - $user->Bank);
                    $user->Bank = 99999.99;
                }
            } else {
                if ($user->Bank + $Zinsen < $user->Punkte) {
                    $user->EinnahmenZinsen += $Zinsen;
                    $user->Bank += $Zinsen;
                } else {
                    $user->EinnahmenZinsen += ($user->Punkte - $user->Bank);
                    $user->Bank = $user->Punkte;
                }
            }
        }

        $einkommen = (EINKOMMEN_BASIS + ($user->Gebaeude3 * EINKOMMEN_BIOLADEN_BONUS) + ($user->Gebaeude4 * EINKOMMEN_DOENERSTAND_BONUS));        // Dann das Einkommen ausrechnen

        $user->Geld += $einkommen;
        $user->EinnahmenGebaeude += $einkommen;

        $sql_abfrage = "UPDATE
    mitglieder m NATURAL JOIN statistik s
SET
    m.Bank=" . $user->Bank . ",
    s.AusgabenZinsen=" . $user->AusgabenZinsen . ",
    s.EinnahmenZinsen=" . $user->EinnahmenZinsen . ",
    m.Geld=" . $user->Geld . ",
    s.EinnahmenGebaeude=" . $user->EinnahmenGebaeude . "
WHERE
    m.ID=" . $user->ID . ";";
        mysql_query($sql_abfrage);
    }
}

EinkommenBankCheck($ZinsenKredit, $ZinsenAnlage);
