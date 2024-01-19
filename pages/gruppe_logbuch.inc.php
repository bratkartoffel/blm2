<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Gruppe');

$offset = getOrDefault($_GET, 'o', 0);
$rights = Database::getInstance()->getGroupRightsByUserId($_SESSION['blm_user']);
requireEntryFound($rights, '/?p=gruppe');
?>
<div id="SeitenUeberschrift">
    <img src="./pics/big/Community_Help.webp" alt=""/>
    <span>Gruppe - Logbuch<?= createHelpLink(1, 23); ?></span>
</div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>
<?= createGroupNaviation(5, $rights['group_id']); ?>

<div class="form GroupLog">
    <header>Einträge</header>
    <?php
    $messageCount = Database::getInstance()->getGroupLogCount($rights['group_id']);
    $offset = verifyOffset($offset, $messageCount, Config::getInt(Config::SECTION_BASE, 'group_page_size'));

    $entries = Database::getInstance()->getGroupLogEntries($rights['group_id'], $offset, Config::getInt(Config::SECTION_BASE, 'group_page_size'));
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
<?= createPaginationTable('pages', '/?p=gruppe_logbuch', $offset, $messageCount, Config::getInt(Config::SECTION_BASE, 'group_page_size')); ?>
