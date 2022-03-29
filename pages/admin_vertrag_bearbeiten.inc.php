<?php
/**
 * Wird in die index.php eingebunden; Seite zum Bearbeiten des Vertrags für Admins
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 *
 * @todo Eintrage speichern
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Marktplatz"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Verträge - Vertrag
            bearbeiten
        </td>
    </tr>
</table>

<?= $m; ?>
<br/>
<?php
$sql_abfrage = "SELECT
    *
FROM
    vertraege
WHERE
    ID='" . intval($_GET['id']) . "';";
$sql_ergebnis = mysql_query($sql_abfrage);

$v = mysql_fetch_object($sql_ergebnis);
?>
<form action="actions/admin_vertrag.php" method="post">
    <input type="hidden" name="a" value="2"/>
    <table class="Liste" style="width: 400px;">
        <tr>
            <th colspan="2">Vertragsdaten ändern</th>
        </tr>
        <tr>
            <td>Absender:</td>
            <td>
                <select name="von">
                    <?php
                    $sql_abfrage = "SELECT
    ID,
    Name
FROM
    mitglieder
ORDER BY
    Name ASC;";
                    $sql_ergebnis = mysql_query($sql_abfrage);

                    while ($u = mysql_fetch_object($sql_ergebnis)) {
                        if ($u->ID == $v->Von) {
                            echo '					<option value="' . $u->ID . '" selected="selected">' . htmlentities(stripslashes($u->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                        } else {
                            echo '					<option value="' . $u->ID . '">' . htmlentities(stripslashes($u->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Empfänger:</td>
            <td>
                <select name="an">
                    <?php
                    $sql_ergebnis = mysql_query($sql_abfrage);

                    while ($u = mysql_fetch_object($sql_ergebnis)) {
                        if ($u->ID == $v->An) {
                            echo '					<option value="' . $u->ID . '" selected="selected">' . htmlentities(stripslashes($u->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                        } else {
                            echo '					<option value="' . $u->ID . '">' . htmlentities(stripslashes($u->Name), ENT_QUOTES, "UTF-8") . '</option>' . "\n";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Was</td>
            <td>
                <select name="was">
                    <?php
                    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                        if ($i == $v->Was) {
                            echo '					<option value="' . $i . '" selected="selected">' . WarenName($i) . "</option>\n";
                        } else {
                            echo '					<option value="' . $i . '">' . WarenName($i) . "</option>\n";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Menge</td>
            <td><input type="menge" value="<?= $v->Menge; ?>" size="6"/></td>
        </tr>
        <tr>
            <td>Preis</td>
            <td><input type="preis" value="<?= number_format($v->Preis, 2, ",", "."); ?>" size="5"/> <?= $Currency; ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; padding-top: 5px;">
                <input type="submit" value="Speichern"/>
            </td>
        </tr>
    </table>
</form>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
