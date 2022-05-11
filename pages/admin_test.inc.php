<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Testseite</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<fieldset class="AdminVariables">
    <legend>$_SERVER</legend>
    <pre><?= var_export($_SERVER, true); ?></pre>
</fieldset>
<fieldset class="AdminVariables">
    <legend>$_SESSION</legend>
    <pre><?= var_export($_SESSION, true); ?></pre>
</fieldset>
<fieldset class="AdminVariables">
    <legend>phpInfo()</legend>
    <?php
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();
    $matches = array();
    preg_match('@<body>(.*)</body>@iUsm', $phpinfo, $matches);
    $phpinfo = $matches[1];

    echo $phpinfo;
    ?>
</fieldset>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
