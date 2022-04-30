<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Mafia');

$opponent = getOrDefault($_POST, 'opponent');
$action = getOrDefault($_POST, 'action', -1);
$level = getOrDefault($_POST, 'level', -1);

$backLink = sprintf('/?p=mafia&opponent=%s&action=%d&level=%d', urlencode($opponent), $action, $level);
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
if (mafia_base_data[$action][$level]['cost'] > $player['Geld']) {
    redirectTo($backLink, 111, __LINE__);
}

$otherPlayer = Database::getInstance()->getPlayerPointsAndNameAndMoneyAndGruppeAndZaunByName($opponent);
requireEntryFound($otherPlayer, $backLink);
if ($player['Gruppe'] !== null && $otherPlayer['Gruppe'] !== null) {
    $groupDiplomacy = Database::getInstance()->getGroupDiplomacyTypeById($player['Gruppe'], $otherPlayer['Gruppe']);
} else {
    $groupDiplomacy = -1;
}

if (!mafiaRequirementsMet($otherPlayer['Punkte'])) {
    redirectTo($backLink, 155, __LINE__);
}
if ($groupDiplomacy === group_diplomacy_nap || $groupDiplomacy === group_diplomacy_bnd) {
    redirectTo($backLink, 156, __LINE__);
}
if (maybeMafiaOpponents($otherPlayer['Punkte'], $player['Punkte'], $groupDiplomacy)) {
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
if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
        array('Geld' => -mafia_base_data[$action][$level]['cost']),
        array('Geld >= :whr0' => mafia_base_data[$action][$level]['cost'])) !== 1) {
    Database::getInstance()->rollback();
    redirectTo($backLink, 111, __LINE__);
}
if ($success) {
    if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
            array('Punkte' => mafia_base_data[$action]['points'])) !== 1) {
        Database::getInstance()->rollback();
        redirectTo($backLink, 142, __LINE__);
    }
    if (Database::getInstance()->updateTableEntryCalculate('punkte', null,
            array('MafiaPlus' => mafia_base_data[$action]['points']),
            array('useR_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
        Database::getInstance()->rollback();
        redirectTo($backLink, 142, __LINE__);
    }
}

switch ($action) {
    // espionage
    case mafia_action_espionage:
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + mafia_sperrzeit_spionage))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }

        $data = Database::getInstance()->getPlayerEspionageDataByID($otherPlayer['ID']);
        if ($success) {
            $stock = array();
            for ($i = 1; $i <= count_wares; $i++) {
                $stock[] = sprintf('* %s: %s', getItemName($i), formatWeight($data['Lager' . $i]));
            }
            $buildings = array();
            for ($i = 1; $i <= count_buildings; $i++) {
                $buildings[] = sprintf('* %s: %d', getBuildingName($i), $data['Gebaeude' . $i]);
            }

            if (Database::getInstance()->createTableEntry('nachrichten', array(
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
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Spionage gegen ' . $data['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Die Spionage war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unsere Spitzel erkannt bevor diese irgendwelche relevanten Daten sammeln konnten.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('nachrichten', array(
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

        if (Database::getInstance()->createTableEntry('log_mafia', array(
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
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + mafia_sperrzeit_raub))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }

        $amount = 0;
        if ($success) {
            $rate = mt_rand(0, $factor) / $factor;
            $amount = $otherPlayer['Geld'] * $rate;

            if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
                    array('Geld' => $amount)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $otherPlayer['ID'],
                    array('Geld' => -$amount), array('Geld >= :whr0' => $amount)) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Raub gegen ' . $otherPlayer['Name'] . ' erfolgreich',
                    'Nachricht' => sprintf('Der Raub gegen %s war erfolgreich, es konnten %s erbeutet werden.

[i]- Ihre Mafia -[/i]
', createBBProfileLink($otherPlayer['ID'], $otherPlayer['Name']), formatCurrency($amount)))) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
        } else {
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Spionage gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Raub war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unseren Agenten geschnappt bevor dieser das Bargeld einstecken konnte..

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('nachrichten', array(
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

        if (Database::getInstance()->createTableEntry('log_mafia', array(
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
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + mafia_sperrzeit_diebstahl))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }

        $data = Database::getInstance()->getPlayerStock($otherPlayer['ID']);
        if ($success) {
            $valuesSub = array();
            $valuesAdd = array();
            $wheresSub = array('user_id = :whr0' => $otherPlayer['ID']);
            for ($i = 1; $i <= count_wares; $i++) {
                $valuesSub['Lager' . $i] = -$data['Lager' . $i];
                $wheresSub['Lager' . $i . ' >= :whr' . $i] = $data['Lager' . $i];
                $valuesAdd['Lager' . $i] = $data['Lager' . $i];
            }

            if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null, $valuesAdd,
                    array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null, $valuesSub, $wheresSub) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            $wares = array();
            for ($i = 1; $i <= count_wares; $i++) {
                if ($data['Lager' . $i] == 0) continue;
                if (Database::getInstance()->createTableEntry('log_mafia', array(
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

            if (Database::getInstance()->createTableEntry('nachrichten', array(
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
        } else {
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Diebstahl gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Diebstahl war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen entdeckten unsere Transporter und wir mussten fliehen.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $otherPlayer['ID'],
                    'Betreff' => 'Mafia: Diebstahl von ' . $player['Name'] . ' vereitelt',
                    'Nachricht' => 'Wir konnten einen Diebstahlversuch von ' . createBBProfileLink($_SESSION['blm_user'], $player['Name']) . ' vereiteln, der Dieb konnte geschnappt werden.

[i]- Ihre Wachen -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('log_mafia', array(
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
        if (Database::getInstance()->updateTableEntry('mitglieder', $_SESSION['blm_user'],
                array('NextMafia' => date('Y-m-d H:i:s', time() + mafia_sperrzeit_bomben))) !== 1) {
            Database::getInstance()->rollback();
            redirectTo($backLink, 142, __LINE__);
        }

        if ($success) {
            $data = Database::getInstance()->getPlayerPlantageAndBauhofLevel($otherPlayer['ID']);
            $plantage = calculateBuildingDataForPlayer(1, $data, 0);

            if (Database::getInstance()->updateTableEntryCalculate('gebaeude', null,
                    array('Gebaeude1' => -1), array('user_id = :whr0' => $otherPlayer['ID'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $otherPlayer['ID'],
                    array('Punkte' => -$plantage['Punkte'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate('punkte', null,
                    array('MafiaMinus' => $plantage['Punkte']),
                    array('useR_id = :whr0' => $otherPlayer['ID'])) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 142, __LINE__);
            }

            if (Database::getInstance()->createTableEntry('nachrichten', array(
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
            if (Database::getInstance()->createTableEntry('nachrichten', array(
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
            if (Database::getInstance()->createTableEntry('nachrichten', array(
                    'Von' => 0,
                    'An' => $_SESSION['blm_user'],
                    'Betreff' => 'Mafia: Angriff gegen ' . $otherPlayer['Name'] . ' fehlgeschlagen',
                    'Nachricht' => 'Der Angriff war leider [b]nicht[/b] erfolgreich, die gegnerischen Wachen haben unseren Agenten geschnappt und konnten den Brandsatz entschärfen.

[i]- Ihre Mafia -[/i]
')) !== 1) {
                Database::getInstance()->rollback();
                redirectTo($backLink, 141, __LINE__);
            }
            if (Database::getInstance()->createTableEntry('nachrichten', array(
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

        if (Database::getInstance()->createTableEntry('log_mafia', array(
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
