<?php
/**
 * Wird in die index.php eingebunden; Zeigt den Postein- und Ausgang des Benutzers an
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/nachrichten.png" alt="Nachrichtenliste"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Ihr Posteingang
                <a href="./?p=hilfe&amp;mod=1&amp;cat=13"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Nachrichten && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <b>Hier sehen Sie alle Ihre Nachrichten die Sie empfangen haben.</b>
    <table class="Liste" style="margin-top: 20px; margin-bottom: 10px; width: 780px;" cellspacing="0">
        <tr>
            <th style="width: 30px;">Nr</th>
            <th style="width: 150px;">Von</th>
            <th>Betreff</th>
            <th style="width: 140px;">Datum / Zeit</th>
            <th style="width: 60px;">Gelesen?</th>
            <th style="width: 90px; text-align: center;">Aktion</th>
        </tr>
        <?php
        $sql_abfrage = "SELECT
    n.*,
    m.Name AS Absender,
    m.ID AS AbsenderID
FROM
    nachrichten n LEFT OUTER JOIN mitglieder m ON m.ID=n.Von
WHERE
    An=" . $_SESSION['blm_user'] . "
ORDER BY
    Zeit DESC;";
        $sql_ergebnis = mysql_query($sql_abfrage);        // Holt sich erst mal alle Nachrichten
        $_SESSION['blm_queries']++;

        $eintrag = false;        // Bisher her haben wir noch keine Nachricht ausgegeben

        while ($nachricht = mysql_fetch_object($sql_ergebnis)) {        // So, jetzt werden alle Nahrichten durchlaufen
            $nr++;        // Die Nr. der Nachricht für die Ausgabe der ersten Spalte
            if ($nachricht->Gelesen == 1) {        // Wenn die Nachricht schon gelesen wurde, dann
                echo '<tr id="nl_' . $nachricht->ID . '">';                    // mach ne normale Spalte
            } else {        // ...ansonsten...
                echo '<tr class="Ungelesen" id="nl_' . $nachricht->ID . '">';    // mach ne Zeile mit der Class "Ungelesen"
            }

            if ($nachricht->Absender == "") {        // Wenn der Absender leer ist (NULL)
                $nachricht->Absender = "-User gelöscht-";    // dann kommt die Nachricht vom System
            }

            echo '<td>' . $nr . '</td>
							<td><a href="./?p=profil&amp;uid=' . $nachricht->AbsenderID . '">' . htmlentities($nachricht->Absender, ENT_QUOTES, "UTF-8") . '</a></td>
							<td>' . htmlentities(stripslashes($nachricht->Betreff), ENT_QUOTES, "UTF-8") . '</td>
							<td>' . date("d.m.Y - H:i:s", $nachricht->Zeit) . '</td>
							<td>' . JaNein($nachricht->Gelesen) . '</td>
							<td>
								<a href="./?p=nachrichten_lesen&amp;nid=' . $nachricht->ID . '">
									<img src="./pics/small/readmail.png" border="0" alt="Nachricht lesen" />
								</a>
								<a href="./actions/nachrichten.php?a=2&amp;id=' . $nachricht->ID . '" onclick="delNachricht(' . $nachricht->ID . ', document.getElementById(\'nl_' . $nachricht->ID . '\')); this.removeAttribute(\'onclick\'); return false;">
									<img src="./pics/small/error.png" border="0" alt="Nachricht l&ouml;schen" />
								</a>
								';
            if ($nachricht->Absender != "-User gelöscht-" && $nachricht->Absender != "-System-") {
                echo '<a href="./?p=nachrichten_schreiben&amp;an=' . $nachricht->AbsenderID . '&amp;answer=' . $nachricht->ID . '">
									<img src="./pics/small/answermail.png" border="0" alt="Antworten" />';
            }
            echo '
								</a>
							</td>
						</tr>';        // so, jetzt lönnen wir die Zeile ausgeben
            $eintrag = true;    // Wir haben mindestens eine Nachricht
        }

        if (!$eintrag) {        // Falls wir keine Nachrichten haben, gib ne entsprechende Zeile aus.
            echo '<tr><td colspan="6" style="text-align: center;"><i>Sie haben keine Nachrichten in diesem Ordner.</i></td></tr>';
        }

        /*
        *
        *		Das selbe kommt unten nochmal für den Postausgang. Da es dort zu 99% derselbe Code ist, werde ich hier mit den Kommentaren
        *		aufhören.
        *
        */
        ?>
    </table>
    <a href="./?p=nachrichten_schreiben">Neue Nachricht verfassen</a> |
    <a href="actions/nachrichten.php?a=3">Alle Nachrichten l&ouml;schen</a>
    <table cellspacing="0" style="margin-top: 20px;">
        <tr>
            <td style="width: 80px;"><img src="pics/big/nachrichten.png" alt="Nachrichten"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Ihr Postausgang
                <a href="./?p=hilfe&amp;mod=1&amp;cat=13"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
    <b>Hier sehen Sie alle Ihre Nachrichten die Sie gesendet haben.</b>
    <table class="Liste" style="margin-top: 20px; margin-bottom: 20px; width: 780px;" cellspacing="0">
        <tr>
            <th style="width: 30px;">Nr</th>
            <th style="width: 150px;">An</th>
            <th>Betreff</th>
            <th style="width: 140px;">Datum / Zeit</th>
            <th style="width: 60px;">Gelesen?</th>
            <th style="width: 60px; text-align: center;">Aktion</th>
        </tr>
        <?php
        $sql_abfrage = "SELECT
    n.*,
    m.Name AS Empfaenger
FROM
    nachrichten n LEFT OUTER JOIN mitglieder m ON n.An=m.ID
WHERE
    Von=" . $_SESSION['blm_user'] . "
ORDER BY
    Zeit DESC;";
        $sql_ergebnis = mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $eintrag = false;
        $nr = 0;

        while ($nachricht = mysql_fetch_object($sql_ergebnis)) {
            $nr++;
            if ($nachricht->Gelesen) {
                echo '<tr id="nl_' . $nachricht->ID . '">';                    // mach ne normale Spalte
            } else {        // ...ansonsten...
                echo '<tr class="Ungelesen" id="nl_' . $nachricht->ID . '">';    // mach ne Zeile mit der Class "Ungelesen"
            }

            echo '<td>' . $nr . '</td>
							<td>' . htmlentities(stripslashes($nachricht->Empfaenger), ENT_QUOTES, "UTF-8") . '</td>
							<td>' . htmlentities(stripslashes($nachricht->Betreff), ENT_QUOTES, "UTF-8") . '</td>
							<td>' . date("d.m.Y - H:i:s", $nachricht->Zeit) . '</td>
							<td>' . JaNein($nachricht->Gelesen) . '</td>
							<td>
								<a href="./?p=nachrichten_lesen&amp;nid=' . $nachricht->ID . '">
									<img src="./pics/small/readmail.png" border="0" alt="Nachricht lesen" />
								</a>';
            if ($nachricht->Gelesen == 0) {
                echo '<a href="./actions/nachrichten.php?a=2&amp;id=' . $nachricht->ID . '" onclick="delNachricht(' . $nachricht->ID . ', document.getElementById(\'nl_' . $nachricht->ID . '\')); this.removeAttribute(\'onclick\'); return false;">
									<img src="./pics/small/error.png" border="0" alt="Nachricht l&ouml;schen" />
								</a>';
            }
            echo '</td>
						</tr>';
            $eintrag = true;
        }

        if (!$eintrag) {
            echo '<tr><td colspan="6" style="text-align: center;"><i>Sie haben keine Nachrichten in diesem Ordner.</i></td></tr>';
        }
        ?>
    </table>
    <?php
}
