<?php
/**
 * Wird in die index.php eingebunden; Hauptseite des Administrations Bereichs
 *
 * @version 1.0.2
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 *
 * @todo Gruppenverwaltung
 * @todo Benutzerverwaltung
 * @todo Auftragsverwaltung
 *
 * @todo Logbuch Nachrichten
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Logo der Unterseite"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Administrations Bereich</td>
    </tr>
</table>

<?= $m; ?>

<br/>
<br/>
<ul style="margin-left: 20px;">
    <li><a href="./?p=admin_test&amp;<?= time(); ?>">Variablen Testseite</a></li>
    <li><a href="./?p=admin_markt&amp;<?= time(); ?>">Marktplatz</a></li>
    <li><a href="./?p=admin_vertrag&amp;<?= time(); ?>">Verträge</a></li>
    <li><a href="./?p=admin_gruppe&amp;<?= time(); ?>">Gruppen</a></li>
    <li><a href="./?p=admin_benutzer&amp;<?= time(); ?>">Benutzer</a></li>
    <li><a href="./?p=admin_auftrag&amp;<?= time(); ?>">Aufträge</a></li>
    <li><a href="./?p=admin_changelog&amp;<?= time(); ?>">Changelog</a></li>
    <li>
        Logbücher:
        <ul>
            <li><a href="./?p=admin_log_bank&amp;<?= time(); ?>">Bank</a></li>
            <li><a href="./?p=admin_log_bioladen&amp;<?= time(); ?>">Bioladen</a></li>
            <li><a href="./?p=admin_log_gruppenkasse&amp;<?= time(); ?>">Gruppenkasse</a></li>
            <li><a href="./?p=admin_log_login&amp;<?= time(); ?>">Login</a></li>
            <li><a href="./?p=admin_log_mafia&amp;<?= time(); ?>">Mafia</a></li>
            <li><a href="./?p=admin_log_vertraege&amp;<?= time(); ?>">Verträge</a></li>
        </ul>
    </li>
    <li>
        Vorlagen:
        <ul>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=1&amp;<?= time(); ?>">Verwarnung
                    Multiaccounts</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=2&amp;<?= time(); ?>">Verwarnung
                    Passwortweitergabe</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=3&amp;<?= time(); ?>">Verwarnung Bugusing</a>
            </li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=4&amp;<?= time(); ?>">Verwarnung Spamming</a>
            </li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=5&amp;<?= time(); ?>">Verwarnung Ausnutzung</a>
            </li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=6&amp;<?= time(); ?>">Verwarnung
                    Accountpushing</a></li>
        </ul>
    </li>
</ul>
