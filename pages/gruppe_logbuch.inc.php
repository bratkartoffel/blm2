<?php
restrictSitter('Gruppe');

$offset = getOrDefault($_GET, 'o', 0);
$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');
?>
<div id="SeitenUeberschrift">
    <img src="/pics/big/Community_Help.png" alt=""/>
    <span>Gruppe - Logbuch<?= createHelpLink(1, 23); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(5, $rights['group_id']); ?>

<div class="form GroupLog">
    <header>Einträge</header>
    <?php
    $messageCount = Database::getInstance()->getGroupLogCount($rights['group_id']);
    $offset = verifyOffset($offset, $messageCount, group_page_size);

    $entries = Database::getInstance()->getGroupLogEntries($rights['group_id'], $offset, group_page_size);
    foreach ($entries as $row) {
        ?>
        <div>
            <span><?= formatDateTime(strtotime($row['Datum'])); ?></span>
            <span><?= replaceBBCode($row['Text']); ?></span>
        </div>
        <?php
    }
    ?>
</div>
<?= createPaginationTable('/?p=gruppe_logbuch', $offset, $messageCount, group_page_size); ?>
