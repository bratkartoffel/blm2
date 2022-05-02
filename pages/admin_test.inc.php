<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.png" alt=""/>
    <span>Administrationsbereich - Testseite</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<fieldset>
    <legend>$_SERVER</legend>
    <pre><?= var_export($_SERVER, true); ?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
    <legend>$_SESSION</legend>
    <pre><?= var_export($_SESSION, true); ?></pre>
</fieldset>
<fieldset style="margin-top: 20px;">
    <legend>phpInfo()</legend>
    <pre><?php phpinfo(); ?></pre>
</fieldset>

<p>
    <a href="/?p=admin">Zurück...</a>
</p>
