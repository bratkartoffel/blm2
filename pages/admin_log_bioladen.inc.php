<?php
/**
 * Wird in die index.php eingebunden; Seite zur Ansicht des Logbuches (Bioladen)
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

if ($_GET['name'] != "") {
    $filter = '%' . mysql_real_escape_string($_GET['name']) . '%';
} else {
    $filter = '%';
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Bioladenlogbuch"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Logbücher - Bioladen</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<form action="./" method="get">
    <input type="hidden" name="p" value="admin_log_bioladen"/>
    <h3>Filtern nach Namen:</h3>
    <input type="text" name="name" value="<?= htmlentities(stripslashes($_GET['name']), ENT_QUOTES, "UTF-8"); ?>"/>
    <input type="submit" value="Abschicken"/><br/>
</form>
<br/>
<table class="Liste" style="width: 720px;">
    <tr>
        <th>Wer</th>
        <th>Wann</th>
        <th>Was</th>
        <th>Wieviel</th>
        <th>Einzelpreis</th>
        <th>Gesamtpreis</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
									*,
									UNIX_TIMESTAMP(Wann) AS Wann
								FROM
									log_bioladen_view
								WHERE
									Wer LIKE '" . $filter . "'
								;";
    $sql_ergebnis = mysql_query($sql_abfrage);

    while ($l = mysql_fetch_object($sql_ergebnis)) {
        ?>
        <tr>
            <td><?= htmlentities(stripslashes($l->Wer), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= date("d.m.Y H:i:s", $l->Wann); ?></td>
            <td><?= Warenname($l->Was); ?></td>
            <td><?= number_format($l->Wieviel, 0, "", "."); ?> kg</td>
            <td><?= number_format($l->Einzelpreis, 2, ",", ".") . " " . $Currency; ?></td>
            <td><?= number_format($l->Gesamtpreis, 2, ",", ".") . " " . $Currency; ?></td>
        </tr>
        <?php
    }

    if (mysql_num_rows($sql_ergebnis) == 0) {
        ?>
        <tr>
            <td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td>
        </tr>
        <?php
    }
    ?>
</table>
