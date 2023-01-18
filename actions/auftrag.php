<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

ob_start();
requireLogin();

restrictSitter('NeverAllow');

$id = getOrDefault($_GET, 'id', 0);
$auftrag = Database::getInstance()->getAuftragByIdAndVon($id, $_SESSION['blm_user']);
$back = 'index';

Database::getInstance()->begin();
switch (floor($auftrag['item'] / job_type_factor)) {
    // Gebäude
    case job_type_building:
        $back = 'gebaeude';
        requireXsrfToken('/?p=' . $back);
        requireEntryFound($id, '/?p=' . $back);
        $moneyBack = round($auftrag['cost'] * Config::getFloat(Config::SECTION_BASE, 'cancel_refund'), 2);
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Geld' => $moneyBack)) !== 1) {
            redirectTo('/?p=' . $back, 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array('AusgabenGebaeude' => -$moneyBack),
                array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
            redirectTo('/?p=' . $back, 142, __LINE__);
        }
        break;

    // Produktion
    case job_type_production:
        $back = 'plantage';
        requireXsrfToken('/?p=' . $back);
        requireEntryFound($id, '/?p=' . $back);
        $duration = strtotime($auftrag['finished']) - strtotime($auftrag['created']);
        $completed = time() - strtotime($auftrag['created']);
        $percent = $completed / $duration;
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, null,
                array('Lager' . ($auftrag['item'] % job_type_factor) => floor($auftrag['amount'] * $percent)),
                array('ID = :whr0' => $_SESSION['blm_user'])) === null) {
            redirectTo('/?p=' . $back, 142, __LINE__);
        }
        break;

    // Forschung
    case job_type_research:
        $back = 'forschungszentrum';
        requireXsrfToken('/?p=' . $back);
        requireEntryFound($id, '/?p=' . $back);
        $moneyBack = round($auftrag['cost'] * Config::getFloat(Config::SECTION_BASE, 'cancel_refund'), 2);
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_USERS, $_SESSION['blm_user'],
                array('Geld' => $moneyBack)) !== 1) {
            redirectTo('/?p=' . $back, 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate(Database::TABLE_STATISTICS, null,
                array('AusgabenForschung' => -$moneyBack),
                array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
            redirectTo('/?p=' . $back, 142, __LINE__);
        }
        break;

    // unknown action
    default:
        redirectTo('/?p=' . $back, 112, __LINE__);
        break;
}

if (Database::getInstance()->deleteTableEntry(Database::TABLE_JOBS, $id) === null) {
    redirectTo('/?p=' . $back, 143, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=' . $back, 222, substr($back, 0, 1) . ($auftrag['item'] % job_type_factor));
