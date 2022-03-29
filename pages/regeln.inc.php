<?php
/**
 * Wird in die index.php eingebunden; Spielregeln
 *
 * @version 1.0.0
 * @author Simon Frankenberger <simonfrankenberger@web.de>
 * @package blm2.pages
 */
?>
<table id="SeitenUeberschrift">
    <tr>
        <td style="width: 80px;"><img src="pics/big/regeln.png" alt="Regeln"/></td>
        <td style="font-size: 16pt; font-weight: bold; text-decoration: underline">Die Regeln
            <?php
            if (istAngemeldet()) {
                echo '<a href="./?p=hilfe&amp;mod=1&amp;cat=19"><img src="pics/help.gif" alt="Hilfe" style="border: none;" /></a>';
            }
            ?>
        </td>
    </tr>
</table>

<?= $m; ?>

<b>
    Jedes Spiel hat gewisse Regeln, an die sich die Mitspieler halten müssen.<br/>
    So ist das auch beim Bioladenmanager.<br/>
    Die Regeln dienen nur zum Schutz des Betreibers und den anderen Mitspielern. Die Strafen richten sich immer nach dem
    Ermessen des/der Admins.<br/>
</b>
<br/>
<table class="Liste" cellspacing="0">
    <tr>
        <th>Spielregeln</th>
    </tr>
    <tr>
        <td id="p1">
            <b>1. Nettique:</b><br/>
            <br/>
            Die Spieler sollten sich untereinander nett verhalten und keinen beleidigen oder persönlich angreifen.<br/>
            Missachtet ein Spieler diese Regel wissentlich, wiederholt und ohne Reue, so kann das von einem Reset bis zu
            (im schlimmsten Fall) einem Ban führen.<br/>
            <br/>
            Ausserdem sollte bei einer Nachricht auch auf die Groß- und Kleinschreibung geachtet werden,
            dies ist aber kein Muss.
        </td>
    </tr>
    <tr>
        <td id="p2">
            <b>2. Accountresetting:</b><br/>
            <br/>
            Wer den Account eines anderen einfach zurücksetzt, löscht, unspielbar macht oder dessen Ruf schädigt, wird
            gebannt.<br/>
            Sowas gehört sich einfach nicht...
        </td>
    </tr>
    <tr>
        <td id="p3">
            <b>3. Besitz:</b><br/>
            <br/>
            Alle Gegenstände im Spiel sind nur virtuell und Eigentum der Admins.
        </td>
    </tr>
    <tr>
        <td id="p4">
            <b>4. Spamming:</b><br/>
            <br/>
            Spamming ist verboten.<br/>
            Durch Spamming enstehen enorm große Datenmengen, die den Server nur unnö;tig belasten.<br/>
            Falls diese Regel missachtet wird, brauchen wir irgendwann einen neuen Server mit größeren Kapazitäten, was
            letztendlich zu mehr Werbung und/oder Premium-Accounts führen wird.
        </td>
    </tr>
    <tr>
        <td id="p5">
            <b>5. Multiaccounts:</b><br/>
            <br/>
            Jeder Spieler darf nur einen Account haben.<br/>
            Sobald ein Spieler mehr als 1 Account hat, werden beide gelöscht und gebannt.<br/>
            <br/>
            Es gibt für einen Admin weit mehr Möglichkeiten als manche denken. Für mich zählt (unter anderem) die
            Kombination aus IP, Browser, Zeit und dem Kontakt zu anderen Spielern (vorallem der Versand von übermäßig
            billig oder teueren Waren).
        </td>
    </tr>
    <tr>
        <td id="p6">
            <b>6. Unerlaubte Äusserungen:</b><br/>
            <br/>
            Jegliche rassistische Äusserungen, Gewaltverheerlichung und Pornographie werden ohne Vorwarung
            sofort gelöscht und führen zu einem permanenten Ban von diesem Spiel.<br/>
            So was muss hier einfach nicht sein.
        </td>
    </tr>
    <tr>
        <td id="p7">
            <b>7. Cheating / Bugusing:</b><br/>
            <br/>
            Jegliche Manipulation des Spiels und jede Ausnutzung von Spielfehlern, um sich einen unfairen Vorteil
            gegeüber den anderen Spielern zu verschaffen, führen zum Reset oder zur Löschung des Accounts.<br/>
            <br/>
            Falls Ihnen solche Fehler auffallen, dann schreiben Sie eine möglichst genaue Beschreibung des Fehlers in
            das Forum (Link in der Navigation)
        </td>
    </tr>
    <tr>
        <td id="p8">
            <b>8. Realismus:</b><br/>
            <br/>
            Das Spiel dient lediglich zur Belustigung und der Realismus ist nicht gewährleistet.<br/>
            Biobauern haben strenge Auflagen und deren Leben ist sicherlich nicht so leicht wie dieses Spiel.
        </td>
    </tr>
    <tr>
        <td id="p9">
            <b>9. Passwortweitergabe:</b><br/>
            <br/>
            Ein Spieler darf sein Passwort nicht an andere Spieler weitergeben. Sobald ein Account von mehr als einer
            Person genutzt wird, wird dieser verwarnt und bei Nichtbeachtung der Verwarnung gesperrt.<br/>
            <br/>
            Bitte nutzen Sie stattdessen das integrierte "Sitter-Feature".
        </td>
    </tr>
    <tr>
        <td id="p10">
            <b>10. Ausnutzung</b><br/>
            <br/>
            Der Inhalt der Gruppenkasse gehört den entsprechenden Mitgliedern laut der Statistik der Kasse. Es ist
            verboten, die Kasse leerzuräumen, und dann die Gruppe zu verlassen ("Plünderungen").
        </td>
    </tr>
    <tr>
        <td id="p12">
            <b>11. Accountpushing</b><br/>
            <br/>
            Es ist verboten, einen anderen Account (z.B durch überhöhte Preise bei Verträgen) unspielbar zu machen, und
            somit einem anderen Account einem Vorteil zu verschaffen.
        </td>
    </tr>
</table>