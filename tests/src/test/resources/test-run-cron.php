<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

require_once __DIR__ . '/../include/database.class.php';

$database = Database::getInstance();
$database->begin();
$database->updateTableEntry(Database::TABLE_RUNTIME_CONFIG, null,
    array('conf_value' => time() - Config::getInt(Config::SECTION_BASE, 'cron_interval')),
    array('conf_name = :whr0' => 'lastcron'));
$database->updateTableEntry(Database::TABLE_RUNTIME_CONFIG, null,
    array('conf_value' => time() - Config::getInt(Config::SECTION_BASE, 'points_interval')),
    array('conf_name = :whr0' => 'lastpoints'));
$database->commit();

include __DIR__ . '/../cronjobs/cron.php';
