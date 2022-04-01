<?php
restrictSitter('Notizblock');

if (isset($_GET['notizblock'])) $ich->Notizblock = $_GET['notizblock'];
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/writemail.png" alt="Notizblock"/></td>
        <td>Der Notizblock <a href="./?p=hilfe&amp;mod=1&amp;cat=14"><img src="/pics/help.gif" alt="Hilfe"/></a></td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<h3>
    Hier können Sie verschiedene Informationen speichern.
</h3>

<form action="/actions/notizblock.php" method="post" name="form_notizblock">
    <table class="Liste" style="width: 500px;">
        <tr>
            <th> Notizblock</th>
        </tr>
        <tr>
            <td>
                <textarea name="notizblock" style="width: 480px; height: 400px;" maxlength="4096"
                          onkeyup="ZeichenUebrig(this, document.getElementById('charsLeft'));"><?= sichere_ausgabe($ich->Notizblock, false); ?></textarea>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                Noch <span id="charsLeft">4096</span> Zeichen übrig.
                <input type="submit" value="Speichern"/>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    ZeichenUebrig(document.form_notizblock.notizblock, document.getElementById('charsLeft'));
</script>
