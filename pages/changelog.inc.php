<?php
/**
 * Wird in die index.php eingebunden; Seite mit Changelog
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/changelog.png" alt="Changelog"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Das Changelog
                <a href="./?p=hilfe&amp;mod=1&amp;cat=20"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>

<?= $m; ?>

    <b>
        Hier können Sie die Änderungen am Bioladenmanager 2 innerhalb der letzten 2 Monate verfolgen.<br/>
        Jede größere Änderung wird hier festgehalten.
    </b>
    <br/>
    <br/>
<?php
// Gruppenverarbeitung. Zuerst mal alle Daten (Datum) abholen
$sql_abfrage1 = "SELECT
    Datum
FROM
    changelog
WHERE
    Datum >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
GROUP BY
    Datum
ORDER BY
    Datum DESC;";
$sql_ergebnis1 = mysql_query($sql_abfrage1) or die(mysql_error());
$_SESSION['blm_queries']++;

while ($datum = mysql_fetch_object($sql_ergebnis1)) {
    $sql_abfrage2 = "SELECT
    Kategorie
FROM
    changelog
WHERE
    Datum='" . $datum->Datum . "'
GROUP BY
    Kategorie
ORDER BY
    Kategorie ASC;";
    $sql_ergebnis2 = mysql_query($sql_abfrage2);
    $_SESSION['blm_queries']++;
    ?>
    <table class="Liste" cellspacing="0" style="margin-bottom: 15px;">
    <tr>
        <th colspan="2"><?= $datum->Datum; ?></th>
    </tr>
    <?php
    while ($kategorie = mysql_fetch_object($sql_ergebnis2)) {
        ?>
        <tr>
        <td style="width: 100px;"><?= htmlentities(stripslashes($kategorie->Kategorie), ENT_QUOTES, "UTF-8"); ?></td><?php
        $sql_abfrage3 = "SELECT
    ID,
    Aenderung
FROM
    changelog
WHERE
    Datum='" . $datum->Datum . "'
AND
    Kategorie='" . $kategorie->Kategorie . "'
ORDER BY
    Aenderung ASC";
        $sql_ergebnis3 = mysql_query($sql_abfrage3);
        $_SESSION['blm_queries']++;
        ?>
        <td>
            <ul><?php
                while ($aenderung = mysql_fetch_object($sql_ergebnis3)) {
                    $nr++;
                    ?>
                    <li><u>#<?= $aenderung->ID; ?>
                        :</u> <?= htmlentities(stripslashes($aenderung->Aenderung), ENT_QUOTES, "UTF-8"); ?></li><?php
                }
                ?>
            </ul>
        </td>
        </tr><?php
    }
    ?>
    </table><?php
}
