<div id="SeitenUeberschrift">
    <img src="/pics/big/kedit.webp" alt=""/>
    <span>Serverstatistik<?= createHelpLink(1, 18); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<p>
    Hier sehen Sie eine Serverweite Statistik.
</p>
<?php
$data = Database::getInstance()->getServerStatistics();
?>
<table class="Liste ServerStatistik">
    <tr>
        <th colspan="2">Finanzen</th>
    </tr>
    <tr>
        <td>Gesamteinnahmen:</td>
        <td><?= formatCurrency($data['EinnahmenGesamt']); ?></td>
    </tr>
    <tr>
        <td>Gesamtausgaben:</td>
        <td><?= formatCurrency($data['AusgabenGesamt']); ?></td>
    </tr>
    <tr>
        <td>=&gt; Ergebnis:</td>
        <td><?= formatCurrency($data['EinnahmenGesamt'] - $data['AusgabenGesamt']); ?></td>
    </tr>
    <tr>
        <td>Anzahl aller Aufträge:</td>
        <td><?php
            $data['AnzahlAuftraege']++;
            echo $data['AnzahlAuftraege'] - 1; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Auftrag:</td>
        <td><?= formatCurrency($data['AusgabenGesamt'] / $data['AnzahlAuftraege']); ?></td>
    </tr>
    <tr>
        <td>Gewinn / Auftrag:</td>
        <td><?= formatCurrency(($data['EinnahmenGesamt'] - $data['AusgabenGesamt']) / $data['AnzahlAuftraege']); ?></td>
    </tr>
</table>

<table class="Liste ServerStatistik">
    <tr>
        <th colspan="2">Forschung</th>
    </tr>
    <tr>
        <td>Ausgaben für Forschung:</td>
        <td><?= formatCurrency($data['AusgabenForschung']); ?></td>
    </tr>
    <tr>
        <td>Gesamtforschungslevel:</td>
        <td><?= $data['GesamtForschung']; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Forschungslevel:</td>
        <td><?= formatCurrency(($data['AusgabenForschung'] / $data['GesamtForschung'])); ?></td>
    </tr>
</table>

<table class="Liste ServerStatistik">
    <tr>
        <th colspan="2">Gebäude</th>
    </tr>
    <tr>
        <td>Ausgaben für Gebäude:</td>
        <td><?= formatCurrency($data['AusgabenGebaeude']); ?></td>
    </tr>
    <tr>
        <td>Gesamtgebäudelevel:</td>
        <td><?= $data['GesamtGebaeude']; ?></td>
    </tr>
    <tr>
        <td>Ausgaben / Gebäudelevel:</td>
        <td><?= formatCurrency(($data['AusgabenGebaeude'] / $data['GesamtGebaeude'])); ?></td>
    </tr>
</table>

<table class="Liste ServerStatistik">
    <tr>
        <th colspan="2">Allgemein</th>
    </tr>
    <tr>
        <td>Anzahl Spieler:</td>
        <td><?= $data['AnzahlSpieler']; ?></td>
    </tr>
    <tr>
        <td>Anzahl Gruppen:</td>
        <td><?= $data['AnzahlGruppen']; ?></td>
    </tr>
    <tr>
        <td>Anzahl der IGMs:</td>
        <td><?= $data['AnzahlIGMs']; ?></td>
    </tr>
    <tr>
        <td>IGMs / Spieler:</td>
        <td><?= formatCurrency($data['AnzahlIGMs'] / $data['AnzahlSpieler'], false); ?></td>
    </tr>
    <tr>
        <td>Spieler / Gruppe:</td>
        <td><?= ($data['AnzahlGruppen'] == 0 ? '0' : formatCurrency($data['AnzahlSpielerInGruppe'] / $data['AnzahlGruppen'], false)); ?></td>
    </tr>
    <tr>
        <td>
            Die Antwort auf die Frage nach dem Leben,<br/>
            dem Universum und dem ganzen Rest:
        </td>
        <td>42</td>
    </tr>
</table>
