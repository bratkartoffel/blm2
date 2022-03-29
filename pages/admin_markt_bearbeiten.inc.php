<?php
/**
 * Wird in die index.php eingebunden; Seite zum Bearbeiten des Marktes für Admins
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

if (!istAdmin()) {
    header("location: ./?p=index&m=101");
    header("HTTP/1.0 404 Not Found");
    die();
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/marktplatz.png" alt="Marktplatz"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Marktplatz - Angebot
            bearbeiten
        </td>
    </tr>
</table>

<?= $m; ?>
<?php
$sql_abfrage = "SELECT
									*
								FROM
									marktplatz
								WHERE
									ID='" . intval($_GET['id']) . "';";
$sql_ergebnis = mysql_query($sql_abfrage);

$angebot = mysql_fetch_object($sql_ergebnis);
?>
<br/>
<form action="./actions/admin_markt.php" method="post">
    <input type="hidden" name="a" value="2"/>
    <input type="hidden" name="id" value="<?= $angebot->ID; ?>"/>
    <table class="Liste" cellspacing="0" style="width: 400px;">
        <tr>
            <th>Angebot bearbeiten</th>
        </tr>
        <tr>
            <td style="font-weight: bold; height: 40px;">
                <input type="text" name="menge" size="2" value="<?= $angebot->Menge; ?>"/> kg <select name="was">
                    <?php
                    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {
                        if ($angebot->Was == $i) {
                            echo '					<option value="' . $i . '" selected="selected">' . WarenName($i) . "</option>\n";
                        } else {
                            echo '					<option value="' . $i . '">' . WarenName($i) . "</option>\n";
                        }
                    }
                    ?>
                </select> zu <input type="text" name="preis" size="3"
                                    value="<?= number_format($angebot->Preis, 2, ",", "."); ?>"/> <?= $Currency; ?> pro
                kg
                <input type="submit" value="verkaufen"/>.
            </td>
        </tr>
    </table>
</form>
