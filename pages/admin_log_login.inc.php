<?php
/**
 * Wird in die index.php eingebunden; Seite zur Ansicht des Logbuches (Login)
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

if ($_GET['wer'] != "") {
    $filter_wer = '%' . mysql_real_escape_string($_GET['wer']) . '%';
} else {
    $filter_wer = '%';
}

if ($_GET['ip'] != "") {
    $filter_ip = '%' . mysql_real_escape_string($_GET['ip']) . '%';
} else {
    $filter_ip = '%';
}

if ($_GET['art'] != "") {
    if ($_GET['art'] == "0")
        $filter_art = 'Normal';
    else
        $filter_art = 'Sitter';
} else {
    $filter_art = '%';
}

switch ($_GET['sort']) {
    case "0":
        $sort = "IP";
        break;
    case "1":
        $sort = "Wer";
        break;
    case "2":
        $sort = "Wann";
        break;
    case "3":
        $sort = "Art";
        break;
    default:
        $_GET['sort'] = "2";
        $sort = "Wann";
}

if ($_GET['order'] == "0") {
    $order = "ASC";
    $_GET['order'] = "0";
} else {
    $order = "DESC";
    $_GET['order'] = "1";
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Bioladenlogbuch"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Logbücher - Login</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<form action="./" method="get">
    <input type="hidden" name="p" value="admin_log_login"/>
    <h3>Filtern nach Namen:</h3>
    <input type="text" name="wer" value="<?= htmlentities(stripslashes($_GET['wer']), ENT_QUOTES, "UTF-8"); ?>"/>
    <br/>
    <h3>Filtern nach IP:</h3>
    <input type="text" name="ip" value="<?= htmlentities(stripslashes($_GET['ip']), ENT_QUOTES, "UTF-8"); ?>"/>
    <br/>
    <h3>Filtern nach Art:</h3>
    <select name="art">
        <option value="0" <?php
        if ($_GET['art'] == "0") {
            echo 'selected="selected"';
        }
        ?>>Hauptaccount
        </option>
        <option value="1" <?php
        if ($_GET['art'] == "1") {
            echo 'selected="selected"';
        }
        ?>>Sitter
        </option>
    </select>
    <br/>
    <h3>Sortieren nach</h3>
    <select name="sort">
        <option value="0" <?php
        if ($_GET['sort'] == "0") {
            echo 'selected="selected"';
        }
        ?>>IP
        </option>
        <option value="1" <?php
        if ($_GET['sort'] == "1") {
            echo 'selected="selected"';
        }
        ?>>Wer
        </option>
        <option value="2" <?php
        if ($_GET['sort'] == "2") {
            echo 'selected="selected"';
        }
        ?>>Wann
        </option>
        <option value="3" <?php
        if ($_GET['sort'] == "3") {
            echo 'selected="selected"';
        }
        ?>>Art
        </option>
    </select><select name="order">
        <option value="0" <?php
        if ($_GET['order'] == "0") {
            echo 'selected="selected"';
        }
        ?>>Aufsteigend
        </option>
        <option value="1" <?php
        if ($_GET['order'] == "1") {
            echo 'selected="selected"';
        }
        ?>>Absteigend
        </option>
    </select>
    <br/>
    <br/>
    <input type="submit" value="Abschicken"/><br/>
</form>
<br/>
<table class="Liste" style="width: 720px;">
    <tr>
        <th>Wer</th>
        <th>IP</th>
        <th>Wann</th>
        <th>Art</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
    COUNT(*) AS anzahl
FROM
    log_login_view
WHERE
    Wer LIKE '" . $filter_wer . "'
AND
    IP LIKE '" . $filter_ip . "'
AND
    Art LIKE '" . $filter_art . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $anzahl = mysql_fetch_object($sql_ergebnis);
    $anzahl_eintraege = $anzahl->anzahl;

    $offset = intval($_GET['o']);        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
    // Dabei berechnet sich der Starteintrag aus $offset*RANGLISTE_OFFSET

    if (ADMIN_LOG_OFFSET * $offset > $anzahl_eintraege) {        // Will er das Offset höher setzen, als es Spieler gibt?
        $offset = intval($anzahl_eintraege / ADMIN_LOG_OFFSET);        // Wenn ja, dann setz das Offset auf den letzmöglichen Wert
    }

    if ($offset < 0) {        // Ist das Offset negativ?
        $offset = 0;            // ... dann setz es auf Standard
    }

    $sql_abfrage = "SELECT
    *,
    UNIX_TIMESTAMP(Wann) AS Wann
FROM
    log_login_view
WHERE
    Wer LIKE '" . $filter_wer . "'
AND
    IP LIKE '" . $filter_ip . "'
AND
    Art LIKE '" . $filter_art . "'
ORDER BY
    " . $sort . " " . $order . "
LIMIT " . $offset * ADMIN_LOG_OFFSET . ", " . ADMIN_LOG_OFFSET . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);

    while ($l = mysql_fetch_object($sql_ergebnis)) {
        ?>
        <tr>
            <td><?= htmlentities(stripslashes($l->Wer), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= $l->IP; ?></td>
            <td><?= date("d.m.Y H:i:s", $l->Wann); ?></td>
            <td><?= $l->Art; ?></td>
        </tr>
        <?php
    }

    if (mysql_num_rows($sql_ergebnis) == 0) {
    ?>
    <tr>
        <td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td>
    </tr>
</table>
<?php
} else {
    echo '</table><div style="width: 500px; margin-top: 20px; font-weight: bold; font-size: 12pt; text-align: center;">Seite: ';
    $temp = "";                            // Hier wird die Ausgabe zwischengespeichert

    for ($i = 0; $i < $anzahl_eintraege; $i++) {        // so, dann gehen wiŕ mal alle Spieler durch
        if ($i % ADMIN_LOG_OFFSET == 0) {                                    // Wenn wir gerade bei einem "Offset-Punkte" angekommen sind, dann...
            if (($i / ADMIN_LOG_OFFSET) != $offset) {                    // Wenn der gerade bearbeitende Offset nicht der angefordete ist, dann...
                $temp .= '<a href="./?p=' . $_GET['p'] . '&amp;o=' . ($i / ADMIN_LOG_OFFSET) . '&amp;ip=' . $_GET['ip'] . '&amp;wer=' . $_GET['wer'] . '&amp;art=' . $_GET['art'] . '&amp;sort=' . $_GET['sort'] . '&amp;order=' . $_GET['order'] . '&amp;' . time() . '">' . (($i / ADMIN_LOG_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
            } else {
                $temp .= (($i / ADMIN_LOG_OFFSET) + 1) . ' | ';    // Ansonsten zeig nur die Nummer an.
            }
        }
    }

    echo substr($temp, 0, -2);        // Zum Schluss noch die Vorbereitete Ausgabe ausgeben, ohne den letzten Trenner
    echo '</div><br />';
}
?>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
