<?php
/**
 * Wird in die index.php eingebunden; Seite zur Ansicht des Logbuches (Gruppenkasse)
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

if ($_GET['wer'] != "") {
    $filter_wer = '%' . mysql_real_escape_string($_GET['wer']) . '%';
} else {
    $filter_wer = '%';
}

if ($_GET['wen'] != "") {
    $filter_wen = '%' . mysql_real_escape_string($_GET['wen']) . '%';
} else {
    $filter_wen = '%';
}

if ($_GET['gruppe'] != "") {
    $filter_gr = '%' . mysql_real_escape_string($_GET['gruppe']) . '%';
} else {
    $filter_gr = '%';
}
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Gruppenkassenlogbuch"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Admin - Logbücher - Gruppenkasse</td>
    </tr>
</table>

<?= $m; ?>
<br/>
<form action="./" method="get">
    <input type="hidden" name="p" value="admin_log_gruppenkasse"/>
    <h3>Filtern nach Absender:</h3>
    <input type="text" name="wer" value="<?= htmlentities(stripslashes($_GET['wer']), ENT_QUOTES, "UTF-8"); ?>"/>
    <br/>
    <h3>Filtern nach Empfänger:</h3>
    <input type="text" name="wen" value="<?= htmlentities(stripslashes($_GET['wen']), ENT_QUOTES, "UTF-8"); ?>"/>
    <br/>
    <h3>Filtern nach Gruppe:</h3>
    <input type="text" name="gruppe" value="<?= htmlentities(stripslashes($_GET['gruppe']), ENT_QUOTES, "UTF-8"); ?>"/>
    <br/>
    <br/>
    <input type="submit" value="Abschicken"/><br/>
</form>
<br/>
<table class="Liste" style="width: 720px;">
    <tr>
        <th>Wer</th>
        <th>Wen</th>
        <th>Gruppe</th>
        <th>Wann</th>
        <th>Wieviel</th>
        <th>Wohin</th>
    </tr>
    <?php
    $sql_abfrage = "SELECT
									*,
									UNIX_TIMESTAMP(Wann) AS Wann
								FROM
									log_gruppenkasse_view
								WHERE
									Wer LIKE '" . $filter_wer . "'
								AND
									Wen LIKE '" . $filter_wen . "'
								AND
									Gruppe LIKE '" . $filter_gr . "'
								;";
    $sql_ergebnis = mysql_query($sql_abfrage);

    while ($l = mysql_fetch_object($sql_ergebnis)) {
        ?>
        <tr>
            <td><?= htmlentities(stripslashes($l->Wer), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= htmlentities(stripslashes($l->Wen), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= htmlentities(stripslashes($l->Gruppe), ENT_QUOTES, "UTF-8"); ?></td>
            <td><?= date("d.m.Y H:i:s", $l->Wann); ?></td>
            <td><?= number_format($l->Wieviel, 2, ",", ".") . " " . $Currency; ?></td>
            <td><?= $l->Wohin; ?></td>
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
    ?>
</table>
