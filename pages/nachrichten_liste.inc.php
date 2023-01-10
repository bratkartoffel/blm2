<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

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
<table class="Liste Nachrichten" data-count="<?= $messageCountIn; ?>" id="MessagesIn">
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
    $nr = $messageCountIn - $offset_in * messages_page_size;
    foreach ($entries as $row) {
        ?>
        <tr class="<?= ($row['Gelesen'] == 0 ? 'Ungelesen' : 'Gelesen'); ?>" data-id="<?= $row['ID']; ?>">
            <td><?= $nr--; ?></td>
            <td><?= formatDateTime(strtotime($row['Zeit'])); ?></td>
            <td><?= createProfileLink($row['VonID'], $row['VonName']); ?></td>
            <td>
                <a href="/?p=nachrichten_lesen&amp;id=<?= $row['ID']; ?>"
                   id="read_<?= $row['ID']; ?>"><?= escapeForOutput($row['Betreff']); ?></a>
            </td>
            <td><?= getYesOrNo($row['Gelesen']); ?></td>
            <td id="action_<?=$row['ID'];?>">
                <a href="/actions/nachrichten.php?a=2&amp;id=<?= $row['ID']; ?>&amp;o_in=<?= $offset_in; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                   id="delete_<?= $row['ID']; ?>">Löschen</a>
            </td>
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
    <a href="/?p=nachrichten_schreiben" id="new_message">Neue Nachricht schreiben</a> |
    <a href="/actions/nachrichten.php?a=3&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>" id="delete_all_messages">Alle
        Nachrichten löschen</a>
</div>

<h2>Postausgang</h2>
<p>Hier sehen Sie alle von Ihnen gesendete Nachrichten. Sie können nur Nachrichten löschen, die der Empfänger noch nicht
    gelesen hat.</p>

<?php
$messageCountOut = Database::getInstance()->getAllMessagesByVonCount($_SESSION['blm_user']);
$offset_out = verifyOffset($offset_out, $messageCountOut, messages_page_size);
?>
<table class="Liste Nachrichten" data-count="<?= $messageCountOut; ?>" id="MessagesOut">
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
    $nr = $messageCountOut - $offset_out * messages_page_size;
    foreach ($entries as $row) {
        ?>
        <tr data-id="<?= $row['ID']; ?>">
            <td><?= $nr--; ?></td>
            <td><?= formatDateTime(strtotime($row['Zeit'])); ?></td>
            <td><?= createProfileLink($row['AnID'], $row['AnName']); ?></td>
            <td>
                <a href="/?p=nachrichten_lesen&amp;id=<?= $row['ID']; ?>"
                   id="read_<?= $row['ID']; ?>"><?= escapeForOutput($row['Betreff']); ?></a>
            </td>
            <td><?= getYesOrNo($row['Gelesen']); ?></td>
            <td id="action_<?=$row['ID'];?>">
                <?php
                if ($row['Gelesen'] == 0 || $row['AnID'] === null) {
                    ?>
                    <a href="/actions/nachrichten.php?a=2&amp;id=<?= $row['ID']; ?>&amp;o_out=<?= $offset_out; ?>&amp;token=<?= $_SESSION['blm_xsrf_token']; ?>"
                       id="delete_<?= $row['ID']; ?>">Löschen</a>
                    <?php
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
