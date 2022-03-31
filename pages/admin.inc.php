<table id="SeitenUeberschrift">
    <tr>
        <td><img src="/pics/big/admin.png" alt=""/></td>
        <td>Administrations Bereich</td>
    </tr>
</table>

<?= CheckMessage(getOrDefault($_GET, 'm', 0)); ?>

<ul>
    <li><a href="./?p=admin_test">Variablen Testseite</a></li>
    <li><a href="./?p=admin_markt">Marktplatz</a></li>
    <li><a href="./?p=admin_vertrag">Verträge</a></li>
    <li><a href="./?p=admin_gruppe">Gruppen</a></li>
    <li><a href="./?p=admin_benutzer">Benutzer</a></li>
    <li><a href="./?p=admin_auftrag">Aufträge</a></li>
    <li>
        Logbücher:
        <ul>
            <li><a href="./?p=admin_log_bank">Bank</a></li>
            <li><a href="./?p=admin_log_bioladen">Bioladen</a></li>
            <li><a href="./?p=admin_log_gruppenkasse">Gruppenkasse</a></li>
            <li><a href="./?p=admin_log_login">Login</a></li>
            <li><a href="./?p=admin_log_mafia">Mafia</a></li>
            <li><a href="./?p=admin_log_vertraege">Verträge</a></li>
        </ul>
    </li>
    <li>
        Vorlagen:
        <ul>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=1">Verwarnung Multiaccounts</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=2">Verwarnung Passwortweitergabe</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=3">Verwarnung Bugusing</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=4">Verwarnung Spamming</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=5">Verwarnung Ausnutzung</a></li>
            <li><a href="./?p=admin_vorlage_verwarnungen&amp;cat=6">Verwarnung Accountpushing</a></li>
        </ul>
    </li>
</ul>
