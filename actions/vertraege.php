<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Vertraege');

$vid = getOrDefault($_REQUEST, 'vid', 0);

switch (getOrDefault($_REQUEST, 'a', 0)) {
    // new contract
    case 1:
        $ware = getOrDefault($_POST, 'ware', 0);
        $menge = getOrDefault($_POST, 'menge', 0);
        $preis = getOrDefault($_POST, 'preis', .0);
        $empfaenger = getOrDefault($_POST, 'empfaenger');
        $empfaengerId = Database::getInstance()->getPlayerIDByName($empfaenger);
        $backLink = sprintf('/?p=vertraege_neu&ware=%d&menge=%d&preis=%f&empfaenger=%s', $ware, $menge, $preis, urlencode($empfaenger));
        requireEntryFound($empfaengerId, $backLink, 118, __LINE__);

        if ($empfaengerId == $_SESSION['blm_user']) {
            redirectTo($backLink, 173, __LINE__);
        }

        if ($ware < 1 || $ware > count_wares) {
            redirectTo($backLink, 117, __LINE__);
        }

        $data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);

        $minPrice = calculateSellPrice($ware, $data['Forschung' . $ware], $data['Gebaeude3'], $data['Gebaeude6']);
        if ($preis < round($minPrice * contract_min_sell_price, 2) || $preis > round($minPrice * contract_max_sell_price, 2)) {
            redirectTo($backLink, 153, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Lager' . $ware => -$menge),
                array('Lager' . $ware . ' >= :whr0' => $menge)) != 1) {
            Database::getInstance()->rollBack();
            redirectTo($backLink, 142, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_CONTRACTS, array(
                'Von' => $_SESSION['blm_user'],
                'An' => $empfaengerId,
                'Was' => $ware,
                'Menge' => $menge,
                'Preis' => $preis
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo($backLink, 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=vertraege_liste', 214);
        break;

    // accept contract
    case 2:
        $data = Database::getInstance()->getContractByIdAndAn($vid, $_SESSION['blm_user']);
        requireEntryFound($data, '/?p=vertraege_liste');

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Geld' => -$data['Menge'] * $data['Preis'], 'Lager' . $data['Was'] => $data['Menge']),
                array('Geld >= :whr0' => $data['Menge'] * $data['Preis'])) == 0) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 111, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array('AusgabenVertraege' => $data['Menge'] * $data['Preis']),
                array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_CONTRACTS, $vid) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 143, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $_SESSION['blm_user'],
                'Betreff' => 'Vertrag ' . $vid . ' angenommen',
                'Nachricht' => 'Sie haben den Vertrag mit der Nummer ' . $vid . ' angenommen, die Waren befinden sich bereits in Ihrem Lager.'
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 141, __LINE__);
        }
        if ($data['Von'] > 0 && Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $data['Von'],
                'Betreff' => 'Vertrag ' . $vid . ' angenommen',
                'Nachricht' => 'Der Vertrag mit der Nummer ' . $vid . ' wurde von ' . createBBProfileLink($_SESSION['blm_user'], $data['AnName']) . ' angenommen, das Geld ist bereits bei Ihnen eingetroffen.'
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_CONTRACTS, array(
                'senderId' => $data['Von'],
                'senderName' => Database::getInstance()->getPlayerNameById($data['Von']),
                'receiverId' => $_SESSION['blm_user'],
                'receiverName' => $data['AnName'],
                'item' => $data['Was'],
                'amount' => $data['Menge'],
                'price' => $data['Preis'],
                'accepted' => 1
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=vertraege_liste', 215);
        break;

    // reject contract
    case 3:
        $data = Database::getInstance()->getContractByIDAndAnOrVonEquals($vid, $_SESSION['blm_user']);
        requireEntryFound($data, '/?p=vertraege_liste');

        $myId = ($data['An'] === $_SESSION['blm_user'] ? $data['An'] : $data['Von']);
        $myName = ($data['An'] === $_SESSION['blm_user'] ? Database::getInstance()->getPlayerNameById($data['An']) : Database::getInstance()->getPlayerNameById($data['Von']));
        $hisId = ($data['An'] === $_SESSION['blm_user'] ? $data['Von'] : $data['An']);
        $hisName = ($data['An'] === $_SESSION['blm_user'] ? Database::getInstance()->getPlayerNameById($data['Von']) : Database::getInstance()->getPlayerNameById($data['An']));

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $data['Von'],
                array('Lager' . $data['Was'] => $data['Menge'])) == 0) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_CONTRACTS, $vid) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 143, __LINE__);
        }
        // retract contract
        if ($data['Von'] == $_SESSION['blm_user']) {
            if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $myId,
                    'Betreff' => 'Vertrag ' . $vid . ' wurde zurückgezogen',
                    'Nachricht' => 'Sie haben den Vertrag mit der Nummer ' . $vid . ' zurückgezogen, die Waren befinden sich wieder in Ihrem Lager.'
                )) != 1) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=vertraege_liste', 141, __LINE__);
            }
        } else {
            // reject contract
            if ($data['Von'] > 0 && Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                    'Von' => 0,
                    'An' => $hisId,
                    'Betreff' => 'Vertrag ' . $vid . ' wurde abgelehnt',
                    'Nachricht' => 'Der Vertrag mit der Nummer ' . $vid . ' wurde von ' . createBBProfileLink($myId, $myName) . ' abgelehnt, die Waren wurden wieder in Ihr Lager gebracht.'
                )) != 1) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=vertraege_liste', 141, __LINE__);
            }
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_CONTRACTS, array(
                'senderId' => $data['Von'],
                'senderName' => Database::getInstance()->getPlayerNameById($data['Von']),
                'receiverId' => $data['An'],
                'receiverName' => Database::getInstance()->getPlayerNameById($data['An']),
                'item' => $data['Was'],
                'amount' => $data['Menge'],
                'price' => $data['Preis'],
                'accepted' => 0
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 141, __LINE__);
        }

        Database::getInstance()->commit();
        redirectTo('/?p=vertraege_liste', 216);
        break;

    default:
        redirectTo('/?p=vertraege_liste', 112, __LINE__);
        break;
}
