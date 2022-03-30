<?php
/**
 * Wird in die index.php eingebunden; Seite zur Ansicht des Logbuches (Bankbewegungen)
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 *
 * @todo Link zum Hinzufügen eines Eintrags
 */

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $name = $_GET['name'];
    $filter = mysql_real_escape_string($name);
} else {
    $name = "";
    $filter = "%";
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Banklogbuch"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Logbücher - Bank</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<form action="./" method="get">
    <input type="hidden" name="p" value="admin_log_bank"/>
    <h3>Filtern nach Namen:</h3>
    <input type="text" name="name" value="<?= sichere_ausgabe($name); ?>"/>
    <input type="submit" value="Abschicken"/><br/>
</form>
<br/>
<table class="Liste" style="width: 450px;">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Aktion</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
    COUNT(*) AS anzahl
FROM
    log_bank_view
WHERE
    Wer LIKE '" . $filter . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $anzahl = mysql_fetch_object($sql_ergebnis);
    $anzahl_eintraege = $anzahl->anzahl;

    $offset = isset($_GET['o']) ? intval($_GET['o']) : 0;        // Ruft das Offset der Rangliste ab, also den Starteintrag, ab welchen die Ausgabe erfolgen soll
    // Dabei berechnet sich der Starteintrag aus $offset*RANGLISTE_OFFSET

    if (ADMIN_LOG_OFFSET * $offset > $anzahl_eintraege) {        // Will er das Offset höher setzen, als es Spieler gibt?
        $offset = intval($anzahl_eintraege / ADMIN_LOG_OFFSET);        // Wenn ja, dann setz das Offset auf den letzmöglichen Wert
    }

    if ($offset < 0) {        // Ist das Offset negativ?
        $offset = 0;            // ... dann setz es auf Standard
    }

    $sql_abfrage = "SELECT
    *,
    UNIX_TIMESTAMP(Wann) AS WannTs
FROM
    log_bank_view
WHERE
    Wer LIKE '" . $filter . "'
ORDER BY Wann DESC
LIMIT " . $offset * ADMIN_LOG_OFFSET . ", " . ADMIN_LOG_OFFSET . ";";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    while ($l = mysql_fetch_object($sql_ergebnis)) {
        ?>
        <tr>
            <td><?= htmlentities(stripslashes($l->Wer), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= date("d.m.Y H:i:s", $l->WannTs); ?></td>
            <td><?= number_format($l->Wieviel, 2, ",", "."); ?></td>
            <td><?= $l->Aktion; ?></td>
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
                $temp .= '<a href="./?p=' . (isset($_GET['p']) ? sichere_ausgabe($_GET['p']) : 0) . '&amp;o=' . ($i / ADMIN_LOG_OFFSET) . '&amp;name=' . sichere_ausgabe($name) . '">' . (($i / ADMIN_LOG_OFFSET) + 1) . '</a> | ';    // Zeig die Nummer des Offsets als Link an
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
