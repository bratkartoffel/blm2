<?php
/**
 * Wird in die index.php eingebunden; Profil eines Benuzers
 *
 * @version 1.0.1
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */

$sql_abfrage = "SELECT
    m.ID,
    m.Name,
    m.LastLogin,
    m.Beschreibung,
    m.IGMGesendet,
    m.IGMEmpfangen,
    g.Name AS NameG,
    g.ID AS ID_G,
    m.RegistriertAm,
    m.Gesperrt,
    m.Verwarnungen
FROM
    mitglieder m LEFT OUTER JOIN gruppe g ON m.Gruppe=g.ID
WHERE
    m.ID='" . intval($_GET['uid']) . "';";
$sql_ergebnis = mysql_query($sql_abfrage);        // Ruft alle wichtigen Daten des Users ab
$_SESSION['blm_queries']++;

$profil = mysql_fetch_object($sql_ergebnis);        // Holt sich die Daten

if (intval($profil->ID) == 0) {        // Wenn der User nicht gefunden wurde, dann...
    echo '<script type="text/javascript">document.location.href=\'./?p=rangliste\'</script>';        // ... geh wieder zurück zur Rangliste
    die();
}

if ($profil->Beschreibung == "") {    // Wenn der Benutzer keine Beschreibung angegeben hat...
    $profil->Beschreibung = "[i]Der Benutzer hat keine Beschreibung eingegeben.[/i]";    // .. dann schreib an der Stelle ne geeignete Meldung
}

if ($profil->NameG == "") {
    $profil->NameG = "<i>Keine</i>";
} else {
    $profil->NameG = '<a href="./?p=gruppe&amp;id=' . $profil->ID_G . '">' . htmlentities(stripslashes($profil->NameG), ENT_QUOTES, "UTF-8") . '</a>';
}

if ($profil->LastLogin == 0) {
    $profil->LastLogin = '<span style="color: red; font-weight: bold;">nie</span>';
} else {
    $profil->LastLogin = date("d.m.Y", $profil->LastLogin);
}

$platz = GetPlatz($profil->ID);
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/profil.png" alt="Profil"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Das Profil
            von <?= htmlentities($profil->Name, ENT_QUOTES, "UTF-8"); ?></td>
    </tr>
</table>

<?= $m; ?>

<b>
    Hier sehen Sie das Profil des Benutzers &quot;<?= htmlentities($profil->Name, ENT_QUOTES, "UTF-8"); ?>&quot;. Der
    Spieler ist f&uuml;r sein Profil selbst verantwortlich!
</b>
<br/>
<br/>
<table class="Liste" cellspacing="0" style="width: 400px;">
    <tr>
        <th>Kategorie:</th>
        <th>Wert:</th>
    </tr>
    <tr>
        <td>Name:</td>
        <td><?= htmlentities($profil->Name, ENT_QUOTES, "UTF-8"); ?></td>
    </tr>
    <tr>
        <td>Bild:</td>
        <td><img src="pics/spieler.php?uid=<?= $profil->ID; ?>"/></td>
    </tr>
    <tr>
        <td>Gruppe:</td>
        <td><?= $profil->NameG; ?></td>
    </tr>
    <tr>
        <td>Dabei seit:</td>
        <td><?= date("d.m.Y", $profil->RegistriertAm); ?></td>
    </tr>
    <tr>
        <td>Gesperrt:</td>
        <td><?php
            if ($profil->Gesperrt)
                echo '<span style="color: red; font-weight: bold;">JA</span>';
            else
                echo '<span style="color: green; font-weight: bold;">Nein</span>';
            ?></td>
    </tr>
    <tr>
        <td>Verwarnungen:</td>
        <td><?= $profil->Verwarnungen; ?></td>
    </tr>
    <tr>
        <td>Letzter Login:</td>
        <td><?= $profil->LastLogin; ?></td>
    </tr>
    <tr>
        <td>Punkte:</td>
        <td><?= number_format(GetSpielerPunkte($profil->ID), 0, ",", "."); ?> (Platz: <a
                    href="./?p=rangliste&amp;o=<?= intval(($platz - 1) / RANGLISTE_OFFSET); ?>&amp;highlight=<?= $profil->ID; ?>&amp;<?= time(); ?>"><?= $platz; ?></a>)
        </td>
    </tr>
    <tr>
        <td>Kontakt:</td>
        <td>
            <a href="./?p=nachrichten_schreiben&amp;an=<?= $profil->ID; ?>">IGM schreiben</a>
            |
            <a href="./?p=vertrag_neu&amp;an=<?= $profil->ID; ?>">Vertrag verfassen</a>
        </td>
    </tr>
    <tr>
        <td>IGM's gesendet:</td>
        <td><?= intval($profil->IGMGesendet); ?></td>
    </tr>
    <tr>
        <td>IGM's empfangen:</td>
        <td><?= intval($profil->IGMEmpfangen); ?></td>
    </tr>
    <tr>
        <td>Beschreibung:</td>
        <td><?= ReplaceBBCode($profil->Beschreibung, 75); ?></td>
    </tr>
</table>
