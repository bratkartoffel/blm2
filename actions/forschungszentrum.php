<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

ob_start();
requireLogin();
restrictSitter('Forschung');

$was = getOrDefault($_POST, 'was', 0);

if ($was <= 0 || $was > count_wares) {
    redirectTo('/?p=forschungszentrum', 112, __LINE__);
}

$data = Database::getInstance()->getPlayerMoneyAndResearchLevelsAndPlantageLevelAndResearchLabLevel($_SESSION['blm_user']);
requireEntryFound($data, '/?p=forschungszentrum', 112, __LINE__);

$researchData = calculateResearchDataForPlayer($was, $data['Gebaeude2'], $data['Forschung' . $was]);

if (!researchRequirementsMet($was, $data['Gebaeude1'], $data['Gebaeude2'])) {
    redirectTo('/?p=forschungszentrum', 112, __LINE__);
}

if ($data['Geld'] < $researchData['Kosten']) {
    redirectTo('/?p=forschungszentrum', 111, __LINE__);
}

Database::getInstance()->begin();
if (Database::getInstance()->createTableEntry('auftrag', array(
        'finished' => date("Y-m-d H:i:s", time() + $researchData['Dauer']),
        'user_id' => $_SESSION['blm_user'],
        'item' => 300 + $was,
        'cost' => $researchData['Kosten'],
        'points' => $researchData['Punkte']
    )) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=forschungszentrum', 141, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate('mitglieder', $_SESSION['blm_user'],
        array('Geld' => -$researchData['Kosten']),
        array('Geld >= :whr0' => $researchData['Kosten'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=forschungszentrum', 142, __LINE__);
}

if (Database::getInstance()->updateTableEntryCalculate('statistik', null,
        array('AusgabenForschung' => $researchData['Kosten']),
        array('user_id = :whr0' => $_SESSION['blm_user'])) == 0) {
    Database::getInstance()->rollBack();
    redirectTo('/?p=forschungszentrum', 142, __LINE__);
}

Database::getInstance()->commit();
redirectTo('/?p=forschungszentrum', 207, "f" . $was);
