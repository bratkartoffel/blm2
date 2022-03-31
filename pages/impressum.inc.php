<?php
/**
 * Wird in die index.php eingebunden; Das Impressum, sehr wichtig ;)
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/impressum.png" alt="Impressum"/></td>
        <td>Impressum
            <?php
            if (istAngemeldet()) {
                echo '<a href="./?p=hilfe&amp;mod=1&amp;cat=21"><img src="/pics/help.gif" alt="Hilfe" style="border: none;" /></a>';
            }
            ?>
        </td>
    </tr>
</table>

<?= $m; ?>

<span style="font-weight: bold;">
	 Das gesamte Spiel ist <em>komplett kostenlos</em> und OpenSource, und kann (bald) <a href="https://github.com/bratkartoffel/blm2">hier</a> heruntergeladen werden.<br/>
	 <br/>
	 Die Lizenz (MIT Licence) kann <a href="LICENCE.md">hier</a> eingesehen werden.<br/>
	 <br/>
	 Der Banner oben dient bisher lediglich <em>zum Abdecken der Serverkosten</em> und verfolgt <em>keine Gewinnabsicht</em> meinerseits!
</span>

<h2>Programmiert wurde die Grundversion von:</h2>
<span style="font-weight: bold;">
	<br/>
	Simon Frankenberger<br/>
	Gerhardinger Str. 18<br/>
	94474 Vilshofen<br/>
    <a href="mailto:page-contact-1664@fraho.eu">page-contact-1664@fraho.eu</a>
	<br/>
</span>

<h2>Disclaimer</h2>
<span style="font-weight: bold;">
	Ich übernehme keinerlei Haftung für Links, die auf andere Seiten verweisen.<br/>
	Die Links werden kontrolliert, jedoch kann es passieren, dass mal der eine oder andere Link übersehen
	wird.<br/>
</span>

<h2>Bilder und Grafiken</h2>
<span style="font-weight: bold;">
	Alle Bilder der Gebäude und der Gemüsesorten sind von mir aufgenommen, und sind somit frei
	verfügbar und dürfen beliebig weiterverwendet werden.<br/>
	Die Grafiken und die Smileys sind dem &quot;CrystalPack&quot; von <a
            href="http://www.crystalxp.net">CrystalXP.net</a> entnommen und sind
	frei verfügbar unter der GPL.
</span>
