<?php
/**
 * Wird in die index.php eingebunden; Zeigt genauere Informationen zum Kriegsverlauf an
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/mafia.png" alt="Kriege"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Kriegdetails</td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Gruppe && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>
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
        | <a href="./?p=gruppe_kasse&amp;<?= time(); ?>">Gruppenkasse</a>
        | <a href="./?p=gruppe_logbuch&amp;<?= time(); ?>">Logbuch</a>
    </div>
    <br/>
    <br/>
    <?php
    $id = intval($_GET['id']);

    $sql_abfrage = "SELECT
    d.Seit,
    d.PunktePlus,
    d.PunkteMinus,
    d.Betrag,
    g.Name AS GegnerName,
    g.ID AS GegnerID
FROM
    gruppe_diplomatie d JOIN gruppe g ON d.An=g.ID
WHERE
    d.ID='" . $id . "'
AND
    d.Von='" . $ich->Gruppe . "';";
    $sql_ergebnis = mysql_query($sql_abfrage);

    $krieg = mysql_fetch_object($sql_ergebnis);

    if (intval($krieg->Seit) == 0) {
        die('<script type="text/javascript">document.location.href="./?p=gruppe&m=112";</script>');
    }
    ?>
    <table class="Liste" style="width: 450px;">
        <tr>
            <th colspan="2">
                Kriegsdetails:
            </th>
        </tr>
        <tr>
            <td>Krieg gegen:</td>
            <td>
                <a href="./?p=gruppe&amp;id=<?= $krieg->GegnerID; ?>&amp;<?= time(); ?>"><?= htmlentities(stripslashes($krieg->GegnerName), ENT_QUOTES, "UTF-8"); ?></a>
            </td>
        </tr>
        <tr>
            <td>Kriegsbeginn:</td>
            <td><?= date("d.m.Y - H:i:s", $krieg->Seit); ?>
        </tr>
        <tr>
            <td>Umkämpfter Betrag:</td>
            <td style="text-align: right;"><?= number_format($krieg->Betrag, 0, ",", ".") . " " . $Currency; ?></td>
        </tr>
        <tr>
            <td>Verlorene Punkte:</td>
            <td style="text-align: right;"><?= $krieg->PunkteMinus; ?></td>
        </tr>
        <tr>
            <td>Gewonnene Punkte:</td>
            <td style="text-align: right;"><?= $krieg->PunktePlus; ?></td>
        </tr>
        <tr>
            <td>Stand:</td>
            <td style="text-align: right;"><?php
                if ($krieg->PunktePlus - $krieg->PunkteMinus == 0) {
                    echo '<b>+- ' . ($krieg->PunktePlus - $krieg->PunkteMinus) . '</b>';
                }

                if ($krieg->PunktePlus - $krieg->PunkteMinus < 0) {
                    echo '<b>- ' . ($krieg->PunkteMinus - $krieg->PunktePlus) . '</b>';
                }

                if ($krieg->PunktePlus - $krieg->PunkteMinus > 0) {
                    echo '<b>+ ' . ($krieg->PunktePlus - $krieg->PunkteMinus) . '</b>';
                }
                ?></td>
        </tr>
        <tr>
            <td>Angebot unterbreiten:</td>
            <td style="text-align: center;"><a href="actions/gruppe.php?a=18&amp;id=<?= $id; ?>"
                                               onclick="return confirm('Wollen Sie wirklich den Krieg beenden und kapitulieren?\nAuswirkungen auf Ihre Gruppe:\n\n· Der umkämpfte Betrag (<?= number_format($krieg->Betrag, 0, ",", ".") . " " . $Currency; ?>) geht an den Gegner\n· Die Plantagen Ihrer Mitglieder werden um 2 Stufe gesenkt.\n· Jedes Ihrer Mitglieder verliert 5% seiner Punkte.');">Kapitulation</a>
            </td>
        </tr>
    </table>
    <?php
}
