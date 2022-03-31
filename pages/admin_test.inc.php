<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin_test.png" alt=""/></td>
        <td>Administrations Bereich - gesetzte Variablen
        </td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<fieldset>
    <legend>$_SERVER</legend>
    <pre><?= var_export($_SERVER, true); ?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
    <legend>$_SESSION</legend>
    <pre><?= var_export($_SESSION, true); ?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
    <legend>$ich</legend>
    <pre><?= var_export($ich, true); ?></pre>
</fieldset>

<p>
    <a href="./?p=admin">Zurück...</a>
</p>
