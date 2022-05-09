<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<ul>
    <li><a href="/?p=admin_test">Variablen Testseite</a></li>
    <li><a href="/?p=admin_markt">Marktplatz</a></li>
    <li><a href="/?p=admin_vertrag">Verträge</a></li>
    <li><a href="/?p=admin_gruppe">Gruppen</a></li>
    <li><a href="/?p=admin_benutzer">Benutzer</a></li>
    <li><a href="/?p=admin_auftrag">Aufträge</a></li>
    <li>
        Logbücher:
        <ul>
            <li><a href="/?p=admin_log_bank">Bank</a></li>
            <li><a href="/?p=admin_log_bioladen">Bioladen</a></li>
            <li><a href="/?p=admin_log_gruppenkasse">Gruppenkasse</a></li>
            <li><a href="/?p=admin_log_login">Login</a></li>
            <li><a href="/?p=admin_log_mafia">Mafia</a></li>
            <li><a href="/?p=admin_log_vertraege">Verträge</a></li>
        </ul>
    </li>
</ul>
