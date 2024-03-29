<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

require_once __DIR__ . '/../include/functions.inc.php';
require_once __DIR__ . '/../include/database.class.php';

$count = getOrDefault($_GET, 'count', 1);
$lastCron = time() - Config::getInt(Config::SECTION_BASE, 'cron_interval') * 60;
$lastPoints = time() - Config::getInt(Config::SECTION_BASE, 'points_interval') * 3600;

$database = Database::getInstance();
$database->begin();

$database->updateTableEntry(Database::TABLE_RUNTIME_CONFIG, null,
        array('conf_value' => $lastCron),
        array('conf_name = :whr0' => 'lastcron'));
$database->updateTableEntry(Database::TABLE_RUNTIME_CONFIG, null,
        array('conf_value' => $lastPoints),
        array('conf_name = :whr0' => 'lastpoints'));
$database->commit();

Config::enhanceFromDb(array(
        array('conf_name' => 'lastcron', 'conf_value' => $lastCron),
        array('conf_name' => 'lastpoints', 'conf_value' => $lastPoints),
));
require_once __DIR__ . '/../cronjobs/cron.php';

for ($i = 1; $i < $count; $i++) {
    runCron();
}
