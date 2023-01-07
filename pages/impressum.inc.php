<div id="SeitenUeberschrift">
    <img src="/pics/big/games.webp" alt=""/>
    <span>Impressum<?= createHelpLink(1, 21); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Das gesamte Spiel ist komplett kostenlos und OpenSource, und kann
    <a href="https://github.com/bratkartoffel/blm2" target="_blank">hier</a>
    heruntergeladen werden.
</p>
<p>
    Die Lizenz (MIT Licence) kann <a href="https://github.com/bratkartoffel/blm2/blob/master/LICENCE.md" target="_blank">hier</a> eingesehen werden.
</p>

<h2>Betrieben wird diese Installation durch:</h2>
<p class="indent">
    <span class="bot"><?= obfuscate(admin_name); ?></span>
    <?php
    if (strlen(admin_addr_line_1) > 0) {
        ?>
        <span class="bot"><?= obfuscate(admin_addr_line_1); ?></span>
        <?php
    }
    ?>
    <?php
    if (strlen(admin_addr_line_2) > 0) {
        ?>
        <span class="bot"><?= obfuscate(admin_addr_line_2); ?></span>
        <?php
    }
    ?>
    <span>E-Mail: <a class="bot"><?= obfuscate(admin_email); ?></a></span>
</p>

<h2>Programmiert wurde das Original von:</h2>
<p class="indent">
    <span class="bot"><?= obfuscate('Simon Frankenberger'); ?></span>
    <span>E-Mail: <a class="bot"><?= obfuscate('simon-blm2@fraho.eu'); ?></a></span>
</p>

<h2>Disclaimer</h2>
<p>
    Ich übernehme keinerlei Haftung für Links, die auf andere Seiten verweisen. Die Links werden in unregelmässigen
    Abständen kontrolliert, jedoch kann es passieren, dass mal der eine oder andere Link übersehen wird.
</p>

<h2>Bilder und Grafiken</h2>
<p>
    Alle Fotos wurden von <a href="https://unsplash.com/license" target="_blank">unsplash.com</a> genommen.
    Die Icons stammen entstammen dem "Crystal Clear Icons By Everaldo" und stehen unter der LGPL 3.0.
    Die Bilder und Grafiken unterhalb von "pics/style" wurden von mir gezeichnet und stehen wie der Rest des
    Programs unter der MIT Licence.<br>
    Der Font für das Captcha wurde als "Public Domain" released und ist frei verfügbar auf
    <a href="https://www.fontspace.com/sportsball-font-f30615" target="_blank">fontspace.com</a>.
</p>

<script>
    deobfuscate();
</script>