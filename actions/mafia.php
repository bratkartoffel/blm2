<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Mafia');

$opponent = getOrDefault($_POST, 'opponent', 0);
$action = getOrDefault($_POST, 'action', -1);
$level = getOrDefault($_POST, 'level', -1);

$backLink = sprintf('/?p=mafia&opponent=%d&action=%d&level=%d', urlencode($opponent), $action, $level);
if ($action < 0 || $action > count(mafia_base_data)) {
    redirectTo($backLink, 112, __LINE__);
}
if ($level < 0 || $level > count(mafia_base_data[0])) {
    redirectTo($backLink, 112, __LINE__);
}

$player = Database::getInstance()->getPlayerPointsAndGruppeAndMoneyAndNextMafiaAndPizzeriaById($_SESSION['blm_user']);
if (!mafiaRequirementsMet($player['Punkte'])) {
    redirectTo($backLink, 112, __LINE__);
}
if (strtotime($player['NextMafia']) > time()) {
    redirectTo($backLink, 170, __LINE__);
}
if (mafia_base_data[$action][$level]['cost'] > $player['Geld']) {
    redirectTo($backLink, 111, __LINE__);
}

$otherPlayer = Database::getInstance()->getPlayerPointsAndNameAndMoneyAndGruppeAndZaunById($opponent);
requireEntryFound($otherPlayer, $backLink);
if ($player['Gruppe'] !== null && $otherPlayer['Gruppe'] !== null) {
    $groupDiplomacy = Database::getInstance()->getGroupDiplomacyTypeById($player['Gruppe'], $otherPlayer['Gruppe']);
} else {
    $groupDiplomacy = -1;
}
if ($player['ID'] == $otherPlayer['ID']) {
    redirectTo($backLink, 171, __LINE__);
}
if ($groupDiplomacy === group_diplomacy_nap || $groupDiplomacy === group_diplomacy_bnd) {
    redirectTo($backLink, 156, __LINE__);
}
if ($player['Gruppe'] !== null && $player['Gruppe'] == $otherPlayer['Gruppe']) {
    redirectTo($backLink, 112, __LINE__);
}
if ($groupDiplomacy !== group_diplomacy_war &&
    (!mafiaRequirementsMet($otherPlayer['Punkte']) || !maybeMafiaOpponents($otherPlayer['Punkte'], $player['Punkte'], $groupDiplomacy))) {
    redirectTo($backLink, 155, __LINE__);
}

$chance = mafia_base_data[$action][$level]['chance'];
$chance += $player['Gebaeude8'] * mafia_bonus_factor_pizzeria;
$chance -= $otherPlayer['Gebaeude7'] * mafia_bonus_factor_fence;
$chance = max(0.0, min($chance, 1.0));
$factor = 10000;

$random = mt_rand(0, $factor) / $factor;
$success = $random <= $chance;

Database::getInstance()->begin();
if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
        array('Geld' => -mafia_base_data[$action][$level]['cost']),
        array('Geld >= :whr0' => mafia_base_data[$action][$level]['cost'])) !== 1) {
    Database::getInstance()->rollback();
    redirectTo($backLink, 111, __LINE__);
}
if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
        array('AusgabenMafia' => mafia_base_data[$action][$level]['cost']),
        array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
    Database::getInstance()->rollback();
    redirectTo($backLink, 111, __LINE__);
}
if ($success) {
    if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
            array('Punkte' => mafia_base_data[$action]['points'], 'MafiaPlus' => mafia_base_data[$action]['points'])) !== 1) {
        Database::getInstance()->rollback();
        redirectTo($backLink, 142, __LINE__);
    }
}

switch ($action) {
    // espionage
    case mafia_action_espionage:
        $sperrZeit = mafia_sperrzeit_spionage;
        if ($groupDiplomacy === group_diplomacy_war) $sperrZeit *= mafia_sperrzeit_factor_war;
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + $sperrZeit))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }
        $data = Database::getInstance()->getPlayerEspionageDataByID($otherPlayer['ID']);
        if ($success) {
            $stock = array();
            for ($i = 1; $i <= count_wares; $i++) {
                if ($data['Lager' . $i] == 0) continue;
                $stock[] = sprintf('* %s: %s', getItemName($i), formatWeight($data['Lager' . $i]));
            }
            $buildings = array();
            for ($i = 1; $i <= count_buildings; $i++) {
                if ($data['Gebaeude' . $i] == 0) continue;
                $buildings[] = sprintf('* %s: %d', getBuildingName($i), $data['Gebaeude' . $i]);
            }

            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Spionage gegen ' . $data['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Die Spionage gegen %s war erfolgreich, hier die von uns in Erfahrung gebrachten Daten:

[b]Bargeld[/b]: %s

[b]Lagerstände[/b]:
%s

[b]Gebäudelevel[/b]:
%s

[i]- Ihre Mafia -[/i]
',
                        createBBProfileLink($data['ID'], $data['Name']),
                        formatCurrency($data['Geld']),
                        implode("\n", $stock),
                        implode("\n", $buildings))
                )) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Spionage gegen ' . $data['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Die Spionage war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unsere Spitzel erkannt bevor diese irgendwelche relevanten Daten sammeln konnten.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Spionageversuch von ' . $player['Name'] . ' vereitelt',
                    'Nachricht' => 'Wir konnten einen Spionageversuch von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' vereitelt, der Spitzel konnte keine Informationen übermitteln.

[i]- Ihre Wachen -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MAFIA, array(
                'senderId' => $_SESSION['blm_user'],
                'senderName' => $player['Name'],
                'receiverId' => $otherPlayer['ID'],
                'receiverName' => $otherPlayer['Name'],
                'action' => 'ESPIONAGE',
                'chance' => $chance,
                'success' => $success ? 1 : 0)) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste');
        break;

    // robbery
    case mafia_action_robbery:
        $sperrZeit = mafia_sperrzeit_raub;
        if ($groupDiplomacy === group_diplomacy_war) $sperrZeit *= mafia_sperrzeit_factor_war;
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + $sperrZeit))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }
        $amount = null;
        if ($success) {
            $rate = mt_rand(0, $factor) / $factor;
            $amount = $otherPlayer['Geld'] * $rate;

            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                    array('Geld' => $amount)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $otherPlayer['ID'],
                    array('Geld' => -$amount), array('Geld >= :whr0' => $amount)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Raub gegen ' . $otherPlayer['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Der Raub gegen %s war erfolgreich, es konnten %s erbeutet werden.

[i]- Ihre Mafia -[/i]
', createBBProfileLink($otherPlayer['ID'], $otherPlayer['Name']), formatCurrency($amount)))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Raub von ' . $player['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Wir wurden von %s ausgeraubt, Ihnen wurden %s gestohlen.

[i]- Ihre Wachen -[/i]
', createBBProfileLink($_SESSION['blm_user'], $player['Name']), formatCurrency($amount)))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Raub gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Raub war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unseren Agenten geschnappt bevor dieser das Bargeld einstecken konnte..

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Raub von ' . $player['Name'] . ' vereitelt',
                    'Nachricht' => 'Wir konnten einen Raubversuch von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' vereiteln, der Dieb konnte geschnappt werden.

[i]- Ihre Wachen -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MAFIA, array(
                'senderId' => $_SESSION['blm_user'],
                'senderName' => $player['Name'],
                'receiverId' => $otherPlayer['ID'],
                'receiverName' => $otherPlayer['Name'],
                'action' => 'ROBBERY',
                'amount' => $amount,
                'chance' => $chance,
                'success' => $success ? 1 : 0)) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste');
        break;

    // heist
    case mafia_action_heist:
        $sperrZeit = mafia_sperrzeit_diebstahl;
        if ($groupDiplomacy === group_diplomacy_war) $sperrZeit *= mafia_sperrzeit_factor_war;
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + $sperrZeit))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }
        $data = Database::getInstance()->getPlayerStock($otherPlayer['ID']);
        if ($success) {
            $valuesSub = array();
            $valuesAdd = array();
            $wheresSub = array();
            for ($i = 1; $i <= count_wares; $i++) {
                $valuesSub['Lager' . $i] = -$data['Lager' . $i];
                $wheresSub['Lager' . $i . ' >= :whr' . ($i - 1)] = $data['Lager' . $i];
                $valuesAdd['Lager' . $i] = $data['Lager' . $i];
            }

            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'], $valuesAdd) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $otherPlayer['ID'], $valuesSub, $wheresSub) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            $wares = array();
            for ($i = 1; $i <= count_wares; $i++) {
                if ($data['Lager' . $i] == 0) continue;
                if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MAFIA, array(
                        'senderId' => $_SESSION['blm_user'],
                        'senderName' => $player['Name'],
                        'receiverId' => $otherPlayer['ID'],
                        'receiverName' => $otherPlayer['Name'],
                        'action' => 'HEIST',
                        'item' => $i,
                        'amount' => $data['Lager' . $i],
                        'chance' => $chance,
                        'success' => 1)) !== 1) {
                    Database::getInstance()->rollback();
                    redirectTo($backLink, 141, __LINE__);
                }

                $wares[] = sprintf("* %s: %s", getItemName($i), formatWeight($data['Lager' . $i]));
            }

            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Diebstahl gegen ' . $otherPlayer['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Der Diebstahl verlief ohne Probleme, wir konnten das gesamte Lager ausräumen. Folgende Waren konnten sichergestellt werden:

%s 

[i]- Ihre Mafia -[/i]
', implode("\n", $wares)))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Diebstahl von Unbekannt erfolgreich',
                    'Nachricht' => sprintf('Wir wurden von einem unbekannten Angreifer ausgeraubt, nach der Inventur fehlten folgende Waren:
                    
%s

[i]- Ihre Wachen -[/i]
', implode("\n", $wares)))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Diebstahl gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Diebstahl war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen entdeckten unsere Transporter und wir mussten fliehen.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Diebstahl von ' . $player['Name'] . ' vereitelt',
                    'Nachricht' => 'Wir konnten einen Diebstahlversuch von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' vereiteln, der Dieb konnte geschnappt werden.

[i]- Ihre Wachen -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MAFIA, array(
                    'senderId' => $_SESSION['blm_user'],
                    'senderName' => $player['Name'],
                    'receiverId' => $otherPlayer['ID'],
                    'receiverName' => $otherPlayer['Name'],
                    'action' => 'HEIST',
                    'chance' => $chance,
                    'success' => 0)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        }
        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste');
        break;

    // attack
    case mafia_action_attack:
        $sperrZeit = mafia_sperrzeit_bomben;
        if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + $sperrZeit))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }
        if ($success) {
            $data = Database::getInstance()->getPlayerPlantageAndBauhofLevel($otherPlayer['ID']);
            $plantage = calculateBuildingDataForPlayer(1, $data, 0);

            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $otherPlayer['ID'],
                    array('Gebaeude1' => -1, 'Punkte' => -$plantage['Punkte'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                    array('MafiaMinus' => $plantage['Punkte']),
                    array('user_id = :whr0' => $otherPlayer['ID'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Angriff gegen ' . $otherPlayer['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Der Angriff war erfolgreich, die Plantage brennt lichterloh.
Ihr Konkurrent hat dadurch %s Punkte verloren.

[i]- Ihre Mafia -[/i]
', formatPoints($plantage['Punkte'])))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Angriff von ' . $player['Name'] . ' konnte nicht abgewandt werden',
                    'Nachricht' => sprintf('Lieder konnten wir einen Angriff auf unsere Plantage nicht verhindern.
Wir konnten aber durch die Analyse der Trümmer den Verursacher dieses Anschlags ausmachen, %s.
Durch den Angriff haben wir leider zudem %s Punkte verloren.

[i]- Ihre Wachen -[/i]
', createBBProfileLink($_SESSION['blm_user'], $player['Name']), formatPoints($plantage['Punkte'])))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Angriff gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Angriff war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unseren Agenten geschnappt und konnten den Brandsatz entschärfen.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Angriff von ' . $player['Name'] . ' vereitelt',
                    'Nachricht' => 'Wir konnten einen Angriff von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' vereiteln, der Brandsatz konnte entschärft werden bevor Schaden entstanden ist.

[i]- Ihre Wachen -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MAFIA, array(
                'senderId' => $_SESSION['blm_user'],
                'senderName' => $player['Name'],
                'receiverId' => $otherPlayer['ID'],
                'receiverName' => $otherPlayer['Name'],
                'action' => 'ATTACK',
                'chance' => $chance,
                'success' => $success ? 1 : 0)) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=nachrichten_liste');
        break;

    default:
        Database::getInstance()->rollback();
        redirectTo($backLink, 112, __LINE__);
        break;
}
