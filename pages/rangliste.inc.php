<?php
/**
 * Wird in die index.php eingebunden; Rangiste der Spieler und Gruppen, sowie Spezialrangliste
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/rangliste.png" alt="Rangliste"/></td>
        <td>Die Rangliste
            <a href="./?p=hilfe&amp;mod=1&amp;cat=17"><img src="/pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier werden die Besten der Besten aufgelistet, sortiert nach ihren Punkten.<br/>
    Ihre Position ist fett geschrieben.
</b>
<br/>
<br/>
<table class="Liste" cellspacing="0" style="width: 550px;">
    <tr>
        <th style="width: 40px;">Platz</th>
        <th>Name</th>
        <th style="width: 60px;">Punkte</th>
        <?php
        if (IstAngemeldet()) {        // Wenn der Betrachter der Rangliste angemeldet ist, dann zeig die Aktionsspalte an
            echo '<th style="width: 170px;">Aktion</th>';
        }

        echo '</tr>';

        $offset = isset($_GET['o']) ? intval($_GET['o']) : 0;        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
        $offset_gruppe = isset($_GET['o_gr']) ? intval($_GET['o_gr']) : 0;        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
        // Dabei berechnet sich der Starteintrag aus $offset*RANGLISTE_OFFSET

        $find_spieler = isset($_GET['find_spieler']) ? $_GET['find_spieler'] : null;
        if ($find_spieler == null)
            $filter = '%';
        else
            $filter = str_replace('*', '%', mysql_real_escape_string($find_spieler));

        if (RANGLISTE_OFFSET * $offset > Database::getInstance()->getPlayerCount($filter)) {        // Will er das Offset höher setzen, als es Spieler gibt?
            $offset = intval(Database::getInstance()->getPlayerCount($filter) / RANGLISTE_OFFSET);        // Wenn ja, dann setz das Offset auf den letzmöglichen Wert
        }

        if (RANGLISTE_OFFSET * $offset > Database::getInstance()->getGroupCount()) {        // Will er das Offset höher setzen, als es Spieler gibt?
            $offset_gruppe = intval(Database::getInstance()->getGroupCount() / RANGLISTE_OFFSET);        // Wenn ja, dann setz das Offset auf den letzmöglichen Wert
        }

        if ($offset < 0) {        // Ist das Offset negativ?
            $offset = 0;            // ... dann setz es auf Standard
        }

        if ($offset_gruppe < 0) {        // Ist das Offset negativ?
            $offset_gruppe = 0;            // ... dann setz es auf Standard
        }

        $sql_abfrage = "SELECT
    m.Name AS n,
    m.ID AS nID,
    m.LastAction AS l,
    m.Admin AS istAdmin,
    m.Betatester AS istBetatester,
    m.Punkte,
    g.Kuerzel,
    m.Gruppe
FROM
    mitglieder m LEFT OUTER JOIN gruppe g ON m.Gruppe=g.ID
WHERE
    m.ID>0
AND
    m.Name LIKE '" . $filter . "'
ORDER BY
    m.Punkte DESC,
    m.Name ASC
LIMIT " . $offset * RANGLISTE_OFFSET . ", " . RANGLISTE_OFFSET . ";";
        $sql_ergebnis = mysql_query($sql_abfrage);        // Ruft die Liste der Spieler mit deren Punkten ab
        $_SESSION['blm_queries']++;

        $nr = $offset * RANGLISTE_OFFSET;        // Setzt die Startposition des ersten Eintrags

        while ($spieler = mysql_fetch_object($sql_ergebnis)) {    // Solange wir noch nicht alle Spieler ausgegeben haben...
            $nr++;        // Setzt die Position des Spielers eins hoch für die Ausgabe

            if ($spieler->l + 300 < time()) {            // Hat der Benutzer in den letzten 5 Minuten eine neue Seite angefordert?
                $status = "Offline";                // Wenn nicht, dann ist er offline
            } else {
                $status = "Online";                    // ansonsten ist er Online
            }

            if ($spieler->nID == $_SESSION['blm_user']) {        // Wenn der aktuelle Spieler "ich" bin, dann...
                if ($spieler->nID == intval($_GET['highlight'])) {
                    echo '<tr class="RanglisteFettH">';        // mach die Zeile Fett...
                } else {
                    echo '<tr class="RanglisteFett">';        // mach die Zeile Fett...
                }
            } else {
                if ($spieler->nID == intval($_GET['highlight'])) {
                    echo '<tr class="RanglisteH">';                // Ansonsten mach sie normal
                } else {
                    echo '<tr class="Rangliste">';        // mach die Zeile Fett...
                }
            }

            echo '<td style="text-align: right;">' . $nr . '</td>
						<td><img src="./pics/small/' . $status . '.png" alt="' . $status . '" title="' . $status . '" />' . htmlentities(stripslashes($spieler->n), ENT_QUOTES, "UTF-8");
            if ($spieler->istAdmin) {    // Wenn der User Admin ist, zeige den gelben Stern an
                echo '&nbsp;&nbsp;<img src="/pics/small/admin.png" alt="Ingame Administrator" title="Ingame Administrator" />';
            }

            if ($spieler->istBetatester) {    // Wenn er Betatester ist, dann zeig den silbernen Stern an
                echo '&nbsp;&nbsp;<img src="/pics/small/beta.png" alt="Betatester" title="Betatester" />';
            }

            if ($spieler->Kuerzel != "") {
                echo ' (<a href="./?p=gruppe&amp;id=' . $spieler->Gruppe. '">' . htmlentities(stripslashes($spieler->Kuerzel), ENT_QUOTES, "UTF-8") . '</a>)';
            }

            echo '</td><td style="text-align: right; padding-right: 8px;">' . number_format($spieler->Punkte, 0, ",", ".") . '</td>';

            if (IstAngemeldet()) {        // Nur wenn der Betrachter angemeldet ist, dann zeig auf die Aktionsfelder an
                if ($spieler->nID != $_SESSION['blm_user']) {
                    echo '<td>(<a href="./?p=nachrichten_schreiben&amp;an=' . $spieler->nID. '">IGM</a> | <a href="./?p=vertrag_neu&amp;an=' . $spieler->nID. '">Vertrag</a> | <a href="./?p=profil&amp;uid=' . $spieler->nID. '">Profil</a>)</td>';
                } else {
                    echo '<td>(IGM | Vertrag | <a href="./?p=profil&amp;uid=' . $spieler->nID. '">Profil</a>)</td>';
                }
            }
            echo '</tr>';
        }
        echo '</table><br />';

        $anzahl_spieler = Database::getInstance()->getPlayerCount($filter);        // Wieviele Spieler gibts überhaupt
        echo '<div style="font-weight: bold; font-size: 12pt;">Seite: ';
        $temp = "";                            // Hier wird die Ausgabe zwischengespeichert

        for ($i = 0; $i < $anzahl_spieler; $i++) {        // so, dann gehen wiŕ mal alle Spieler durch
            if ($i % RANGLISTE_OFFSET == 0) {                                    // Wenn wir gerade bei einem "Offset-Punkte" angekommen sind, dann...
                if (($i / RANGLISTE_OFFSET) != $offset) {                    // Wenn der gerade bearbeitende Offset nicht der angefordete ist, dann...
                    $temp .= '<a href="./?p=rangliste&amp;o=' . ($i / RANGLISTE_OFFSET) . '&amp;o_gr=' . $offset_gruppe . '&amp;highlight=' . intval($_GET['highlight']) . '&amp;find_spieler=' . htmlentities(stripslashes($_GET['find_spieler'])). '">' . (($i / RANGLISTE_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
                } else {
                    $temp .= (($i / RANGLISTE_OFFSET) + 1) . ' | ';    // Ansonsten zeig nur die Nummer an.
                }
            }
        }

        echo substr($temp, 0, -2);        // Zum Schluss noch die Vorbereitete Ausgabe ausgeben, ohne den letzten Trenner
        echo '</div><br />';
        ?>
        <div style="font-weight: bold; font-size: 12pt;">
            Spielersuche:
            <form action="" method="get">
                <input type="hidden" name="p" value="rangliste"/>
                <input type="hidden" name="o" value="<?= $offset; ?>"/>
                <input type="hidden" name="o_gr" value="<?= $offset_gruppe; ?>"/>
                <input type="hidden" name="highlight" value="<?= intval($_GET['highlight']); ?>"/>
                <input type="text" name="find_spieler"
                       value="<?= sichere_ausgabe($find_spieler); ?>" size="24"/> <span
                        style="font-size: 75%; font-weight: normal;"><i>(Das Zeichen <span
                                style="font-size: 160%">*</span> passt auf ein beliebiges Zeichen)</i></span><br/><br/>
                <input type="submit" value="Suchen"/>
                <input type="reset" value="Reset"
                       onclick="document.forms[0].find_spieler.value='*'; document.forms[0].submit();"/>
            </form>
        </div>
        <br/>

        <h2>Gruppenrangliste:</h2>
        <table class="Liste" cellspacing="0" style="width: 660px;">
            <tr>
                <th style="width: 40px;">Platz</th>
                <th>Name</th>
                <th>Kürzel</th>
                <th>Mitglieder</th>
                <th style="width: 110px;">Punkte</th>
                <th style="width: 110px;">Durschnitt</th>
            </tr>
            <?php
            $sql_abfrage = "SELECT 
    Gruppe, 
    SUM(Punkte) AS GruppenPunkte,
    COUNT(*) AS anzMitglieder
FROM 
    mitglieder
WHERE
    Gruppe IS NOT NULL
GROUP BY 
    Gruppe
ORDER BY
    GruppenPunkte DESC
LIMIT " . $offset_gruppe * RANGLISTE_OFFSET . ", " . RANGLISTE_OFFSET . ";";
            $sql_ergebnis = mysql_query($sql_abfrage) or die(mysql_error());        // Ruft die Liste der Spieler mit deren Punkten ab
            $_SESSION['blm_queries']++;

            $nr = $offset_gruppe * RANGLISTE_OFFSET;        // Setzt die Startposition des ersten Eintrags

            while ($gruppe = mysql_fetch_object($sql_ergebnis)) {    // Solange wir noch nicht alle Spieler ausgegeben haben...
                $nr++;        // Setzt die Position des Spielers eins hoch für die Ausgabe

                $sql_abfrage = "SELECT
    *
FROM
    gruppe
WHERE
    ID='" . $gruppe->Gruppe . "';";
                $sql_ergebnis2 = mysql_query($sql_abfrage);        // Ruft die Liste der Spieler mit deren Punkten ab
                $_SESSION['blm_queries']++;

                $temp = mysql_fetch_object($sql_ergebnis2);

                if ($temp->ID == $ich->Gruppe) {        // Wenn der aktuelle Spieler "ich" bin, dann...
                    echo '<tr class="RanglisteFett">';        // mach die Zeile Fett...
                } else {
                    echo '<tr class="Rangliste">';                // Ansonsten mach sie normal
                }

                echo '<td style="text-align: right;">' . $nr . '</td>
						<td><a href="./?p=gruppe&amp;id=' . $gruppe->Gruppe. '">' . htmlentities(stripslashes($temp->Name), ENT_QUOTES, "UTF-8") . '</a></td>
						<td>' . htmlentities(stripslashes($temp->Kuerzel), ENT_QUOTES, "UTF-8") . '</td>
						<td>' . $gruppe->anzMitglieder . '</td>
						<td>' . number_format($gruppe->GruppenPunkte, 0, ",", ".") . '</td>
						<td>' . number_format($gruppe->GruppenPunkte / $gruppe->anzMitglieder, 0, ",", ".") . '</td>';
                echo '</tr>';
            }

            if (Database::getInstance()->getGroupCount() == 0) {
                echo '<tr><td colspan="6" style="text-align: center;"><i>Bisher sind noch keine Gruppen angemeldet.</i></td></tr>';
            }

            echo '</table><br />';

            $anzahl_gruppen = Database::getInstance()->getGroupCount() - 1;        // Wieviele Spieler gibts überhaupt
            if ($anzahl_gruppen > 0) {
                echo '<div style="font-weight: bold; font-size: 12pt;">Seite: ';
                $temp = "";                            // Hier wird die Ausgabe zwischengespeichert

                for ($i = 0; $i < $anzahl_gruppen; $i++) {        // so, dann gehen wiŕ mal alle Spieler durch
                    if ($i % RANGLISTE_OFFSET == 0) {                                    // Wenn wir gerade bei einem "Offset-Punkte" angekommen sind, dann...
                        if (($i / RANGLISTE_OFFSET) != $offset_gruppe) {                    // Wenn der gerade bearbeitende Offset nicht der angefordete ist, dann...
                            $temp .= '<a href="./?p=rangliste&amp;o=' . intval($_GET['o']) . '&amp;highlight=' . intval($_GET['highlight']) . '&amp;find_spieler=' . htmlentities(stripslashes($_GET['find_spieler'])) . '&amp;o_gr=' . ($i / RANGLISTE_OFFSET). '">' . (($i / RANGLISTE_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
                        } else {
                            $temp .= (($i / RANGLISTE_OFFSET) + 1) . ' | ';    // Ansonsten zeig nur die Nummer an.
                        }
                    }
                }

                echo substr($temp, 0, -2);        // Zum Schluss noch die Vorbereitete Ausgabe ausgeben, ohne den letzten Trenner
                echo '</div><br />';
            }
            ?>
            <h2>Ewige Higscoreliste</h2>
            <table class="Liste" cellspacing="0" style="width: 550px;">
                <tr>
                    <th style="width: 40px;">Platz</th>
                    <th>Name</th>
                    <th style="width: 60px;">Punkte</th>
                </tr>
                <?php
                $sql_abfrage = "SELECT
    ID,
    Name,
    EwigePunkte
FROM
    mitglieder
WHERE
    ID>0
AND
    EwigePunkte>0
ORDER BY
    EwigePunkte DESC,
    Name ASC
LIMIT 
    0, 10;";
                $sql_ergebnis = mysql_query($sql_abfrage);
                $_SESSION['blm_queries']++;

                $platz = 0;        // Setzt die Startposition des ersten Eintrags

                while ($spieler = mysql_fetch_object($sql_ergebnis)) {
                    $platz++;
                    if ($spieler->ID == $_SESSION['blm_user']) {        // Wenn der aktuelle Spieler "ich" bin, dann...
                        echo '<tr class="RanglisteFett">';        // mach die Zeile Fett...
                    } else {
                        echo '<tr class="Rangliste">';                // Ansonsten mach sie normal
                    }
                    echo '<td>' . $platz . '</td>
						<td><a href="./?p=profil&amp;uid=' . $spieler->ID. '">' . htmlentities(stripslashes($spieler->Name), ENT_QUOTES, "UTF-8") . '</a></td>
						<td>' . $spieler->EwigePunkte . '</td>
					</tr>';
                }

                if (mysql_num_rows($sql_ergebnis) == 0) {
                    echo '<tr><td colspan="3" style="text-align: center;"><i>Bisher sind noch keine Punkte für die ewige Higscoreliste vergeben worden!</i></td></tr>';
                }

                echo '</table>';
                ?>
                <h2>Verschiedenes</h2>
                <table class="Liste" style="width: 600px;">
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=0&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Bioladenfreak:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    ID,
    Name,
    Onlinezeit
FROM
    mitglieder
WHERE
    Admin = 0
ORDER BY
    Onlinezeit DESC,
    RAND(CURDATE())
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit ';
                            if (date("m", $user->Onlinezeit) > 1) {
                                echo (date("m", $user->Onlinezeit) - 1) . " Monat(e), ";
                            }

                            if (date("d", $user->Onlinezeit) > 1) {
                                echo (date("d", $user->Onlinezeit) - 1) . " Tag(en), ";
                            }

                            echo date("H", $user->Onlinezeit) - 1 . " Stunden und " . intval(date("i", $user->Onlinezeit)) . " Minuten.";
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=1&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Pate:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    m.ID,
    m.Name,
    s.AusgabenMafia
FROM
    mitglieder m NATURAL JOIN statistik s
WHERE
    Admin = 0
ORDER BY
    s.AusgabenMafia DESC,
    RAND(HOUR(CURDATE()))
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit Ausgaben von ' . number_format($user->AusgabenMafia, 0, ",", ".") . ' ' . $Currency . ' für die Mafia.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=2&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Händlerkönig:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    m.ID,
    m.Name,
    s.AusgabenMarkt
FROM
    mitglieder m NATURAL JOIN statistik s
WHERE
    Admin = 0
ORDER BY
    s.AusgabenMarkt DESC,
    RAND(CURDATE()+2)
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit Ausgaben von ' . number_format($user->AusgabenMarkt, 0, ",", ".") . ' ' . $Currency . ' auf dem freien Markt.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=3&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Baumeister:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
											m.ID,
											m.Name,
											(";
                            for ($i = 1; $i <= ANZAHL_GEBAEUDE; $i++) {
                                $sql_abfrage .= "Gebaeude" . $i . "+";
                            }

                            $sql_abfrage = substr($sql_abfrage, 0, -1) . ")AS GebaeudeLevel
										FROM
											mitglieder m NATURAL JOIN gebaeude g
										WHERE
											ID>1
										ORDER BY
											GebaeudeLevel DESC,
											RAND(YEAR(CURDATE()))
										LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit ' . $user->GebaeudeLevel . ' Gebäudeleveln.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=4&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Das Genie:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
											m.ID as mID,
											m.Name,
											(";
                            for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                                $sql_abfrage .= "Forschung" . $i . "+";
                            }

                            $sql_abfrage = substr($sql_abfrage, 0, -1) . ")AS ForschungsLevel
										FROM
											mitglieder m NATURAL JOIN forschung f
										WHERE
											ID>1
										ORDER BY
											ForschungsLevel DESC,
											RAND(MONTH(CURDATE()))
										LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->mID . '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit ' . $user->ForschungsLevel . ' Forschungsleveln.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=5&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Kapitalist:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    m.ID,
    m.Name,
    s.EinnahmenZinsen
FROM
    mitglieder m NATURAL JOIN statistik s
WHERE
    Admin = 0
ORDER BY
    s.EinnahmenZinsen DESC,
    RAND(DAY(CURDATE()))
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit Einnahmen von ' . number_format($user->EinnahmenZinsen, 0, ",", ".") . ' ' . $Currency . ' durch Zinsen.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            <a href="./?p=rangliste_spezial&amp;rang=6&amp;o=<?= intval($_GET['o']); ?>&amp;highlight=<?= intval($_GET['highlight']); ?>">
                                Der Mitteilungsbedürftige:
                            </a>
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    ID,
    Name,
    IgmGesendet
FROM
    mitglieder
WHERE
    Admin = 0
ORDER BY
    IgmGesendet DESC,
    RAND(WEEK(CURDATE()))
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a> mit ' . $user->IgmGesendet . ' gesendeten Nachrichten.';
                            ?></td>
                    </tr>
                    <tr>
                        <th style="width: 1px; padding: 2px 8px 2px 8px; border-bottom: solid 1px #666666; white-space: nowrap;">
                            Der Spieler des Tages:
                        </th>
                        <td><?php
                            $sql_abfrage = "SELECT
    ID,
    Name
FROM
    mitglieder
WHERE
    Admin = 0
ORDER BY
    RAND(" . date("dmY") . ")
LIMIT 0,1;";
                            $sql_ergebnis = mysql_query($sql_abfrage);
                            $_SESSION['blm_queries']++;

                            $user = mysql_fetch_object($sql_ergebnis);

                            echo '<a href="./?p=profil&amp;uid=' . $user->ID. '">' . htmlentities(stripslashes($user->Name), ENT_QUOTES, "UTF-8") . '</a>. Einer muss den Zufallsgenerator ja testen.';
                            ?></td>
                    </tr>
                </table>
