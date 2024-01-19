<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/games.webp" alt=""/>
    <span>Impressum<?= createHelpLink(1, 21); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Das gesamte Spiel ist komplett kostenlos und OpenSource, und kann
    <a href="https://github.com/bratkartoffel/blm2" target="_blank">hier</a>
    heruntergeladen werden.
</p>
<p>
    Die Lizenz (MIT Licence) kann
    <a href="https://github.com/bratkartoffel/blm2/blob/master/LICENCE.md" target="_blank">hier</a>
    eingesehen werden.
</p>

<h2>Betrieben wird diese Installation durch:</h2>
<p class="indent">
    <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_name')); ?></span>
    <?php
    if (strlen(Config::get(Config::SECTION_BASE, 'admin_addr_line_1')) > 0) {
        ?>
        <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_addr_line_1')); ?></span>
        <?php
    }
    ?>
    <?php
    if (strlen(Config::get(Config::SECTION_BASE, 'admin_addr_line_2')) > 0) {
        ?>
        <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_addr_line_2')); ?></span>
        <?php
    }
    ?>
    <span>E-Mail: <a class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_email')); ?></a></span>
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

<h1>Datenschutzhinweise</h1>

<h2>
    Einführung
</h2>
<p>
    Für den Betrieb dieser Webseite habe ich mich dazu entscheiden, möglichst wenig Daten zu erheben und
    somit keine Drittanbieter einzubinden. Das bedeutet, dass diese Seite keine nicht technisch notwendigen
    Cookies setzt oder Analyse-Werkzeuge verwendet. Jeglicher Inhalt von externen Daten, die ich trotzdem zur
    Verfügung stellen möchte, werden erst nach aktivem Anklicken eines speziell gekennzeichneten Links
    angezeigt. Somit ist Ihr Recht auf
    <a href="https://de.wikipedia.org/wiki/Informationelle_Selbstbestimmung" target="_blank">Informationelle
        Selbstbestimmung</a>
    so gut wie möglich gewährleistet.
</p>
<h2>
    1. Name und Konktaktdaten des für die Verarbeitung Verantwortlichen
</h2>
<p>
    Diese Datenschutz-Information gilt für die Datenverarbeitung durch:
</p>
<p class="indent">
    <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_name')); ?></span>
    <?php
    if (strlen(Config::get(Config::SECTION_BASE, 'admin_addr_line_1')) > 0) {
        ?>
        <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_addr_line_1')); ?></span>
        <?php
    }
    ?>
    <?php
    if (strlen(Config::get(Config::SECTION_BASE, 'admin_addr_line_2')) > 0) {
        ?>
        <span class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_addr_line_2')); ?></span>
        <?php
    }
    ?>
    <span>E-Mail: <a class="bot"><?= obfuscate(Config::get(Config::SECTION_BASE, 'admin_email')); ?></a></span>
</p>
<p>
    Die Bestellung eines betrieblichen Datenschutzbeauftragten ist nicht erforderlich.
</p>
<h2>
    2. Erhebung und weitere Verarbeitung personenbezogener Daten inkl. Art und Zweck der Verwendung
</h2>
<p>
    Durch den Besuch dieser Webseite werden technisch bedingt Informationen von Ihnen übertragen. Diese
    könnten Sie wiederum identifizierbar machen. So wird durch jeden Zugriff mittels Ihres Browsers Ihre
    IP-Adresse (von Ihrem Provider zugewiesen) und eine Identifikation Ihres Browsers (User-Agent) übertragen.
    Dieses Verhalten des Browsers können Sie leider grundsätzlich nicht verhinden und sind für die Kommunikation
    innerhalb des Internets unabdingbar. Ich kann Ihnen aber versichern, dass diese Informationen auf dieser
    Seite sensibel und nur Ihren Interessen entsprechend verwende. Aus diesem Grund werden keine statistischen
    Auswertungen oder Analysen, auch nicht zu Werbezwecken, durchgeführt. Diese Informationen werden gemäss den
    gesetzlichen Anforderungen nur zweckdienlich zur Aufrechterhaltung des Dienstes verwendet. Sobald dieser
    Zweck erfüllt wurde, werden die Daten umgehend und automtisiert wieder gelöscht. Folgende Daten werden zu
    diesem Zweck erfasst und für <em>maximal 30 Tage</em> auf dem Server gespeichert:
</p>
<ul>
    <li>Die aufgerufene <a href="https://de.wikipedia.org/wiki/Domain" target="_blank">Domain</a>:
        <em>www.fraho.eu</em></li>
    <li>Die <a href="https://de.wikipedia.org/wiki/IP-Adresse" target="_blank">IP-Adresse</a> des
        anfragenden Rechners:
        <em>192.168.178.13</em></li>
    <li>Datum und Ihrzeit des Zugriffs:
        <em>2022/12/03T13:15:27Z</em></li>
    <li><a href="https://de.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Argument%C3%BCbertragung"
           target="_blank">Art</a> der Anfrage:
        <em>GET</em></li>
    <li>Quelle des Zugriffs (<a href="https://de.wikipedia.org/wiki/Referrer" target="_blank">Referrer-URL</a>):
        <em>https://www.fraho.eu</em></li>
    <li>Der <a href="https://de.wikipedia.org/wiki/HTTP-Statuscode" target="_blank">Statuscode</a> der Antwort:
        <em>200</em>
    </li>
    <li>Verwendeter Browser und ggf. das Betriebssystem Ihres Rechners
        (<a href="https://de.wikipedia.org/wiki/User_Agent" target="_blank">User-Agent</a>):
        <em>Mozilla/5.0 (Windows NT 6.1; Win64; x64) Gecko/X Firefox/X</em></li>
</ul>
<p>
    Jeder Spieler muss sich zum Spielen registrieren. Während der Registrierung werden neben einem frei wählbaren
    Pseudonym auch eine gültige EMail-Adresse und ein Passwort gespeichert. Die EMail-Adresse wird benötigt, um den
    Account initial zu aktivieren und im Falle eines vergessenen Passworts den Zugriff auf den Account
    wiederherzustellen.
</p>
<p>
    Zudem werden im Spiel alle Aktionen der Benutzers protokolliert. Diese Protokolle werden für die gesamte Dauer der
    Spielrunde aufgehoben. Einzige Ausnahme ist das Log der Anmeldungen, hier wird nach <em>30 Tagen</em> die IP-Adresse
    durch eine pseudoanonymisierte Prüfsumme ersetzt.
</p>
<p>
    Das Passwort wird mittels der modernen <a href="https://de.wikipedia.org/wiki/Hashfunktion" target="_blank">Hashfunktion</a>
    <a href="https://de.wikipedia.org/wiki/Argon2" target="_blank">Argon2</a> gespeichert.
    Abgesehen von den genannten Informationen werden <em>keine personenbezogenen Daten</em> gespeichert.
</p>
<p>
    Diese Daten werden durch mich zu folgenden Zwecken verarbeitet:
</p>
<ul>
    <li>Gewährleistung der Verfügbarkeit und Datenübertragen von der Webseite</li>
    <li>Erkennung und Blockierung von Angriffen</li>
    <li>Auswertung zur Stabilität und Erreichbarkeit des Systems</li>
    <li>Zu administrativen Zwecken (z.B. Identifikation von verwaisten Links)</li>
</ul>
<p>
    Rechtsgrundlage für die Datenverarbeitung ist Art. 6 Abs. 1 lit. f DSGVO. Mein berechtigtes Interesse folgt
    aus oben aufgelisteten Zwecken zur Datenerhebung. Ein Rückschluss der zur Verfügung stehenden Daten zu Ihrer
    Person ist für mich nicht möglich.
</p>
<p>
    Ein Download aller zu Ihrem Spielprofil zugehörigen Daten ist über die
    <a href="./?p=einstellungen#download_gdpr">Einstellungen</a> möglich.
</p>
<h2>
    3. Datensicherheit / Datenverarbeitung
</h2>
<p>
    Die gesamte Datenverarbeitung dieses Dienstes erfolgt auf einem selbst betriebenen Server. Ein Zugriff auf
    diesen Server ist lediglich durch mich möglich. Dieser Server steht in Nürnberg in einem gesicherten
    Rechenzentrum und wird durch <a href="https://www.hetzner.com/" target="_blank">Hetzner</a> zur Verfügung
    gestellt.
</p>
<p>
    Die Datenübertragung zwischen Ihrem Browser und dem Server ist über das verbreitete TLS-Verfahren
    (<a href="https://de.wikipedia.org/wiki/Transport_Layer_Security" target="_blank">Transport Layer
        Security</a>) mit der jeweils höchsten, von Ihrem Browser unterstützen Verschlüsselungsstufe
    (<a href="https://de.wikipedia.org/wiki/Cipher_Suite" target="_blank">Cipher Suite</a>) abgesichert.
</p>
<p>Im übrigen werden von mir geeignete technische und organisatorische Sicherheitsmassnahmen verwendet, um Ihre
    Daten gegenüber zufälligen oder vorsätzlichen Manipulationen (Verlust, Zerstörung oder Zugriff Dritter) zu
    schützen. Hierzu wurden möglichst viele zum aktuellen Zeitpunkt geltenden Hinweise, Best-Practises und
    Schutzhinweise der IT-Branche ergriffen, um ein Kompromittieren des Servers erheblich zu erschweren. Diese
    Massnahmen werden laufend mit neuen Erkenntnissen verbessert und beinhalten aktuell unter Anderem:
</p>
<ul>
    <li>Verschlüsselte Erreichbarkeit via HTTPS auf Basis moderner Cipher-Suites (TLS 1.3 / TLS 1.2 mit AEAD,
        PFS und 256-Bit-ECDSA-Zertifikat)
    </li>
    <li>Verzicht von technisch nicht notwendigen Cookies</li>
    <li>Keine Einbindung von Ressourcen aus (unsicheren) Drittquellen</li>
    <li>Gehärtetes GNU/Linux-System auf Basis von Ubuntu 22.04</li>
    <li>Moderne Sicherheitsfeatures wie DNSSEC, OCSP-Stapling und CAA</li>
    <li>Einsatz aktueller HTTP-Security-Header wie HSTS, Expect-CT und Referrer-Policy</li>
    <li>Administrativer Zugriff nur Mittels SSH-Schlüssel</li>
    <li>Voll verschlüsselte Festplatten</li>
    <li>Schutz gegen Verlust von Daten mittels ZFS und periodischen Backups</li>
    <li>Installation von Sicherheitsupdates innert weniger Stunden</li>
    <li>Abschottung aller Dienste gegeneinander mittels Container</li>
</ul>
<p>
    Von dem Sicherheitsniveau dieser Webseite können Sie sich gerne zum Beispiel beim
    <a href="https://www.ssllabs.com/ssltest/analyze.html?d=blm2.fraho.eu" target="_blank">Qualys SSL Labs</a>
    überzeugen.
</p>
<h2>
    4. Aktualität und Änderung dieser Datenschutzhinweise
</h2>
<p>
    Diese Datenschutzhinweise sind aktuell gültig und haben den Stand Januar 2023.
</p>
<p>
    Durch die Weiterentwicklung der Webseite oder auf Grund geänderter gesetzlicher Anforderungen kann es
    notwendig werden, diese Datenschutzhinweise anzupassen. Die jeweils aktuellen und gültigen
    Datenschutzhinweise können jederzeit unter dem Link eingesehen werden.
</p>
<h2>
    5. Adressverarbeitung
</h2>
<p>
    Alle die auf dieser Webseite angegebenen Kontaktinformationen von mir dienen ausdrücklich nur zu
    Informationszwecken bzw. zur Kontaktaufnahme. Sie dürfen insbesondere nicht für die Zusendung von Werbung,
    Spam und ähnliches genutzt werden. Einer werblichen Nutzung dieser Daten wird deshalb hiermit widersprochen.
    Sollten diese Informationen dennoch zu den vorstehend genannten Zwecken genutzt werden, behalte ich mir
    etwaige rechtliche Schritte vor.
</p>
