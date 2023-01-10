<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

$absender = getOrDefault($_GET, 'absender');
$empfaenger = getOrDefault($_GET, 'empfaenger');
$offset = getOrDefault($_GET, 'o', 0);
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/kservices.webp" alt=""/>
    <span>Administrationsbereich - Nachrichten Logbuch</span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<div id="FilterForm">
    <form action="/" method="get">
        <input type="hidden" name="p" value="admin_log_nachrichten"/>
        <label for="absender">Absender:</label>
        <input type="text" name="absender" id="absender" value="<?= escapeForOutput($absender); ?>"/>
        <label for="empfaenger">Empfänger:</label>
        <input type="text" name="empfaenger" id="empfaenger" value="<?= escapeForOutput($empfaenger); ?>"/>
        <input type="submit" value="Abschicken"/><br/>
    </form>
</div>

<table class="Liste AdminLog">
    <tr>
        <th>Absender</th>
        <th>Empfaenger</th>
        <th>Wann</th>
        <th>Betreff</th>
        <th>Nachricht</th>
    </tr>
    <?php
    $filter_sender = empty($absender) ? "%" : $absender;
    $filter_receiver = empty($empfaenger) ? "%" : $empfaenger;
    $entriesCount = Database::getInstance()->getAdminMessageLogCount($filter_sender, $filter_receiver);
    $offset = verifyOffset($offset, $entriesCount, admin_log_page_size);
    $entries = Database::getInstance()->getAdminMessageLogEntries($filter_sender, $filter_receiver, $offset, admin_log_page_size);

    for ($i = 0; $i < count($entries); $i++) {
        $row = $entries[$i];
        ?>
        <tr>
            <td><?= createProfileLink($row['senderId'], $row['senderName']); ?></td>
            <td><?php
                if ($row['receiverId'] === null) {
                    echo '<i>Rundmail</i>';
                } else {
                    echo createProfileLink($row['receiverId'], $row['receiverName']);
                }
                ?></td>
            <td><?= formatDateTime(strtotime($row['created'])); ?></td>
            <td><?= escapeForOutput($row['subject']); ?></td>
            <td><?= escapeForOutput($row['message']); ?></td>
        </tr>
        <?php
    }
    if ($entriesCount == 0) {
        echo '<tr><td colspan="8" style="text-align: center;"><i>- Keine Einträge gefunden -</i></td></tr>';
    }
    ?>
</table>
<?= createPaginationTable('/?p=admin_log_nachrichten&amp;absender=' . escapeForOutput($filter_sender) . '&amp;empfaenger=' . escapeForOutput($empfaenger), $offset, $entriesCount, admin_log_page_size); ?>

<div>
    <a href="/?p=admin">&lt;&lt; Zurück</a>
</div>
