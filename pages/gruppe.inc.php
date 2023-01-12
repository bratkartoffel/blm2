<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

restrictSitter('Gruppe');

$name = getOrDefault($_GET, 'name');
$tag = getOrDefault($_GET, 'tag');
$id = getOrDefault($_GET, 'id', 0);
$offset = getOrDefault($_GET, 'offset', 0);
$player = Database::getInstance()->getPlayerNameAndPointsAndGruppeAndPlantageLevelById($_SESSION['blm_user']);
$rights = array();
?>
    <div id="SeitenUeberschrift">
        <img src="/pics/big/Community_Help.webp" alt=""/>
        <span>Gruppe<?= createHelpLink(1, 23); ?></span>
    </div>

<?= getMessageBox(getOrDefault($_GET, 'm', 0)); ?>

<?php
// no id parameter given / the players group
if ($id == 0 || $id == $player['GruppeID']) {
    // player has no group
    if ($player['GruppeID'] === null) {
        ?>
        <p>
            Hier können Sie einer bereits bestehenden Gruppe beitreten, oder eine neue gründen.
            Zum Beitreten benötigen Sie die Plantage auf mindestens
            Stufe <?= Config::getInt(Config::SECTION_GROUP, 'plantage_level_join_group'); ?>,
            zum Gründen müssen Sie Ihre Plantage mindestens auf Stufe 8 haben.
        </p>
        <div class="form GroupCreate">
            <form action="/actions/gruppe.php" method="post">
                <input type="hidden" name="a" value="1"/>
                <header>Neue Gruppe gründen</header>
                <div>
                    <label for="create_name">Name:</label>
                    <input type="text" name="name" id="create_name" value="<?= escapeForOutput($name); ?>"
                           maxlength="<?= Config::getInt(Config::SECTION_GROUP, 'max_name_length'); ?>"/>
                </div>
                <div>
                    <label for="create_tag">Kürzel:</label>
                    <input type="text" name="tag" id="create_tag" value="<?= escapeForOutput($tag); ?>"
                           maxlength="<?= Config::getInt(Config::SECTION_GROUP, 'max_tag_length'); ?>"/>
                </div>
                <div>
                    <label for="create_pwd">Passwort:</label>
                    <input type="password" name="pwd" id="create_pwd"
                           minlength="<?= Config::getInt(Config::SECTION_BASE, 'password_min_len'); ?>"/>
                </div>
                <div>
                    <?php
                    if ($player['Gebaeude' . building_plantage] >= Config::getInt(Config::SECTION_GROUP, 'plantage_level_create_group')) {
                        ?>
                        <input type="submit" value="Gründen" id="create_group"/>
                        <?php
                    } else {
                        ?>
                        <p>Ihre Stufe ihrer Plantage ist zu niedrig,<br/>um eine Gruppe gründen zu können.</p>
                        <?php
                    }
                    ?>
                </div>
            </form>
        </div>
        <div class="form GroupJoin">
            <form action="/actions/gruppe.php" method="post">
                <input type="hidden" name="a" value="2"/>
                <header>Gruppe beitreten</header>
                <div>
                    <label for="join_name">Name:</label>
                    <input type="text" name="name" id="join_name" value="<?= escapeForOutput($name); ?>"
                           maxlength="<?= Config::getInt(Config::SECTION_GROUP, 'max_name_length'); ?>"/>
                </div>
                <div>
                    <label for="join_pwd">Passwort:</label>
                    <input type="password" name="pwd" id="join_pwd"/>
                </div>
                <div>
                    <?php
                    if ($player['Gebaeude' . building_plantage] >= Config::getInt(Config::SECTION_GROUP, 'plantage_level_join_group')) {
                        ?>
                        <input type="submit" value="Beitreten" id="join_group"/>
                        <?php
                    } else {
                        ?>
                        <p>Ihre Stufe ihrer Plantage ist zu niedrig,<br/>um einer Gruppe beitreten zu können.</p>
                        <?php
                    }
                    ?>
                </div>
            </form>
        </div>
        <?php
    } // player has no group
    else {
        // player has a group, show links for group interactions
        $id = $player['GruppeID'];
        $rights = Database::getInstance()->getPlayerNameAndGroupIdAndGroupRightsById($_SESSION['blm_user']);
        echo createGroupNaviation(0, $id);
    } // player has a group
} // no id parameter given

if ($id != 0) {
    $group = Database::getInstance()->getGroupInformationById($id);
    requireEntryFound($group, '/?p=gruppe');
    $members = Database::getInstance()->getGroupMembersById($id);
    $diplomacy_db = Database::getInstance()->getAllGroupDiplomacyById($id);
    $diplomacy = array(group_diplomacy_bnd => array(), group_diplomacy_nap => array(), group_diplomacy_war => array());
    foreach ($diplomacy_db as $entry) {
        if ($entry['Aktiv'] == 0) continue;
        $diplomacy[intval($entry['Typ'])][] = $entry;
    }
    ?>
    <div class="form Gruppe">
        <header>Gruppe: <?= escapeForOutput($group['Name']); ?></header>
        <div class="left">
            <div class="GroupImage"><img id="group_image"
                                         src="/pics/profile.php?gid=<?= $id; ?>&amp;ts=<?= ($group['LastImageChange'] == null ? 0 : strtotime($group['LastImageChange'])); ?>"
                                         alt="Gruppenbild"/></div>
            <div class="GroupDescription"
                 id="gruppe_beschreibung"><?= replaceBBCode(empty($group['Beschreibung']) ? '[i]Keine Beschreibung verfügbar[/i]' : $group['Beschreibung']); ?></div>
        </div>
        <div class="right">
            <div><b>Kürzel</b>: <?= escapeForOutput($group['Kuerzel']); ?></div>
            <div><b>Erstellt</b>: <?= formatDate(strtotime($group['Erstellt'])); ?></div>
            <div><b>∑ Punkte</b>: <?= formatPoints($group['Punkte']); ?></div>
            <div><b>∅ Punkte</b>: <?= formatPoints($group['Punkte'] / count($members)); ?></div>

            <h4>Mitglieder (<?= count($members); ?> / <?= Config::getInt(Config::SECTION_GROUP, 'max_members'); ?>
                ):</h4>
            <ul>
                <?php
                foreach ($members as $member) {
                    if ($id == $player['GruppeID']) {
                        $online = strtotime($member['LastAction']) + 1800 >= time();
                        echo sprintf('<li><img src="%s" alt="%s" title="%s"/> %s (%s)</li>',
                            $online ? '/pics/style/online.webp' : '/pics/style/offline.webp',
                            $online ? 'Online' : 'Offline',
                            $online ? 'Online' : 'Offline',
                            createProfileLink($member['ID'], $member['Name']),
                            formatPoints($member['Punkte']));
                    } else {
                        echo sprintf('<li>%s (%s)</li>',
                            createProfileLink($member['ID'], $member['Name']),
                            formatPoints($member['Punkte']));
                    }
                }
                ?>
            </ul>

            <h4>Bündnisse:</h4>
            <ul>
                <?php
                if (count($diplomacy[group_diplomacy_bnd]) > 0) {
                    foreach ($diplomacy[group_diplomacy_bnd] as $entry) {
                        echo sprintf('<li>%s</li>', createGroupLink($entry['GruppeID'], $entry['GruppeName']));
                    }
                } else {
                    echo '<li>Keine Einträge</li>';
                }
                ?>
            </ul>

            <h4>Nichtangriffspakte:</h4>
            <ul>
                <?php
                if (count($diplomacy[group_diplomacy_nap]) > 0) {
                    foreach ($diplomacy[group_diplomacy_nap] as $entry) {
                        echo sprintf('<li>%s</li>', createGroupLink($entry['GruppeID'], $entry['GruppeName']));
                    }
                } else {
                    echo '<li>Keine Einträge</li>';
                }
                ?>
            </ul>

            <h4>Kriege:</h4>
            <ul>
                <?php
                if (count($diplomacy[group_diplomacy_war]) > 0) {
                    foreach ($diplomacy[group_diplomacy_war] as $entry) {
                        echo sprintf('<li>%s</li>', createGroupLink($entry['GruppeID'], $entry['GruppeName']));
                    }
                } else {
                    echo '<li>Keine Einträge</li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <?php

    if ($id == $player['GruppeID']) {
        $messageCount = Database::getInstance()->getGroupMessageCount($id);
        $offset = verifyOffset($offset, $messageCount, Config::getInt(Config::SECTION_BASE, 'group_page_size'));
        if (array_key_exists('message_write', $rights) && $rights['message_write'] == 1) {
            ?>
            <div class="form GroupMessage">
                <form action="/actions/gruppe.php" method="post">
                    <input type="hidden" name="a" value="4"/>
                    <header><label for="message">Nachricht schreiben</label></header>
                    <textarea cols="80" rows="15" id="message"
                              name="message"><?= escapeForOutput(getOrDefault($_GET, 'message')); ?></textarea>
                    <div>
                        <input type="submit" value="Absenden"/>
                    </div>
                </form>
            </div>
            <?php
        }

        Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'], array('GruppeLastMessageZeit' => date('Y-m-d H:i:s')));

        $entries = Database::getInstance()->getGroupMessageEntries($id, $offset, Config::getInt(Config::SECTION_BASE, 'group_page_size'));
        foreach ($entries as $row) {
            ?>
            <div class="form GroupMessage MessagePin<?= $row['Festgepinnt']; ?>">
                <header><?= sprintf('Absender: %s / %s', createProfileLink($row['VonID'], $row['VonName']), formatDateTime(strtotime($row['Zeit']))); ?></header>
                <div><?= replaceBBCode($row['Nachricht']); ?></div>
                <?php
                $links = array();
                if (array_key_exists('message_pin', $rights) && $rights['message_pin'] == 1) {
                    if ($row['Festgepinnt'] == 0) {
                        $links[] = sprintf('<span><a href="/actions/gruppe.php?a=5&amp;id=%d">Festpinnen</a></span>', $row['ID']);
                    } else {
                        $links[] = sprintf('<span><a href="/actions/gruppe.php?a=6&amp;id=%d">Lösen</a></span>', $row['ID']);
                    }
                }
                if (array_key_exists('message_delete', $rights) && $rights['message_delete'] == 1) {
                    $links[] = sprintf('<span><a href="/actions/gruppe.php?a=7&amp;id=%d">Löschen</a></span>', $row['ID']);
                }

                if (count($links) > 0) {
                    echo sprintf("<div>%s</div>", implode("\n", $links));
                }
                ?>
            </div>
            <?php
        }
        echo createPaginationTable('/?p=gruppe', $offset, $messageCount, Config::getInt(Config::SECTION_BASE, 'group_page_size'));
    }
}
