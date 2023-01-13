<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Marktplatz');

switch (getOrDefault($_GET, 'a', 0)) {
    // sell wares
    case 1:
        $ware = getOrDefault($_POST, 'ware', 0);
        $amount = getOrDefault($_POST, 'amount', 0);
        $price = getOrDefault($_POST, 'price', .0);

        $data = Database::getInstance()->getPlayerResearchLevelsAndAllStorageAndShopLevelAndSchoolLevel($_SESSION['blm_user']);
        $sellPrice = calculateSellPrice($ware, $data['Forschung' . $ware], $data['Gebaeude' . building_shop], $data['Gebaeude' . building_school]);

        if ($ware < 1 || $ware > count_wares) {
            redirectTo(sprintf('/?p=marktplatz_verkaufen&ware=%d&amount=%d&price=%f&', $ware, $amount, $price), 117, __LINE__);
        }

        if ($amount > $data['Lager' . $ware]) {
            redirectTo(sprintf('/?p=marktplatz_verkaufen&ware=%d&amount=%d&price=%f', $ware, $amount, $price), 116, __LINE__);
        }

        if ($price < round($sellPrice * Config::getFloat(Config::SECTION_MARKET, 'min_price'), 2) || $price > round($sellPrice * Config::getFloat(Config::SECTION_MARKET, 'max_price'), 2)) {
            redirectTo(sprintf('/?p=marktplatz_verkaufen&ware=%d&amount=%d&price=%f', $ware, $amount, $price), 153, __LINE__);
        }

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Lager' . $ware => -$amount),
                array('Lager' . $ware . ' >= :whr0' => $amount)) != 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=marktplatz_verkaufen&ware=%d&amount=%d&price=%f&', $ware, $amount, $price), 116, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_MARKET, array(
                'Von' => $_SESSION['blm_user'],
                'Was' => $ware,
                'Menge' => $amount,
                'Preis' => $price
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo(sprintf('/?p=marktplatz_verkaufen&ware=%d&amount=%d&price=%f&', $ware, $amount, $price), 141, __LINE__);
        }
        Database::getInstance()->commit();
        redirectTo('/?p=marktplatz_liste', 218);
        break;

    // buy offer
    case 2:
        requireXsrfToken('/?p=marktplatz_liste');
        $id = getOrDefault($_GET, 'id', 0);

        $entry = Database::getInstance()->getMarktplatzEntryById($id);
        requireEntryFound($entry, '/?p=marktplatz_liste');

        $amount = $entry['Menge'] * $entry['Preis'];

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Geld' => -$amount),
                array('Geld >= :whr0' => $amount)) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 111, __LINE__);
        }

        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Lager' . $entry['Was'] => $entry['Menge'])) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 142, __LINE__);
        }

        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array('AusgabenMarkt' => $amount), array('user_id = :whr0' => $_SESSION['blm_user'])) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 142, __LINE__);
        }

        if ($entry['Von'] != 0) {
            $reducedAmount = round($amount * (1 - Config::getInt(Config::SECTION_MARKET, 'provision_rate')), 2);
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['Von'],
                    array('Geld' => $reducedAmount)) != 1) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=marktplatz_liste', 142, __LINE__);
            }
            if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                    array('EinnahmenMarkt' => $reducedAmount),
                    array('user_id = :whr0' => $entry['Von'])) != 1) {
                Database::getInstance()->rollBack();
                redirectTo('/?p=marktplatz_liste', 142, __LINE__);
            }
        }

        if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $entry['Von'],
                'Betreff' => 'Angebot auf freiem Markt verkauft',
                'Nachricht' => sprintf("Soeben wurde das Angebot #%d (%s %s zu insgesamt %s) von einem anonymen Käufer gekauft.",
                    $entry['ID'], formatWeight($entry['Menge']), getItemName($entry['Was']), formatCurrency($amount))
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 141, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_MARKET, $entry['ID']) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 143, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MARKET, array(
                'sellerId' => $entry['Von'],
                'sellerName' => Database::getInstance()->getPlayerNameById($entry['Von']),
                'buyerId' => $_SESSION['blm_user'],
                'buyerName' => Database::getInstance()->getPlayerNameById($_SESSION['blm_user']),
                'item' => $entry['Was'],
                'amount' => $entry['Menge'],
                'price' => $entry['Preis']
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 141, __LINE__);
        }
        Database::getInstance()->commit();

        redirectTo('/?p=marktplatz_liste', 217);
        break;

    // retract offer
    case 3:
        requireXsrfToken('/?p=marktplatz_liste');
        $id = getOrDefault($_GET, 'id', 0);

        $entry = Database::getInstance()->getMarktplatzEntryByIdAndVon($id, $_SESSION['blm_user']);
        requireEntryFound($entry, '/?p=marktplatz_liste');

        Database::getInstance()->begin();
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $entry['Von'],
                array('Lager' . $entry['Was'] => floor($entry['Menge'] * Config::getFloat(Config::SECTION_MARKET, 'retract_rate')))) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 142, __LINE__);
        }
        if (Database::getInstance()->deleteTableEntry(Database::TABLE_MARKET, $entry['ID']) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 143, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_MESSAGES, array(
                'Von' => 0,
                'An' => $entry['Von'],
                'Betreff' => 'Angebot vom freien Markt zurückgezogen',
                'Nachricht' => sprintf("Das Angebot #%d wurde vom Markt zurückgezogen. Leider sind auf dem Transport und während der Lagerung dort ein Teil der Waren verdorben.
                Von den ursprünglichen %s konnten %s wieder in ihr Lager übernommen werden.",
                    $entry['ID'], formatWeight($entry['Menge']), formatWeight(floor($entry['Menge'] * Config::getFloat(Config::SECTION_MARKET, 'retract_rate'))))
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=marktplatz_liste', 141, __LINE__);
        }
        if (Database::getInstance()->createTableEntry(Database::TABLE_LOG_MARKET, array(
                'sellerId' => $entry['Von'],
                'sellerName' => Database::getInstance()->getPlayerNameById($entry['Von']),
                'item' => $entry['Was'],
                'amount' => $entry['Menge'],
                'price' => $entry['Preis']
            )) != 1) {
            Database::getInstance()->rollBack();
            redirectTo('/?p=vertraege_liste', 141, __LINE__);
        }
        Database::getInstance()->commit();

        redirectTo('/?p=marktplatz_liste', 217);
        break;

    // unknown action
    default:
        redirectTo('/?p=marktplatz_liste', 112, __LINE__);
        break;
}
