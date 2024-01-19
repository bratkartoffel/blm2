<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Vertraege');
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/mydocuments.webp" alt=""/>
    <span>Verträge<?= createHelpLink(1, 10); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<h2>Eingehende Verträge</h2>
<p>Hier sehen Sie alle Ihre eingehenden Verträge, die Sie noch nicht angenommen oder abgelehnt haben.</p>

<table class="Liste Vertraege">
    <tr>
        <th>Nr</th>
        <th>Datum</th>
        <th>Von</th>
        <th>Was</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getAllContractsByAnEquals($_SESSION['blm_user']);
    foreach ($entries as $entry) {
        ?>
        <tr>
            <td><?= $entry['ID']; ?></td>
            <td><?= formatDate(strtotime($entry['Wann'])); ?></td>
            <td><?= createProfileLink($entry['VonID'], $entry['VonName']); ?></td>
            <td><?= getItemName($entry['Was']); ?></td>
            <td><?= formatWeight($entry['Menge']); ?></td>
            <td><?= formatCurrency($entry['Preis']); ?></td>
            <td><?= formatCurrency($entry['Preis'] * $entry['Menge']); ?></td>
            <td>
                <a class="accept_contract" data-id="<?= $entry['ID']; ?>"
                   href="./actions/vertraege.php?a=2&amp;vid=<?= $entry['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Annehmen</a>
                |
                <a clasS="reject_contract" data-id="<?= $entry['ID']; ?>"
                   href="./actions/vertraege.php?a=3&amp;vid=<?= $entry['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Ablehnen</a>
            </td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="8" class="center"><i>Sie haben keine Verträge in diesem Ordner.</i></td></tr>';
    }
    ?>
</table>


<h2>Ausgehende Verträge</h2>
<p> Hier sehen Sie alle ausgehenden Verträge, die Ihr Gegenüber noch nicht angenommen hat.</p>

<table class="Liste Vertraege">
    <tr>
        <th>Nr</th>
        <th>Datum</th>
        <th>An</th>
        <th>Was</th>
        <th>Menge</th>
        <th>Preis / kg</th>
        <th>Gesamtpreis</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getAllContractsByVonEquals($_SESSION['blm_user']);
    foreach ($entries as $entry) {
        ?>
        <tr>
            <td><?= $entry['ID']; ?></td>
            <td><?= formatDate(strtotime($entry['Wann'])); ?></td>
            <td><?= createProfileLink($entry['AnID'], $entry['AnName']); ?></td>
            <td><?= getItemName($entry['Was']); ?></td>
            <td><?= formatWeight($entry['Menge']); ?></td>
            <td><?= formatCurrency($entry['Preis']); ?></td>
            <td><?= formatCurrency($entry['Preis'] * $entry['Menge']); ?></td>
            <td>
                <a href="./actions/vertraege.php?a=3&amp;vid=<?= $entry['ID']; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>">Zurückziehen</a>
            </td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="8" class="center"><i>Sie haben keine Verträge in diesem Ordner.</i></td></tr>';
    }
    ?>
</table>

<p>
    <a href="./?p=vertraege_neu">Neuen Vertrag aufsetzen</a>
</p>
