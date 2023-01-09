<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kword.webp" alt=""/>
    <span>Die Regeln<?= createHelpLink(1, 19); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<h3>Jedes Spiel hat gewisse Regeln, an die sich die Mitspieler halten müssen.</h3>
<p>
    So ist das auch beim Bioladenmanager.
</p>
<p>
    Die Regeln dienen nur zum Schutz des Betreibers und den anderen Mitspielern. Die Strafen richten sich immer nach dem
    Ermessen der Admins.
</p>

<div class="Regel" id="p1">
    <p>1. Netiquette:</p>
    <p>
        Die Spieler sollten sich untereinander nett verhalten und keinen beleidigen oder persönlich angreifen.
        Missachtet ein Spieler diese Regel wissentlich, wiederholt und ohne Reue, so kann das von einem Reset bis zu
        (im schlimmsten Fall) einem Ban führen.
    </p>
    <p>
        Ausserdem sollte bei einer Nachricht auch auf die Groß- und Kleinschreibung geachtet werden, dies ist aber kein
        Muss.
    </p>
</div>

<div class="Regel" id="p2">
    <p>2. Accountresetting:</p>
    <p>
        Wer den Account eines anderen einfach zurücksetzt, löscht, unspielbar macht oder dessen Ruf schädigt, wird
        gebannt. Sowas gehört sich einfach nicht...
    </p>
</div>

<div class="Regel" id="p3">
    <p>3. Besitz:</p>
    <p>
        Alle Gegenstände im Spiel sind nur virtuell und Eigentum der Admins. Es besteht kein Anspruch in irgendeiner
        Weise.
    </p>
</div>

<div class="Regel" id="p4">
    <p>4. Spamming:</p>
    <p>
        Spamming ist verboten. Durch Spamming entstehen enorm große Datenmengen, die den Server nur unnötig belasten.
        Falls diese Regel missachtet wird, brauchen wir irgendwann einen neuen Server mit größeren Kapazitäten, was
        letztendlich zu Werbung und / oder Premium-Accounts führen wird.
    </p>
</div>

<div class="Regel" id="p5">
    <p>5. Multiaccounts:</p>
    <p>
        Jeder Spieler darf nur einen Account haben.
        Sobald ein Spieler mehr als 1 Account hat, werden beide gelöscht und gebannt.
    </p>
    <p>
        Es gibt für einen Admin weit mehr Möglichkeiten als manche denken. Für mich zählt (unter anderem) die
        Kombination aus IP, Browser, Zeit und dem Kontakt zu anderen Spielern (vor Allem der Versand von übermäßig
        billig oder teuren Waren).
    </p>
</div>

<div class="Regel" id="p6">
    <p>6. Unerlaubte Äusserungen:</p>
    <p>
        Jegliche rassistische Äusserungen, Gewaltverherrlichung und Pornografie werden ohne Vorwarnung sofort gelöscht
        und führen zu einem permanenten Ban von diesem Spiel. So was muss hier einfach nicht sein.
    </p>
</div>

<div class="Regel" id="p7">
    <p>7. Cheating / Bugusing:</p>
    <p>
        Jegliche Manipulation des Spiels und jede Ausnutzung von Spielfehlern, um sich einen unfairen Vorteil
        gegeüber den anderen Spielern zu verschaffen, führen zum Reset oder zur Löschung des Accounts.
    </p>
    <p>
        Falls Ihnen solche Fehler auffallen, dann schreiben Sie eine möglichst genaue Beschreibung des Fehlers in
        das Forum (Link in der Navigation)
    </p>
</div>

<div class="Regel" id="p8">
    <p>8. Passwortweitergabe:</p>
    <p>
        Ein Spieler darf sein Passwort nicht an andere Spieler weitergeben. Sobald ein Account von mehr als einer
        Person genutzt wird, wird dieser verwarnt und bei Nichtbeachtung der Verwarnung gesperrt.
    </p>
    <p>
        Bitte nutzen Sie stattdessen das integrierte "Sitter-Feature".
    </p>
</div>

<div class="Regel" id="p9">
    <p>9. Plünderungen:</p>
    <p>
        Der Inhalt der Gruppenkasse gehört den entsprechenden Mitgliedern laut der Statistik der Kasse. Es ist
        verboten, die Kasse leerzuräumen und dann die Gruppe zu verlassen.
    </p>
</div>

<div class="Regel" id="p10">
    <p>10. Accountpushing:</p>
    <p>
        Es ist verboten, einen anderen Account (z.B durch überhöhte Preise bei Verträgen) unspielbar zu machen, und
        somit einem anderen Account einem Vorteil zu verschaffen.
    </p>
</div>
