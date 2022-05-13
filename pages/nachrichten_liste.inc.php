<?php
restrictSitter('Nachrichten');

$offset_in = getOrDefault($_GET, 'o_in', 0);
$offset_out = getOrDefault($_GET, 'o_out', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/korn.webp" alt=""/>
    <span>Nachrichten<?= createHelpLink(1, 13); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<h2>Posteingang</h2>
<p>Hier sehen Sie alle Ihre Nachrichten, die Sie empfangen haben.</p>

<?php
$messageCountIn = Database::getInstance()->getAllMessagesByAnCount($_SESSION['blm_user']);
$offset_in = verifyOffset($offset_in, $messageCountIn, messages_page_size);
?>
<table class="Liste Nachrichten">
    <tr>
        <th>Nr</th>
        <th>Datum / Zeit</th>
        <th>Von</th>
        <th>Betreff</th>
        <th>Gelesen?</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getAllMessagesByAnEntries($_SESSION['blm_user'], $offset_in, messages_page_size);
    $nr = $messageCountIn;
    foreach ($entries as $row) {
        ?>
        <tr class="<?= ($row['Gelesen'] == 0 ? 'Ungelesen' : 'Gelesen'); ?>">
            <td><?= $nr--; ?></td>
            <td><?= formatDateTime(strtotime($row['Zeit'])); ?></td>
            <td><?= createProfileLink($row['VonID'], $row['VonName']); ?></td>
            <td>
                <a href="/?p=nachrichten_lesen&amp;id=<?= $row['ID']; ?>"><?= escapeForOutput($row['Betreff']); ?></a>
            </td>
            <td><?= getYesOrNo($row['Gelesen']); ?></td>
            <td><a href="/actions/nachrichten.php?a=2&amp;id=<?= $row['ID']; ?>">Löschen</a></td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>Der Ordner ist leer.</i></td></tr>';
    }
    ?>
</table>

<?= createPaginationTable('/?p=nachrichten_liste', $offset_in, $messageCountIn, messages_page_size, 'o_in'); ?>

<div>
    <a href="/?p=nachrichten_schreiben">Neue Nachricht schreiben</a> |
    <a href="/actions/nachrichten.php?a=3">Alle Nachrichten löschen</a>
</div>

<h2>Postausgang</h2>
<p>Hier sehen Sie alle von Ihnen gesendete Nachrichten. Sie können nur Nachrichten löschen, die der Empfänger noch nicht
    gelesen hat.</p>

<?php
$messageCountOut = Database::getInstance()->getAllMessagesByVonCount($_SESSION['blm_user']);
$offset_out = verifyOffset($offset_out, $messageCountOut, messages_page_size);
?>
<table class="Liste Nachrichten">
    <tr>
        <th>Nr</th>
        <th>Datum / Zeit</th>
        <th>An</th>
        <th>Betreff</th>
        <th>Gelesen?</th>
        <th>Aktion</th>
    </tr>
    <?php
    $entries = Database::getInstance()->getAllMessagesByVonEntries($_SESSION['blm_user'], $offset_out, messages_page_size);
    $nr = $messageCountOut;
    foreach ($entries as $row) {
        ?>
        <tr>
            <td><?= $nr--; ?></td>
            <td><?= formatDateTime(strtotime($row['Zeit'])); ?></td>
            <td><?= createProfileLink($row['AnID'], $row['AnName']); ?></td>
            <td>
                <a href="/?p=nachrichten_lesen&amp;id=<?= $row['ID']; ?>"><?= escapeForOutput($row['Betreff']); ?></a>
            </td>
            <td><?= getYesOrNo($row['Gelesen']); ?></td>
            <td>
                <?php
                if ($row['Gelesen'] == 0 || $row['AnID'] === null) {
                    ?>
                    <a href="/actions/nachrichten.php?a=2&amp;id=<?= $row['ID']; ?>">Löschen</a>
                    <?php
                } else {
                    echo 'Löschen';
                }
                ?>
            </td>
        </tr>
        <?php
    }

    if (count($entries) == 0) {
        echo '<tr><td colspan="6" style="text-align: center;"><i>Der Ordner ist leer.</i></td></tr>';
    }
    ?>
</table>

<?= createPaginationTable('/?p=nachrichten_liste', $offset_out, $messageCountOut, messages_page_size, 'o_out'); ?>
