<?php
/**
 * Wird in die index.php eingebunden; Verwaltung von diplomatischen Beziehungen in der Gruppe
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Diplomatische Beziehungen
                <a href="./?p=hilfe&amp;mod=1&amp;cat=23"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Gruppe && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>
    <?php
    $sql_abfrage = "SELECT
    *
FROM
    gruppe
WHERE
    ID='" . intval($ich->Gruppe) . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $gruppe = mysql_fetch_object($sql_ergebnis);

    if ($ich->Rechte->Diplomatie) {
        ?>
        <div style="width: 650px; text-align: center; margin-bottom: 5px;">
            <a href="./?p=gruppe&amp;<?= time(); ?>">Board</a> |
            <a href="./?p=gruppe_mitgliederverwaltung&amp;<?= time(); ?>">Mitgliederverwaltung</a>
            <?php
            if ($ich->Rechte->GruppeBeschreibung || $ich->Rechte->GruppeBild || $ich->Rechte->GruppePasswort || $ich->Rechte->GruppeLoeschen) {
                echo ' | <a href="./?p=gruppe_einstellungen&amp;' . time() . '">Einstellungen</a>';
            }

            if ($ich->Rechte->Diplomatie) {
                echo ' | <u><b>Diplomatie (' . NeueGruppenDiplomatie($ich) . ')</b></u>';
            }
            ?>
            | <a href="./?p=gruppe_kasse&amp;<?= time(); ?>">Gruppenkasse</a>
            | <a href="./?p=gruppe_logbuch&amp;<?= time(); ?>">Logbuch</a>
        </div>

        <h3>Nicht-Angriffs-Pakte (NAPs):</h3>
        <table class="Liste" cellspacing="0" style="width: 650px;">
            <tr>
                <th>Partner</th>
                <th>Gültig von</th>
                <th>mind. Gültig bis</th>
                <th>Aktion</th>
            </tr>
            <?php
            $sql_abfrage = "SELECT
    g.ID,
    g.Name,
    d.Seit,
    d.Bis,
    d.ID AS vID,
    d.Seit
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID=d.An
WHERE
    d.Von='" . $ich->Gruppe . "'
AND
    d.Typ='1';";
            $sql_ergebnis = mysql_query($sql_abfrage);

            if (mysql_num_rows($sql_ergebnis) > 0) {
                while ($nap = mysql_fetch_assoc($sql_ergebnis)) {
                    $Beziehung[] = $nap["ID"];
                    echo '<tr>
								<td>
									<a href="./?p=gruppe&amp;id=' . $nap["ID"] . '">' . htmlentities(stripslashes($nap["Name"]), ENT_QUOTES, "UTF-8") . '</a>
								</td>
							';
                    if (intval($nap["Seit"]) == 0) {
                        echo '<td colspan="2" style="text-align: center;">- Noch nicht gültig -</td>';
                        echo '<td><a href="actions/gruppe.php?a=13&amp;id=' . $nap["vID"] . '">Zurückziehen</a></td>';
                    } else {
                        echo '<td>' . date("d.m.Y H:i", $nap["Seit"]) . '</td>
								<td>' . date("d.m.Y H:i", $nap["Bis"]) . '</td>';
                        if (intval($nap["Bis"]) >= time()) {
                            echo '<td><i>- Keine Aktion möglich -</i></td>';
                        } else {
                            echo '<td><a href="actions/gruppe.php?a=17&amp;id=' . $nap["vID"] . '">Kündigen</a></td>';
                        }
                    }

                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" style="text-align: center;"><i>- Bisher wurden keine diplomatischen Beziehungen dieser Art eingetragen -</i></td></tr>';
            }
            ?>
        </table>

        <h3>Bündnisse (BNDs):</h3>
        <table class="Liste" cellspacing="0" style="width: 650px;">
            <tr>
                <th>Partner</th>
                <th>Gültig von</th>
                <th>mind. Gültig bis</th>
                <th>Aktion</th>
            </tr>
            <?php
            $sql_abfrage = "SELECT
    g.ID,
    g.Name,
    d.Seit,
    d.Bis,
    d.ID AS vID,
    d.Seit
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID=d.An
WHERE
    d.Von='" . $ich->Gruppe . "'
AND
    d.Typ='2';";
            $sql_ergebnis = mysql_query($sql_abfrage);

            if (mysql_num_rows($sql_ergebnis) > 0) {
                while ($bnd = mysql_fetch_assoc($sql_ergebnis)) {
                    $Beziehung[] = $bnd["ID"];
                    echo '<tr>
								<td>
									<a href="./?p=gruppe&amp;id=' . $bnd["ID"] . '">' . htmlentities(stripslashes($bnd["Name"]), ENT_QUOTES, "UTF-8") . '</a>
								</td>
							';
                    if (intval($bnd["Seit"]) == 0) {
                        echo '<td colspan="2" style="text-align: center;">- Noch nicht gültig -</td>';
                        echo '<td><a href="actions/gruppe.php?a=13&amp;id=' . $bnd["vID"] . '">Zurückziehen</a></td>';
                    } else {
                        echo '<td>' . date("d.m.Y H:i", $bnd["Seit"]) . '</td>
								<td>' . date("d.m.Y H:i", $bnd["Bis"]) . '</td>';
                        if (intval($bnd["Bis"]) >= time()) {
                            echo '<td><i>- Keine Aktion möglich -</i></td>';
                        } else {
                            echo '<td><a href="actions/gruppe.php?a=17&amp;id=' . $bnd["vID"] . '">Kündigen</a></td>';
                        }
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" style="text-align: center;"><i>- Bisher wurden keine diplomatischen Beziehungen dieser Art eingetragen -</i></td></tr>';
            }
            ?>
        </table>

        <h3>Kriege:</h3>
        <table class="Liste" cellspacing="0" style="width: 650px;">
            <tr>
                <th>Gegner</th>
                <th>Gültig von</th>
                <th>Aktion</th>
            </tr>
            <?php
            $sql_abfrage = "SELECT
    g.ID,
    g.Name,
    d.Seit,
    d.ID AS vID,
    d.Seit
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID=d.An
WHERE
    d.Von='" . $ich->Gruppe . "'
AND
    d.Typ='3';";
            $sql_ergebnis = mysql_query($sql_abfrage);

            if (mysql_num_rows($sql_ergebnis) > 0) {
                while ($krieg = mysql_fetch_assoc($sql_ergebnis)) {
                    $Beziehung[] = $krieg["ID"];
                    echo '<tr>
								<td>
									<a href="./?p=gruppe&amp;id=' . $krieg["ID"] . '">' . htmlentities(stripslashes($krieg["Name"]), ENT_QUOTES, "UTF-8") . '</a>
								</td>
							';
                    if (intval($krieg["Seit"]) == 0) {
                        echo '<td style="text-align: center;">- Noch nicht gültig -</td>';
                        echo '<td><a href="actions/gruppe.php?a=13&amp;id=' . $krieg["vID"] . '">Zurückziehen</a></td>';
                    } else {
                        echo '<td>' . date("d.m.Y H:i", $krieg["Seit"]) . '</td>
								<td><a href="./?p=gruppe_krieg_details&amp;id=' . $krieg["vID"] . '&amp;' . time() . '">Details</a></td>';
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" style="text-align: center;"><i>- Bisher wurden keine diplomatischen Beziehungen dieser Art eingetragen -</i></td></tr>';
            }
            ?>
        </table>

        <table class="Liste" cellspacing="0" style="width: 650px; margin-top: 20px;">
            <tr>
                <th>
                    Eine neue Beziehung eintragen
                </th>
            </tr>
            <tr>
                <td>
                    <form action="actions/gruppe.php" method="post">
                        <input type="hidden" name="a" value="12"/>
                        <select name="typ" onchange="CheckKrieg(this);">
                            <option value="1">Nichtangriffspakt</option>
                            <option value="2">Bündnis</option>
                            <option value="3">Krieg</option>
                        </select>
                        mit
                        <select name="partner">
                            <?php
                            $sql_abfrage = "SELECT
    ID,
    Name
FROM
    gruppe
WHERE
    ID NOT IN (SELECT An FROM gruppe_diplomatie WHERE Von=" . $ich->Gruppe . ")
AND
    ID != " . $ich->Gruppe . "
ORDER BY
    Name;";
                            $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());

                            if (mysql_num_rows($sql_ergebnis) > 0) {
                                while ($gruppe = mysql_fetch_assoc($sql_ergebnis)) {
                                    echo '<option value="' . $gruppe["ID"] . '">' . htmlentities(stripslashes($gruppe["Name"]), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                                }
                            } else {
                                echo '<option disabled="disabled" selected="selected">Es gibt keine anderen Gruppen :)</option>';
                            }
                            ?>
                        </select>
                        <span id="krieg" style="display: none; float: left;">
					<b>um</b>
					<input type="text" name="betrag" value="100.000" size="7"/> €
				</span>
                        <input type="submit" value="Eintragen" style="margin-left: 10px;"/>
                    </form>
                </td>
            </tr>
        </table>
        <table class="Liste" cellspacing="0" style="width: 650px; margin-top: 20px;">
            <tr>
                <th colspan="3">
                    Wartende fremde Anfragen
                </th>
            </tr>
            <tr>
                <th>Typ</th>
                <th>Gruppe</th>
                <th>Aktion</th>
            </tr>
            <?php
            $Typ[1] = "NAP";
            $Typ[2] = "BND";
            $Typ[3] = "Krieg";

            $sql_abfrage = "SELECT
    d.*,
    g.Name
FROM
    gruppe_diplomatie d JOIN gruppe g ON d.Von=g.ID
WHERE
    d.An='" . $ich->Gruppe . "'
AND
    d.Seit IS NULL;";
            $sql_ergebnis = mysql_query($sql_abfrage);

            if (mysql_num_rows($sql_ergebnis) > 0) {
                while ($anfrage = mysql_fetch_object($sql_ergebnis)) {
                    echo '<tr>
								<td>' . $Typ[$anfrage->Typ];
                    if ($anfrage->Typ == 3) {
                        echo " (" . number_format($anfrage->Betrag, 2, ",", ".") . " €)";
                    }
                    echo '</td>
								<td><a href="./?p=gruppe&amp;id=' . $anfrage->Von . '">' . $anfrage->Name . '</a></td>
								<td>
									<a href="actions/gruppe.php?a=14&amp;id=' . $anfrage->ID . '">Annehmen</a> | 
									<a href="actions/gruppe.php?a=15&amp;id=' . $anfrage->ID . '">Ablehnen</a>
								</td>
							</tr>';
                }
            } else {
                echo '<tr><td colspan="3" style="text-align: center;"><i>- Keine Anfragen vorhanden -</i></td></tr>';
            }
            ?>
        </table>
        <?php
    }
}
