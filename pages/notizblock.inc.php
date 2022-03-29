<?php
/**
 * Wird in die index.php eingebunden; Formular zum Notizblock
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/writemail.png" alt="Notizblock"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Der Notizblock
            <a href="./?p=hilfe&amp;mod=1&amp;cat=14"><img src="pics/help.gif" alt="Hilfe" style="border: none;"/></a>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>Hier k&ouml;nnen Sie verschiedene Informationen speichern.</b><br/>
<br/>
<form action="./actions/notizblock.php" method="post" name="form_notizblock">
    <table class="Liste" cellspacing="0" style="width: 500px;">
        <tr>
            <th>
                Notizblock
            </th>
        </tr>
        <tr>
            <td style="text-align: center;">
                <textarea name="notizblock" cols="10" rows="10" style="width: 480px; height: 400px;" maxlength="4096"
                          onkeyup="ZeichenUebrig(this, document.form_notizblock.getElementsByTagName('span')[0]);"><?= htmlentities(stripslashes($ich->Notizblock), ENT_QUOTES, "UTF-8"); ?></textarea>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                Noch <span>4096</span> Zeichen &uuml;brig.
                <input type="submit" value="Speichern"/>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    <!--
    ZeichenUebrig(document.form_notizblock.notizblock, document.form_notizblock.getElementsByTagName('span')[0]);
    // -->
</script>
