<?php
/**
 * Wird in die index.php eingebunden; Vorlagen für Verwarnungen
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/admin.png" alt="Logo der Unterseite"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Administrations Bereich - Vorlagen
            für Verwarnungen
        </td>
    </tr>
</table>

<?= $m; ?>

<br/>
<br/>
<?php
echo '<pre>' . wordwrap($vorlage_admin[intval($_GET['cat'])], 100) . '</pre>';
?><br/>
<br/>
<br/>
<p>
    <a href="./?p=nachrichten_schreiben&amp;betreff=Verwarnung&amp;admin_vorlage=<?= intval($_GET['cat']); ?>&amp;<?= time(); ?>">Nachricht
        erstellen</a><br/>
    <a href="./?p=admin">Zurück...</a>
</p>
