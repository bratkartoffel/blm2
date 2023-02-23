<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Login_Manager.webp" alt=""/>
    <span>Administrationsbereich - Benutzer importieren</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Mit dieser Funktion kann ein mittels GDPR-Export exportierter und gelöschter Benutzer wieder importiert werden.
</p>
Folgende Funktionen können eingestellt werden:
<ul>
    <li><em>Signatur prüfen:</em> Jede exportierte Tabelle ist mit einer Prüfsumme gesichert um Manipulation durch den
        Benutzer zu verhindern. Die Signatur wird mittels des <code>random_secret</code> aus der Konfiguration
        durchgeführt. Das bedeutet jedoch auch, dass der Export mit aktivierter Option nicht auf einer anderen Umgebung
        importiert werden kann.
    </li>
    <li><em>Neue ID:</em> Mit dieser Option wird die Benutzer-ID neu generiert und nicht aus dem Export übernommen. Dies
        ist hilfreich um den Export auf einer anderen Umgebung zu importieren, wo diese ID bereits belegt ist.
    </li>
    <li><em>Runde ignorieren:</em> Ist diese Option nicht aktiv, so lässt sich ein Export nur in der selben Runde wieder
        einspielen.
    </li>
</ul>

<div class="form AdminImportUser">
    <form action="/actions/admin_benutzer.php" method="post"  enctype="multipart/form-data">
        <input type="hidden" name="a" value="6"/>
        <input type="hidden" name="token" value="<?= $_SESSION['blm_xsrf_token']; ?>"/>
        <header>Benutzer importieren</header>
        <div>
            <label for="import">Datei:</label>
            <input type="file" name="import" id="import" accept="application/zip"/>
        </div>
        <div>
            <label for="verify">Signatur prüfen?</label>
            <input type="checkbox" name="verify" value="1" id="verify" checked/>
        </div>
        <div>
            <label for="new_id">Neue ID?</label>
            <input type="checkbox" name="new_id" value="1" id="new_id"/>
        </div>
        <div>
            <label for="ignore_round">Runde ignorieren?</label>
            <input type="checkbox" name="ignore_round" value="1" id="ignore_round"/>
        </div>
        <div>
            <label for="with_logs">Logs importieren?</label>
            <input type="checkbox" name="with_logs" value="1" id="with_logs"/>
        </div>
        <div>
            <input type="submit" value="Importieren"/>
        </div>
    </form>
</div>

<p>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</p>
