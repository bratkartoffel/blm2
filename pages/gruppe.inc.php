<?php
/**
 * Wird in die index.php eingebunden; Gruppenhauptseite
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Gruppe
                <a href="./?p=hilfe&amp;mod=1&amp;cat=23"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if ($_SESSION['blm_sitter'] && !$ich->Sitter->Gruppe) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {

    if (intval($ich->Gruppe) == 0) {
        ?>
        <b>
            Hier können Sie einer bereits bestehenden Gruppe beitreten, oder eine neue gründen.<br/>
            Zum Beitreten benötigen Sie die Plantage auf mindestens Stufe 5,<br/>
            zum gründen müssen Sie Ihre Plantage mindestens auf Stufe 8 haben.
        </b><br/>
        <br/>
        <?php
    }
    ?>
    <?= $m; ?>


    <?php
    if ($ich->Gebaeude1 < 5) {
        echo '<span class="MeldungR" style="font-size: 12pt;">Sie müssen Ihre Plantage mindestens auf Stufe 5 haben, um einer Gruppe beizutreten.</span><br />';
    }

    if (isset($_GET['id']) && intval($_GET['id']) > 0) {
        $id = intval($_GET['id']);
    } else {
        $id = intval($ich->Gruppe);
    }

    if ($id > 0) {
        $sql_abfrage = "SELECT
    *
FROM
    gruppe
WHERE
    ID='" . $id . "';";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $gruppe = mysql_fetch_object($sql_ergebnis);

        if (intval($gruppe->ID) == 0) {
            echo '<script type="text/javascript">
							document.location.href="./?p=gruppe";
						</script>';
        }

        if ($ich->Gruppe == $gruppe->ID) {
            ?>
            <div style="width: 650px; text-align: center; margin-bottom: 5px;">
                <u><b>Board</b></u> |
                <a href="./?p=gruppe_mitgliederverwaltung">Mitgliederverwaltung</a>
                <?php
                if ($ich->Rechte->GruppeBeschreibung || $ich->Rechte->GruppeBild || $ich->Rechte->GruppePasswort || $ich->Rechte->GruppeLoeschen) {
                    echo ' | <a href="./?p=gruppe_einstellungen">Einstellungen</a>';
                }

                if ($ich->Rechte->Diplomatie) {
                    echo ' | <a href="./?p=gruppe_diplomatie">Diplomatie (' . NeueGruppenDiplomatie($ich) . ')</a>';
                }
                ?>
                | <a href="./?p=gruppe_kasse">Gruppenkasse</a>
                | <a href="./?p=gruppe_logbuch">Logbuch</a>
            </div>
            <?php
        }
        ?>
        <table class="Liste" cellspacing="0" style="width: 650px;">
            <tr>
                <th colspan="2" style="font-size: 160%;">
                    Gruppe: <?= htmlentities(stripslashes($gruppe->Name), ENT_QUOTES, "UTF-8"); ?>
                </th>
            </tr>
            <tr>
                <td>
                    <div style="text-align: center;">
                        <img src="pics/gruppe.php?id=<?= $gruppe->ID; ?>" style="max-width: 200px; max-height: 200px"/>
                    </div>
                    <div style="margin-top: 30px;">
                        <?php
                        if ($gruppe->Beschreibung != NULL) {
                            echo ReplaceBBCode(stripslashes($gruppe->Beschreibung));
                        } else {
                            echo '<div style="text-align: center;"><i>Die Gruppe hat keine Beschreibung eingegeben.</i></div>';
                        }
                        ?>
                    </div>
                </td>
                <td style="width: 300px; border-left: 1px dashed #444444; text-align: center;">
                    &sum;<b> Punkte:</b> <?php
                    $sql_abfrage = "SELECT
    SUM(Punkte) AS PunkteG,
    AVG(Punkte) AS PunkteD
FROM
    mitglieder
WHERE
    Gruppe='" . $gruppe->ID . "';";
                    $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());
                    $_SESSION['blm_queries']++;

                    $punkte = mysql_fetch_object($sql_ergebnis);

                    echo number_format($punkte->PunkteG, 0, ",", ".");
                    ?><br/>
                    &empty;<b> Punkte:</b> <?= number_format($punkte->PunkteD, 0, ",", "."); ?><br/>
                    <b> Kontostand:</b> <?= number_format($gruppe->Kasse, 2, ",", ".") . " " . $Currency; ?><br/>
                    <br/>
                    <?php
                    $sql_abfrage = "SELECT
    COUNT(*) AS anzahl
FROM
    mitglieder
WHERE
    Gruppe='" . $gruppe->ID . "';";
                    $sql_ergebnis = mysql_query($sql_abfrage);
                    $_SESSION['blm_queries']++;

                    $anzahl = mysql_fetch_object($sql_ergebnis);
                    ?>
                    <b>Mitglieder (<?= $anzahl->anzahl; ?> / <?= MAX_ANZAHL_GRUPPENMITGLIEDER; ?>):</b><br/>
                    <br/>
                    <ul style="text-align: left; margin-left: 20px;">
                        <?php
                        $sql_abfrage = "SELECT
    m.ID AS ID,
    m.Name AS Name,
    m.GruppeRechte,
    m.Punkte,
    m.LastAction
FROM
    mitglieder m JOIN gruppe g ON m.Gruppe=g.ID
WHERE
    m.Gruppe='" . $id . "'
ORDER BY
    Punkte DESC;";
                        $sql_ergebnis = mysql_query($sql_abfrage);
                        $_SESSION['blm_queries']++;

                        $maximale_rechte = 0;
                        while ($mitglied = mysql_fetch_object($sql_ergebnis)) {
                            echo '<li style="font-size: 90%;">';
                            if ($ich->Gruppe == $id) {
                                if ($mitglied->LastAction + 300 < time()) {
                                    $status = "Offline";
                                } else {
                                    $status = "Online";
                                }
                                echo '<img src="./pics/small/' . $status . '.png" alt="' . $status . '" title="' . $status . '" width="16" height="16" />';
                            }

                            echo '<a href="./?p=profil&amp;uid=' . $mitglied->ID . '">' . htmlentities(stripslashes($mitglied->Name), ENT_QUOTES, "UTF-8") . " (" . intval($mitglied->Punkte) . ")</a></li>";

                            if ($maximale_rechte < $mitglied->GruppeRechte) {
                                $maximale_rechte = $mitglied->GruppeRechte;
                                $maximale_rechte_user = $mitglied->ID;
                            }
                        }
                        ?>
                    </ul>
                    <br/>
                    <br/>
                    <b>NAPs:</b><br/>
                    <br/>
                    <?php
                    $sql_abfrage = "SELECT
    g.ID,
    g.Kuerzel AS Name,
    d.An
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID =d.An
WHERE
    d.Von='" . $gruppe->ID . "'
AND
    d.Seit IS NOT NULL
AND
    d.Typ=1
ORDER BY
    g.Name ASC;";
                    $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());

                    if (mysql_num_rows($sql_ergebnis) > 0) {
                        echo '<ul style="text-align: left; margin-left: 20px;">';
                        while ($nap = mysql_fetch_assoc($sql_ergebnis)) {
                            echo '<li><a href="./?p=gruppe&amp;id=' . $nap["An"] . '">' . htmlentities(stripslashes($nap["Name"]), ENT_QUOTES, "UTF-8") . '</a></li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<i>- Keine -</i>';
                    }
                    ?>
                    <br/>
                    <br/>
                    <b>Bündnisse:</b><br/>
                    <br/>
                    <?php
                    $sql_abfrage = "SELECT
    g.ID,
    g.Kuerzel AS Name,
    d.An
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID =d.An
WHERE
    d.Von='" . $gruppe->ID . "'
AND
    d.Seit IS NOT NULL
AND
    d.Typ=2
ORDER BY
    g.Name ASC;";
                    $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());

                    if (mysql_num_rows($sql_ergebnis) > 0) {
                        echo '<ul style="text-align: left; margin-left: 20px;">';
                        while ($bnd = mysql_fetch_assoc($sql_ergebnis)) {
                            echo '<li><a href="./?p=gruppe&amp;id=' . $bnd["An"] . '">' . htmlentities(stripslashes($bnd["Name"]), ENT_QUOTES, "UTF-8") . '</a></li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<i>- Keine -</i>';
                    }
                    ?>
                    <br/>
                    <br/>
                    <b>Kriege:</b><br/>
                    <br/>
                    <?php
                    $sql_abfrage = "SELECT
    g.ID,
    g.Kuerzel AS Name,
    d.An
FROM
    gruppe g JOIN gruppe_diplomatie d ON g.ID =d.An
WHERE
    d.Von='" . $gruppe->ID . "'
AND
    d.Seit IS NOT NULL
AND
    d.Typ=3
ORDER BY
    g.Name ASC;";
                    $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());

                    if (mysql_num_rows($sql_ergebnis) > 0) {
                        echo '<ul style="text-align: left; margin-left: 20px;">';
                        while ($krieg = mysql_fetch_assoc($sql_ergebnis)) {
                            echo '<li><a href="./?p=gruppe&amp;id=' . $krieg["An"] . '">' . htmlentities(stripslashes($krieg["Name"]), ENT_QUOTES, "UTF-8") . '</a></li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<i>- Keine -</i>';
                    }

                    if ($ich->Gruppe == $gruppe->ID) {
                        ?>
                        <br/>
                        <br/>
                        <h2>Aktion:</h2>
                        <form action="actions/gruppe.php" method="post">
                            <input type="hidden" name="a" value="3"/>
                            <input type="submit" value="Gruppe verlassen"
                                   onclick="return confirm('Wollen Sie diese Gruppe wirklich verlassen?');"/>
                        </form>
                        <?php
                    }
                    else {
                    if (intval($ich->Gruppe) == 0) {
                    ?>
                    <br/>
                    <br/>
                    <h2>Aktion:</h2>
                    <?php
                    if ($anzahl->anzahl < MAX_ANZAHL_GRUPPENMITGLIEDER) {
                    if ($ich->Gebaeude1 >= 5) {
                    ?>
                    <a href="./?p=nachrichten_schreiben&amp;an=<?= $maximale_rechte_user; ?>&amp;betreff=Bewerbung+bei+der+Gruppe&amp;nachricht=<?= htmlentities(urlencode("Hallo, \nich würde gerne in deine Gruppe. Wäre nett, wenn du mir das Passwort sagen kannst.\n\nGruß,\n" . stripslashes($ich->Name)), ENT_QUOTES, "UTF-8"); ?>">Bewerben</a><br/><a
                            href="./?p=gruppe&amp;gruppe=<?= htmlentities(stripslashes($gruppe->Name), ENT_QUOTES, "UTF-8"); ?>">Beitreten
                        <?php
                        }
                        else {
                            ?>
                            <i>- Keine Aktion möglich -</i><br/>
                            (Plantage unter Stufe 5)
                            <?php
                        }
                        }
                        else {
                            ?>
                            <i>- Keine Aktion möglich -</i><br/>
                            (Gruppe ist voll)
                            <?php
                        }
                        }
                        }

                        ?>
                </td>
            </tr>
        </table>
        <?php
        if ($ich->Gruppe == $gruppe->ID) {
            if ($ich->Rechte->NachrichtSchreiben) {
                ?>
                <form action="actions/gruppe.php" method="post">
                    <input type="hidden" name="a" value="4"/>
                    <table class="Liste" style="width: 400px; margin-left: 50px; margin-top: 20px;">
                        <tr>
                            <th>
                                Neue Nachricht schreiben
                            </th>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <textarea name="nachricht" cols="40" rows="10"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <input type="submit" value="Schreiben"
                                       onclick="document.forms[1].submit(); this.disabled='disabled'; this.value='Bitte warten...'; return false;"/>
                            </td>
                        </tr>
                    </table>
                </form>
                <?php
            }

            $sql_abfrage = "SELECT
    COUNT(*) AS anzahl
FROM
    gruppe_nachrichten
WHERE
    Gruppe='" . $ich->Gruppe . "';";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            $anzahl = mysql_fetch_object($sql_ergebnis);
            $anzahl_nachrichten = $anzahl->anzahl;

            $offset = isset($_GET['o']) ? intval($_GET['o']) : 0;        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
            // Dabei berechnet sich der Starteintrag aus $offset*RANGLISTE_OFFSET

            if (GRUPPE_OFFSET * $offset > $anzahl_nachrichten) {        // Will er das Offset höher setzen, als es Spieler gibt?
                $offset = intval($anzahl_nachrichten / GRUPPE_OFFSET);        // Wenn ja, dann setz das Offset auf den letzmöglichen Wert
            }

            if ($offset < 0) {        // Ist das Offset negativ?
                $offset = 0;            // ... dann setz es auf Standard
            }

            $sql_abfrage = "SELECT
    g.ID AS nID,
    g.*,
    m.Name,
    m.ID AS mID
FROM
    gruppe_nachrichten g LEFT OUTER JOIN mitglieder m ON m.ID=g.Von
WHERE
    g.Gruppe='" . $ich->Gruppe . "'
ORDER BY
    Zeit DESC
LIMIT " . ($offset * GRUPPE_OFFSET) . ", " . GRUPPE_OFFSET . ";";
            $sql_ergebnis = mysql_query($sql_abfrage);
            $_SESSION['blm_queries']++;

            while ($nachricht = mysql_fetch_object($sql_ergebnis)) {
                echo '<table class="Liste" style="width: 400px; margin-top: 20px; margin-left: 50px;" id="gn_' . $nachricht->nID . '">
								<tr>
									<th>
										';
                if ($ich->Rechte->NachrichtLoeschen) {
                    echo '<div style="float: right;">
											<a href="actions/gruppe.php?a=5&amp;id=' . $nachricht->nID . '" onclick="delGruppeNachricht(' . $nachricht->nID . ', document.getElementById(\'gn_' . $nachricht->nID . '\')); return false;">
												<img src="pics/small/error.png" style="border: none;" />
											</a>
										</div>';
                }
                echo 'Von <b><a href="./?p=profil&amp;uid=' . $nachricht->mID . '">' . htmlentities(stripslashes($nachricht->Name), ENT_QUOTES, "UTF-8") . '</a></b>
										am <b>' . date("d.m.Y", $nachricht->Zeit) . '</b>
										um <b>' . date("H:i:s", $nachricht->Zeit) . '</b>
									</th>
								</tr>
								<tr>
									<td>' . ReplaceBBCode(stripslashes($nachricht->Nachricht)) . '</td>
								</tr>
							</table>';
            }

            $sql_abfrage = "UPDATE mitglieder SET GruppeLastMessageZeit='" . time() . "' WHERE ID=" . $_SESSION['blm_user'] . ";";
            mysql_query($sql_abfrage);

            if (mysql_num_rows($sql_ergebnis) == 0) {
                echo '<div style="text-align: center; width: 400px; margin-left: 50px; margin-top: 20px;">
								<i>Bisher sind noch keine Gruppennachrichten eingetragen!</i>
							</div>';
            } else {
                echo '<div style="width: 500px; margin-top: 20px; font-weight: bold; font-size: 12pt; text-align: center;">Seite: ';
                $temp = "";                            // Hier wird die Ausgabe zwischengespeichert

                for ($i = 0; $i < $anzahl_nachrichten; $i++) {        // so, dann gehen wiŕ mal alle Spieler durch
                    if ($i % GRUPPE_OFFSET == 0) {                                    // Wenn wir gerade bei einem "Offset-Punkte" angekommen sind, dann...
                        if (($i / GRUPPE_OFFSET) != $offset) {                    // Wenn der gerade bearbeitende Offset nicht der angefordete ist, dann...
                            $temp .= '<a href="./?p=gruppe&amp;o=' . ($i / GRUPPE_OFFSET) . '&amp;id=' . $gruppe->ID . '">' . (($i / GRUPPE_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
                        } else {
                            $temp .= (($i / GRUPPE_OFFSET) + 1) . ' | ';    // Ansonsten zeig nur die Nummer an.
                        }
                    }
                }

                echo substr($temp, 0, -2);        // Zum Schluss noch die Vorbereitete Ausgabe ausgeben, ohne den letzten Trenner
                echo '</div><br />';
            }
        }
    } else {
        if ($ich->Gebaeude1 >= 8) {
            ?>
            <form action="actions/gruppe.php" method="post">
                <input type="hidden" name="a" value="1"/>
                <table class="Liste" cellspacing="0" style="width: 300px; margin-bottom: 30px;">
                    <tr>
                        <th colspan="2">
                            Neue Gruppe gründen
                        </th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="32"/></td>
                    </tr>
                    <tr>
                        <td>Kürzel:</td>
                        <td><input type="text" name="kuerzel" maxlength="6"/></td>
                    </tr>
                    <tr>
                        <td>Beitrittspasswort:</td>
                        <td><input type="password" name="passwort"/></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" value="Gründen"/>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
        } else {
            echo '<span class="MeldungR" style="font-size: 12pt;">Sie müssen Ihre Plantage mindestens auf Stufe 8 haben, um eine neue Gruppe gründen zu können.</span><br /><br />';
        }

        if ($ich->Gebaeude1 >= 5) {
            $gruppe = isset($_GET['gruppe']) ? $_GET['gruppe'] : null;
            ?>
            <form action="actions/gruppe.php" method="post">
                <input type="hidden" name="a" value="2"/>
                <table class="Liste" cellspacing="0" style="width: 300px;">
                    <tr>
                        <th colspan="2">
                            In bestehende Gruppe eintreten
                        </th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="32" value="<?= sichere_ausgabe($gruppe); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Beitrittspasswort:</td>
                        <td><input type="password" name="pwd_beitritt"/></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" value="Beitreten"/>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
        }
    }
}
