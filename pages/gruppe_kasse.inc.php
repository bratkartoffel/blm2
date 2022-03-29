<?php
/**
 * Wird in die index.php eingebunden; Seite zur Verwaltung der Gruppenkasse
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Gruppenkasse
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
        <a href="./?p=gruppe&amp;<?= time(); ?>">Board</a> |
        <a href="./?p=gruppe_mitgliederverwaltung&amp;<?= time(); ?>">Mitgliederverwaltung</a>
        <?php
        if ($ich->Rechte->GruppeBeschreibung || $ich->Rechte->GruppeBild || $ich->Rechte->GruppePasswort || $ich->Rechte->GruppeLoeschen) {
            echo ' | <a href="./?p=gruppe_einstellungen&amp;' . time() . '">Einstellungen</a>';
        }

        if ($ich->Rechte->Diplomatie) {
            echo ' | <a href="./?p=gruppe_diplomatie&amp;' . time() . '">Diplomatie (' . NeueGruppenDiplomatie($ich) . ')</a>';
        }
        ?>
        | <u><b>Gruppenkasse</b></u>
        | <a href="./?p=gruppe_logbuch&amp;<?= time(); ?>">Logbuch</a>
    </div>

    <?php
    if ($ich->Rechte->GruppeKasse) {
        ?>
        <h2>Aktueller Kontostand: <?= number_format($gruppe->Kasse, 2, ",", "."); ?> <?= $Currency; ?></h2>
        <form action="actions/gruppe.php" method="post" enctype="multipart/form-data">
            <table class="Liste" style="width: 600px;" cellspacing="0">
                <tr>
                    <th>Geld aus der Kasse an ein Mitglied überweisen</th>
                </tr>
                <tr>
                    <td>
                        <input type="hidden" name="a" value="16"/>
                        Überweise <input type="text" name="betrag" size="6"
                                         value="<?= number_format($gruppe->Kasse, 2, ",", ""); ?>"/> <?= $Currency; ?>
                        an
                        <select name="an">
                            <?php
                            $sql_abfrage = "SELECT
    ID,
    Name
FROM
    mitglieder
WHERE
    Gruppe='" . $ich->Gruppe . "';";
                            $sql_ergebnis = mysql_query($sql_abfrage);

                            while ($mitglied = mysql_fetch_object($sql_ergebnis)) {
                                if ($mitglied->ID == $_SESSION['blm_user'])
                                    echo '<option selected="selected" value="' . $mitglied->ID . '">' . htmlentities(stripslashes($mitglied->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                                else
                                    echo '<option value="' . $mitglied->ID . '">' . htmlentities(stripslashes($mitglied->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                            }
                            ?>
                        </select> auf die
                        <select name="bank">
                            <option selected="selected" value="1">Bank</option>
                            <option value="0">Hand</option>
                        </select>.
                        <input type="submit" value="Absenden"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
    ?>

    <table style="margin-top: 20px; width: 275px;" class="Liste">
        <tr>
            <th colspan="2">Kontostand:</th>
        </tr>
        <?php
        $sql_abfrage = "SELECT
    GruppeKassenStand,
    Name
FROM
    mitglieder
WHERE
    Gruppe='" . $ich->Gruppe . "'
ORDER BY
    GruppeKassenStand DESC;";
        $sql_ergebnis = mysql_query($sql_abfrage);

        while ($kontostand = mysql_fetch_object($sql_ergebnis)) {
            echo '<tr>
							<td>' . htmlentities(stripslashes($kontostand->Name), ENT_QUOTES, "UTF-8") . '</td>
							<td style="text-align: right;">' . number_format($kontostand->GruppeKassenStand, 2, ",", ".") . ' ' . $Currency . '</td>
						</tr>';
        }
        ?>
    </table>
    <?php
}
