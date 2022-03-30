<?php
/**
 * Wird in die index.php eingebunden; Zeigt die Angebote auf dem Markt an
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/marktplatz.png" alt="Marktplatz"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Der Marktplatz
                <a href="./?p=hilfe&amp;mod=1&amp;cat=11"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if ($_SESSION['blm_sitter'] && !$ich->Sitter->Marktplatz) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <b>
        Hier sehen Sie die aktuellen Angebote auf dem anonymen Marktplatz. Die Preise können selbst bestimmt werden
        und richten sich wahrscheinlich nach dem aktuellen Fortschritt des Spiels.
    </b>
    <br/>
    <br/>
    <form action="./" method="get">
        <input type="hidden" name="p" value="marktplatz_liste"/>
        <table class="Liste" style="width: 500px;" cellspacing="0">
            <tr>
                <th>Filter</th>
                <th colspan="3" style="text-align: right;"><a href="./?p=marktplatz_liste&amp;o=0"
                                                              onclick="AllesAuswaehlen(document.forms[0], ''); return false;">Alles
                        abwählen</a> | <a href="./?p=marktplatz_liste&amp;o=0"
                                          onclick="AllesAuswaehlen(document.forms[0], 'checked'); return false;">Alles
                        auswählen</a></th>
            </tr>
            <tr>
                <?php
                $filter = isset($_GET['w']) ? $_GET['w'] : null;

                if ($filter == null || !is_array($filter)) {
                    $filter = array();
                }

                $url_string = '';
                for ($i = 0; $i < sizeof($filter); $i++) {
                    $url_string = $url_string . "&amp;w[]=" . urlencode($filter[$i]);
                }

                $i = 0;

                for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                    echo '<td style="width: 25%;"><input type="checkbox" name="w[]" value="' . $i . '" ';
                    if (sizeof($filter) == 0 || in_array($i, $filter)) {
                        echo 'checked="checked" ';
                    }
                    echo '/> ' . WarenName($i) . '</td>';
                    if ($i % 4 == 0) {
                        echo '</tr><tr>';
                    }
                }
                for (/* es wird keine variable initialisiert! */; ($i - 1) % 4 > 0; $i++) {
                    echo '<td>&nbsp;</td>';
                }
                ?>
            </tr>
        </table>
        <br/>
        <div style="width: 500px; text-align: center;">
            <input type="submit" value="Filtern"/>
        </div>
    </form>
    <br/>
    <table class="Liste" style="width: 490px" cellspacing="0">
        <tr>
            <th>Ware</th>
            <th>Menge</th>
            <th>Preis / kg</th>
            <th>Gesamtpreis</th>
            <th>Aktion</th>
        </tr>
        <?php
        $offset = isset($_GET['o']) ? intval($_GET['o']) : 0;        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
        // Dabei berechnet sich der Starteintrag aus $offset*MARKTPLATZ_OFFSET
        $anzahl_markt = Database::getInstance()->getMarktplatzCount($filter);

        if ($offset < 0 || ($offset * MARKTPLATZ_OFFSET) > $anzahl_markt) {        // Ist das Offset negativ?
            $offset = 0;            // ... dann setz es auf Standard
        }

        $sql_abfrage = "SELECT
    *
FROM
    marktplatz
" . (sizeof($filter) > 0 ? " WHERE Was IN (" . implode(",", $filter) . ") " : "") . "
ORDER BY
    Was,
    Preis
LIMIT " . $offset * MARKTPLATZ_OFFSET . ", " . MARKTPLATZ_OFFSET . ";";

        $sql_ergebnis = mysql_query($sql_abfrage);        // Alle Angebote abrufen
        $_SESSION['blm_queries']++;

        $eintrag = false;        // Bisher haben wir noch kein Angebot ausgegeben, oder? ;)

        while ($angebot = mysql_fetch_object($sql_ergebnis)) {        // Alle Angebote abrufen...
            echo '<tr>
							<td>' . WarenName($angebot->Was) . '</td>
							<td>' . number_format($angebot->Menge, 0, ",", ".") . ' kg</td>
							<td>' . number_format($angebot->Preis, 2, ",", ".") . ' ' . $Currency . '</td>
							<td>' . number_format($angebot->Preis * $angebot->Menge, 2, ",", ".") . ' ' . $Currency . '</td>
							<td>';
            if ($angebot->Von != $_SESSION['blm_user']) {        // Wenn das Angebot nicht vom Betrachter ist, dann zeig den Kaufen-Link
                echo '<a href="./actions/marktplatz.php?a=2&amp;id=' . $angebot->ID . $url_string . '">Kaufen</a>';
            } else {       // Wenn das Angebot vom Betrachter ist, dann zeig den Zurückziehen-Link
                echo '<a href="./actions/marktplatz.php?a=3&amp;id=' . $angebot->ID . $url_string . '">Zurückziehen</a>';
            }
            echo '</td></tr>';            // ...und ausgeben
            $eintrag = true;        // Jetzt haben wir mindestens einen Eintrag
        }

        if (!$eintrag) {    // Falls kein Angebot gefunden wurde, dann ne entsprechende Meldung ausgeben
            echo '<tr><td colspan="5" style="text-align: center;"><i>Es sind keine Angebote vorhanden, die Ihren Kriterien entsprechen.</i></td></tr>';
        }
        ?>
    </table>
    <?php
    if ($anzahl_markt > 0) {
        echo '<div style="font-weight: bold; font-size: 12pt;">Seite: ';
        $temp = "";                            // Hier wird die Ausgabe zwischengespeichert

        for ($i = 0; $i < $anzahl_markt; $i++) {        // so, dann gehen wiŕ mal alle Spieler durch
            if ($i % MARKTPLATZ_OFFSET == 0) {                                    // Wenn wir gerade bei einem "Offset-Punkte" angekommen sind, dann...
                if (($i / MARKTPLATZ_OFFSET) != $offset) {                    // Wenn der gerade bearbeitende Offset nicht der angefordete ist, dann...
                    $temp .= '<a href="./?p=marktplatz_liste&amp;o=' . ($i / MARKTPLATZ_OFFSET) . $url_string. '">' . (($i / MARKTPLATZ_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
                } else {
                    $temp .= (($i / MARKTPLATZ_OFFSET) + 1) . ' | ';    // Ansonsten zeig nur die Nummer an.
                }
            }
        }

        echo substr($temp, 0, -2);        // Zum Schluss noch die Vorbereitete Ausgabe ausgeben, ohne den letzten Trenner
        echo '</div><br />';
    }
    ?>
    <br/>
    <?php
    $hat_waren = false;
    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Als wird noch überprüft, ob der Benutzer überhaupt waren hat,
        $temp = "Lager" . $i;        // damit man überprüfen kann, ob er ein neues Angebot einstellen kann
        if ($ich->$temp > 0)
            $hat_waren = true;        // Ja, der Benutzer hat Waren
    }

    if ($hat_waren) {        // Wenn der Benutzer was auf Lager hat, dann zeige den Link zum Einstellen eines neuen Angebots an.
        echo '<a href="./?p=marktplatz_verkaufen">Neues Angebot einstellen</a><br />';
    }
}
