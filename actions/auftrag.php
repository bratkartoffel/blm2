<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();

$back = getOrDefault($_GET, 'back', 'index');
restrictSitter('NeverAllow', $back);

$id = getOrDefault($_GET, 'id', 0);
$auftrag = Database::getInstance()->getAuftragByIdAndVon($id, $_SESSION['blm_user']);
requireEntryFound($id, '/?p=' . urlencode($back));

Database::getInstance()->begin();
switch (floor($auftrag['item'] / 100)) {
    // Gebäude
    case 1:
        $moneyBack = round($auftrag['cost'] * action_retrace_rate, 2);
        if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
                array('Geld' => $moneyBack)) !== 1) {
            redirectTo('/?p=' . urlencode($back), 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate('statistik', null,
                array('AusgabenGebaeude' => -$moneyBack),
                array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
            redirectTo('/?p=' . urlencode($back), 142, __LINE__);
        }
        break;

    // Produktion
    case 2:
        $duration = strtotime($auftrag['finished']) - strtotime($auftrag['created']);
        $completed = time() - strtotime($auftrag['created']);
        $percent = $completed / $duration;
        if (Database::getInstance()->updateTableEntryCalculate('lagerhaus', null,
                array('Lager' . ($auftrag['item'] % 100) => floor($auftrag['amount'] * $percent)),
                array('user_id = :whr0' => $_SESSION['blm_user'])) === null) {
            redirectTo('/?p=' . urlencode($back), 142, __LINE__);
        }
        break;

    // Forschung
    case 3:
        $moneyBack = round($auftrag['cost'] * action_retrace_rate, 2);
        if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
                array('Geld' => $moneyBack)) !== 1) {
            redirectTo('/?p=' . urlencode($back), 142, __LINE__);
        }
        if (Database::getInstance()->updateTableEntryCalculate('statistik', null,
                array('AusgabenForschung' => -$moneyBack),
                array('user_id = :whr0' => $_SESSION['blm_user'])) !== 1) {
            redirectTo('/?p=' . urlencode($back), 142, __LINE__);
        }
        break;

    // unknown action
    default:
        redirectTo('/?p=' . urlencode($back), 112, __LINE__);
        break;
}

if (Database::getInstance()->deleteTableEntry('auftrag', $id) === null) {
    redirectTo('/?p=' . urlencode($back), 143, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=' . urlencode($back), 222, substr($back, 0, 1) . $auftrag['Was']);
