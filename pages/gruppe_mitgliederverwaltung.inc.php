<?php
/**
 * Wird in die index.php eingebunden; Dient zur Mitgliederverwaltung
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
    <table id="SeitenUeberschrift">
        <tr>
            <td style="width: 80px;"><img src="pics/big/gruppe.png" alt="Gruppe"/></td>
            <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Mitgliederverwaltung
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


    <div style="width: 650px; text-align: center; margin-bottom: 5px;">
        <a href="./?p=gruppe">Board</a> |
        <u><b>Mitgliederverwaltung</b></u>
        <?php
        if ($ich->Rechte->GruppeBeschreibung || $ich->Rechte->GruppeBild || $ich->Rechte->GruppePasswort || $ich->Rechte->GruppeLoeschen) {
            echo ' | <a href="./?p=gruppe_einstellungen">Einstellungen</a>';
        }

        if ($ich->Rechte->Diplomatie) {
            echo ' | <a href="./?p=gruppe_diplomatie">Diplomatie (' . NeueGruppenDiplomatie($ich) . ')</a>';
        }
        ?>
        | <a href="./?p=gruppe_kasse">Gruppenkasse</a>
        | <a href="./?p=gruppe_logbuch">Logbuch</a>
    </div>
    <table class="Liste" style="width: 600px;" cellspacing="0">
        <tr>
            <th rowspan="2" style="border-right: solid 1px #aa0000;">Name:</th>
            <th colspan="10" style="text-align: center; font-size: 120%;">Rechte</th>
            <?php
            if ($ich->Rechte->MitgliederRechte) {
                echo '<th rowspan="2" style="border-left: solid 1px #aa0000;">Aktion:</th>';
            }
            ?>
        </tr>
        <tr>
            <th style="padding: 3px 8px 3px 8px;">Nachricht schreiben:</th>
            <th style="padding: 3px 8px 3px 8px;">Nachricht löschen:</th>
            <th style="padding: 3px 8px 3px 8px;">Bild bearbeiten</th>
            <th style="padding: 3px 8px 3px 8px;">Beschreibung ändern:</th>
            <th style="padding: 3px 8px 3px 8px;">Diplomatie ändern:</th>
            <th style="padding: 3px 8px 3px 8px;">Kasse verwalten:</th>
            <th style="padding: 3px 8px 3px 8px;">Mitglied kicken:</th>
            <th style="padding: 3px 8px 3px 8px;">Passwort ändern:</th>
            <th style="padding: 3px 8px 3px 8px;">Rechte bearbeiten:</th>
            <th style="padding: 3px 8px 3px 8px;">Gruppe löschen:</th>
        </tr>
        <?php
        $sql_abfrage = "SELECT
    ID,
    Name,
    Punkte,
    GruppeRechte
FROM
    mitglieder
WHERE
    Gruppe='" . $ich->Gruppe . "'
ORDER BY
    Name ASC,
    Punkte DESC;";
        $sql_ergebnis = mysql_query($sql_abfrage);


        $bild[true] = '<img src="pics/small/ok.png" alt="Ja" style="border: none;" />';
        $bild[false] = '<img src="pics/small/error.png" alt="Nein" style="border: none;" />';

        while ($mitglied = mysql_fetch_object($sql_ergebnis)) {
            $name = '<a href="./?p=profil&amp;uid=' . $mitglied->ID . '">' . htmlentities(stripslashes($mitglied->Name), ENT_QUOTES, "UTF-8") . '</a>';
            $rechte = RechteGruppe(0, false, $mitglied->GruppeRechte);

            if ($mitglied->ID != $_SESSION['blm_user'] && $ich->Rechte->MitgliederRechte && !$rechte->Chef) {
                $r1 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=1" onclick="updGruppeRechte(' . $mitglied->ID . ', 1, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->NachrichtSchreiben] . '</a>';
                $r2 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=2" onclick="updGruppeRechte(' . $mitglied->ID . ', 2, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->NachrichtLoeschen] . '</a>';
                $r3 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=4" onclick="updGruppeRechte(' . $mitglied->ID . ', 4, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->GruppeBild] . '</a>';
                $r4 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=8" onclick="updGruppeRechte(' . $mitglied->ID . ', 8, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->GruppeBeschreibung] . '</a>';
                $r5 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=16" onclick="updGruppeRechte(' . $mitglied->ID . ', 16, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->MitgliedKicken] . '</a>';
                $r6 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=32" onclick="updGruppeRechte(' . $mitglied->ID . ', 32, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->GruppePasswort] . '</a>';
                $r7 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=64" onclick="updGruppeRechte(' . $mitglied->ID . ', 64, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->MitgliederRechte] . '</a>';
                $r8 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=128" onclick="updGruppeRechte(' . $mitglied->ID . ', 128, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->GruppeLoeschen] . '</a>';
                $r9 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=256" onclick="updGruppeRechte(' . $mitglied->ID . ', 256, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->Diplomatie] . '</a>';
                $r10 = '<a href="actions/gruppe.php?a=6&amp;id=' . $mitglied->ID . '&amp;recht=1024" onclick="updGruppeRechte(' . $mitglied->ID . ', 1024, this.getElementsByTagName(\'img\')[0]); return false;">' . $bild[$rechte->GruppeKasse] . '</a>';
            } else {
                $r1 = $bild[$rechte->NachrichtSchreiben];
                $r2 = $bild[$rechte->NachrichtLoeschen];
                $r3 = $bild[$rechte->GruppeBild];
                $r4 = $bild[$rechte->GruppeBeschreibung];
                $r5 = $bild[$rechte->MitgliedKicken];
                $r6 = $bild[$rechte->GruppePasswort];
                $r7 = $bild[$rechte->MitgliederRechte];
                $r8 = $bild[$rechte->GruppeLoeschen];
                $r9 = $bild[$rechte->Diplomatie];
                $r10 = $bild[$rechte->GruppeKasse];
            }
            ?>
            <tr>
                <td style="padding: 2px 8px 2px 8px;"><?= $name; ?></td>
                <td style="text-align: center;"><?= $r1; ?></td>
                <td style="text-align: center;"><?= $r2; ?></td>
                <td style="text-align: center;"><?= $r3; ?></td>
                <td style="text-align: center;"><?= $r4; ?></td>
                <td style="text-align: center;"><?= $r9; ?></td>
                <td style="text-align: center;"><?= $r10; ?></td>
                <td style="text-align: center;"><?= $r5; ?></td>
                <td style="text-align: center;"><?= $r6; ?></td>
                <td style="text-align: center;"><?= $r7; ?></td>
                <td style="text-align: center;"><?= $r8; ?></td>
                <?php
                if ($ich->Rechte->MitgliederRechte) {
                    if ($mitglied->ID != $_SESSION['blm_user'] && !$rechte->Chef) {
                        echo '<td style="text-align: center;"><a href="actions/gruppe.php?a=7&amp;id=' . $mitglied->ID . '">Kick</a></td>';
                    } else {
                        echo '<td style="text-align: center;">Kick</td>';
                    }
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}
