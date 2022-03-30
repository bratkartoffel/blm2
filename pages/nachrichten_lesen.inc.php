<?php
/**
 * Wird in die index.php eingebunden; Zeigt eine Nachricht an.
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

$nachricht_id = intval($_GET['nid']);
if ($ich->Sitter->Nachrichten || !$_SESSION['blm_sitter']) {

    $sql_abfrage = "SELECT
    Von,
    Zeit
FROM
    nachrichten
WHERE
    ID='" . $nachricht_id . "'
AND
(
        Von='" . $_SESSION['blm_user'] . "'
    OR
        An='" . $_SESSION['blm_user'] . "'
);";
    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $temp = mysql_fetch_object($sql_ergebnis);

    if (intval($temp->Zeit) == 0) {                            // Nachricht konnte nicht gefunden werden...
        echo '<script type="text/javascript">document.location.href="./?p=nachrichten_liste";</script>';
        die();
    }

    if ($temp->Von == $_SESSION['blm_user']) {        // Der Benutzer sieht sich eine Nachricht aus seinem Postausgang an
        $sql_abfrage = "SELECT
    m.Name AS Empfaenger,
    m.ID AS EmpfaengerID,
    n.Zeit,
    n.Betreff,
    n.Nachricht,
    n.Gelesen
FROM
    nachrichten n LEFT OUTER JOIN mitglieder m ON m.ID=n.An
WHERE
    n.ID='" . $nachricht_id . "'
;";
        $post_eingang = false;
    } else {        // Der Benutzer sieht sich eine Nachricht aus seinem Posteingang an
        $sql_abfrage = "UPDATE
	nachrichten
SET
	Gelesen=1
WHERE
	ID='" . $nachricht_id . "'
;";
        mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "UPDATE
	log_nachrichten
SET
	Gelesen=1
WHERE
	Orig_ID='" . $nachricht_id . "'
;";
        mysql_query($sql_abfrage);
        $_SESSION['blm_queries']++;

        $sql_abfrage = "SELECT
	m.Name AS Absender,
	m.ID AS AbsenderID,
	n.Zeit,
	n.Betreff,
	n.Nachricht,
	n.Gelesen
FROM
	nachrichten n LEFT OUTER JOIN mitglieder m ON m.ID=n.Von
WHERE
	n.ID='" . $nachricht_id . "'
;";

        $post_eingang = true;
    }

    $sql_ergebnis = mysql_query($sql_abfrage);
    $_SESSION['blm_queries']++;

    $nachricht = mysql_fetch_object($sql_ergebnis);

    if ($nachricht->Absender == "" && $nachricht->Empfaenger == "") {        // Die Nachricht hat keinen Absender?
        $nachricht->Absender = "-System-";    // gut, dann kommt sie halt vom System
    }

    if ($post_eingang && $nachricht->AbsenderID == "")
        $nachricht->Absender = "-User gelöscht-";
}
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/readmail.png" alt="Nachricht lesen"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Nachricht lesen
                <a href="./?p=hilfe&amp;mod=1&amp;cat=13"><img src="pics/help.gif" alt="Hilfe"
                                                               style="border: none;"/></a>
            </td>
        </tr>
    </table>
<?php
if (!$ich->Sitter->Nachrichten && $_SESSION['blm_sitter']) {
    echo '<h2 style="color: red; font-weight: bold;">Ihre Rechte reichen nicht aus, um diesen Bereich sitten zu dürfen!</h2>';
} else {
    ?>

    <?= $m; ?>

    <table class="Liste" cellspacing="0">
        <tr>
            <th colspan="2">Nachricht lesen</th>
        </tr>
        <tr>
            <td style="width: 70px; border-right: solid 1px black;"><b><?php
                    if ($nachricht->Absender != "") {
                        echo 'Absender:';
                    } else {
                        echo 'Empfänger:';
                    }
                    ?></b></td>
            <td><?php
                if ($nachricht->Absender == "-User gelöscht-") {
                    echo $nachricht->Absender;
                } else {
                    if ($nachricht->Absender != "") {
                        echo '<a href="./?p=profil&amp;uid=' . $nachricht->AbsenderID . '">' . htmlentities(stripslashes($nachricht->Absender), ENT_QUOTES, "UTF-8") . '</a>';
                    } else {
                        echo '<a href="./?p=profil&amp;uid=' . $nachricht->EmpfaengerID . '">' . htmlentities(stripslashes($nachricht->Empfaenger), ENT_QUOTES, "UTF-8") . '</a>';
                    }
                }
                ?></td>
        </tr>
        <tr>
            <td style="border-right: solid 1px black;"><b>Zeit:</b></td>
            <td><?= date("d.m.Y - H:i:s", $nachricht->Zeit); ?></td>
        </tr>
        <tr>
            <td style="border-right: solid 1px black;"><b>Betreff:</b></td>
            <td><?= htmlentities(stripslashes($nachricht->Betreff), ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
        <tr>
            <td style="border-top-style: solid; border-top-width: 1px; border-right: solid 1px black;"><b>Nachricht:</b>
            </td>
            <td style="border-top-style: solid; border-top-width: 1px;"><?= stripslashes(ReplaceBBCode($nachricht->Nachricht)); ?></td>
        </tr>
        <tr>
            <td style="padding-top: 20px; border-right: solid 1px black;"><b>Aktion:</b></td>
            <td style="padding-top: 20px;">
                <a href="./?p=nachrichten_liste">
                    <img src="pics/small/back.png" border="0" alt="Zurück"/>
                </a>
                <?php
                if ($nachricht->Absender != "-User gelöscht-") {
                    if ($post_eingang || !$nachricht->Gelesen) {
                        ?>
                        <a href="./actions/nachrichten.php?a=2&amp;id=<?= $nachricht_id; ?>">
                            <img src="./pics/small/error.png" border="0" alt="Nachricht löschen"/>
                        </a>
                        <?php
                    }
                    if (property_exists($nachricht, "Empfaenger")) {
                        ?>
                        <a href="?p=nachrichten_schreiben&amp;an=<?= $nachricht->AbsenderID; ?>&amp;answer=<?= $nachricht_id; ?>">
                            <img src="pics/small/answermail.png" style="border: none;" alt="Antworten"/>
                        </a>
                        <?php
                    }
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
}
