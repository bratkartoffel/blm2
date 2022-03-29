<?php
/**
 * Wird in die index.php eingebunden; Verwaltung der Einstellungen der Gruppe
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Gruppeneinstellungen
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
    <div style="width: 700px; text-align: center; margin-bottom: 5px;">
        <a href="./?p=gruppe&amp;<?= intval(time()); ?>">Board</a> |
        <a href="./?p=gruppe_mitgliederverwaltung&amp;<?= intval(time()); ?>">Mitgliederverwaltung</a>
        <?php
        if ($ich->Rechte->GruppeBeschreibung || $ich->Rechte->GruppeBild || $ich->Rechte->GruppePasswort || $ich->Rechte->GruppeLoeschen) {
            echo ' | <u><b>Einstellungen</b></u>';
        }

        if ($ich->Rechte->Diplomatie) {
            echo ' | <a href="./?p=gruppe_diplomatie&amp;' . intval(time()) . '">Diplomatie (' . NeueGruppenDiplomatie($ich) . ')</a>';
        }
        ?>
        | <a href="./?p=gruppe_kasse&amp;<?= intval(time()); ?>">Gruppenkasse</a>
        | <a href="./?p=gruppe_logbuch&amp;<?= intval(time()); ?>">Logbuch</a>
    </div>

    <?php
    if ($ich->Rechte->GruppeBild) {
        ?>
        <form action="actions/gruppe.php" method="post" enctype="multipart/form-data">
            <table class="Liste" style="width: 600px;" cellspacing="0">
                <tr>
                    <th>Bild bearbeiten</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <i>
                            <img src="pics/gruppe.php?id=<?= $gruppe->ID; ?>&amp;<?= intval(time()); ?>"
                                 alt="Bisher wurde kein Bild hochgeladen..."/>
                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="hidden" name="a" value="8"/>
                        <input type="file" name="bild"/>
                        <input type="submit" value="Hochladen" style="margin-left: 20px;"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    if ($ich->Rechte->GruppeBeschreibung) {
        ?>
        <form action="actions/gruppe.php" method="post">
            <table class="Liste" style="width: 600px; margin-top: 30px;" cellspacing="0">
                <tr>
                    <th>Beschreibung bearbeiten</th>
                </tr>
                <?php
                if ($gruppe->Beschreibung != "") {
                    ?>
                    <tr>
                        <td>
                            <u><i><b>Aktuell:</b></i></u><br/>
                            <br/>
                            <?php
                            echo ReplaceBBCode($gruppe->Beschreibung, 75);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="hidden" name="a" value="9"/>
                        <textarea name="beschreibung" cols="60"
                                  rows="12"><?= htmlentities(stripslashes($gruppe->Beschreibung), ENT_QUOTES, "UTF-8"); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <input type="submit" value="Speichern"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    if ($ich->Rechte->GruppePasswort) {
        ?>
        <form action="actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="10"/>
            <table class="Liste" style="width: 600px; margin-top: 30px;" cellspacing="0">
                <tr>
                    <th colspan="2">Beitrittspasswort &auml;ndern</th>
                </tr>
                <tr>
                    <td>Passwort:</td>
                    <td><input type="password" name="pwd_1"/></td>
                </tr>
                <tr>
                    <td>Best&auml;tigung:</td>
                    <td><input type="password" name="pwd_2"/></td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">
                        <input type="submit" value="Speichern"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    if ($ich->Rechte->GruppeLoeschen) {
        ?>
        <form action="actions/gruppe.php" method="post">
            <input type="hidden" name="a" value="11"/>
            <table class="Liste" style="width: 600px; margin-top: 30px;" cellspacing="0">
                <tr>
                    <th>Gruppe l&ouml;schen</th>
                </tr>
                <tr>
                    <td>
                        <em>ACHTUNG!</em><br/>
                        <b>Dieser Schritt kann nicht r&uuml;ckg&auml;ngig gemacht werden!</b>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">
                        <input type="submit" value="Gruppe l&ouml;schen"
                               onclick="return confirm('Wollen Sie die Gruppe wirklich löschen?');"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }
}
