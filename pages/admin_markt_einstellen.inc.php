<?php
/**
 * Wird in die index.php eingebunden; Seite zur Hinzufügen von Angeboten auf den Markt für Admins
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt="Marktplatz"/></td>
        <td>Admin - Marktplatz - Neues Angebot
        </td>
    </tr>
</table>

<?= $m; ?>

<br/>
<form action="./actions/admin_markt.php" method="post">
    <input type="hidden" name="a" value="1"/>
    <table class="Liste" cellspacing="0" style="width: 400px;">
        <tr>
            <th>Angebot einstellen</th>
        </tr>
        <tr>
            <td style="font-weight: bold; height: 40px;">
                <input type="text" name="menge" size="2" value="0"/> kg <select name="was">
                    <?php
                    $eintrag = false;        // Bisher haben wir noch keine Waren auf Lager, welche wir ausgegeben haben.

                    for ($i = 1; $i <= ANZAHL_WAREN; $i++) {        // Schaut das ganze Lager durch, und gibt nur die Einträge aus, bei denen der Lagerstand > 0 ist
                        echo '					<option value="' . $i . '">' . WarenName($i) . "</option>\n";
                    }
                    ?>
                </select> zu <input type="text" name="preis" size="3" value="0,00"/> <?= $Currency; ?> pro kg
                <input type="submit" value="verkaufen"/>.
            </td>
        </tr>
    </table>
</form>
<p>
    <a href="./?p=admin">Zurück...</a>
</p>
