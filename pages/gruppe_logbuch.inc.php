<?php
/**
 * Wird in die index.php eingebunden; Zeigt das Gruppenlogbuch an
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Gruppenlogbuch
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
    ?>
    <div style="width: 650px; text-align: center; margin-bottom: 5px;">
        <a href="./?p=gruppe">Board</a> |
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
        | <u><b>Logbuch</b></u>
    </div>

    <table class="Liste" style="width: 700px;">
        <tr>
            <th style="width: 80px; text-align: center;">Datum</th>
            <th>Text</th>
        </tr>
        <?php
        $sql_abfrage = "SELECT
    *
FROM
    gruppe_logbuch
WHERE
    Gruppe='" . $ich->Gruppe . "'
ORDER BY
    Datum DESC
LIMIT
    0, 25;";
        $sql_ergebnis = mysql_query($sql_abfrage);

        while ($log = mysql_fetch_object($sql_ergebnis)) {
            ?>
            <tr>
                <td style="text-align: center;"><?= date("d.m.Y H:i", $log->Datum); ?></td>
                <td><?= stripslashes($log->Text); ?></td>
            </tr>
            <?php
        }

        if (mysql_num_rows($sql_ergebnis) == 0) {
            ?>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <i>- Bisher sind keine Aktionen eingetragen - </i>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}
