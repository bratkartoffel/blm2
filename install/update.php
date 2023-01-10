<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
$start = microtime(true);
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.class.php');

header('Content-Type: text/plain; charset=UTF-8');
ob_start();
http_response_code(500);

if (getOrDefault($_GET, 'secret') !== upgrade_secret) {
    http_response_code(401);
    die('not allowed');
}

echo "Checking installation for version " . game_version . "\n";
echo "=========================================\n";
echo "Verifying database connection:\n";
$database = Database::getInstance();
echo "> OK\n\n";

echo "Checking base installation:\n";
$executedScripts = array();
if (!$database->tableExists('auftrag')) {
    echo "> Base installation not found, executing setup script\n";
    // initial setup
    $script = 'sql/00-1.10.0-setup.sql';
    $result = $database->executeFile($script);
    if ($result !== null) {
        die("> FAIL: Could not execute setup script, failed step: " . $result);
    }
    $executedScripts[$script] = sha1_file($script);
}
echo "> OK\n\n";

echo "Checking for update information:\n";
if (!$database->tableExists('update_info')) {
    echo "> Update information not found, execute first update script\n";
    // coming from v1.10.0
    $script = 'sql/01-1.10.1-update_info.sql';
    $result = $database->executeFile($script);
    if ($result !== null) {
        die("> FAIL: Could not execute setup script, failed step: " . $result);
    }
    $executedScripts[$script] = sha1_file($script);
}
echo "> OK\n\n";

echo "Enumerating update scripts:\n";
$scripts = glob('sql/*.sql');
sort($scripts);
if (file_exists('/tmp/99_testdata.sql')) {
    $scripts[] = '/tmp/99_testdata.sql';
}
echo "> Found " . count($scripts) . " scripts\n";
foreach ($scripts as $script) {
    if (strpos($script, '/0') !== false) {
        echo "> Skipping $script\n";
        continue;
    }

    echo "> Verify update script: $script\n";
    $dbChecksum = $database->getInstallScriptChecksum($script);
    if ($dbChecksum === null) {
        echo ">> Script unknown, begin execution\n";
        $result = $database->executeFile($script);
        if ($result !== null) {
            die(">> FAIL: Could not execute setup script, failed step: " . $result);
        }
        echo ">> OK\n";
        $executedScripts[$script] = sha1_file($script);
    } else {
        echo ">> Script already executed, verifying checksum\n";
        $fsChecksum = sha1_file($script);
        if ($dbChecksum !== $fsChecksum) {
            die(sprintf(">> FAIL: Calculated checksum for '%s' is different between database (%s) and filesystem (%s). Please correct manually!",
                $script, $dbChecksum, $fsChecksum));
        } else {
            echo ">> OK\n";
        }
    }
}
echo "> OK\n\n";

echo "Saving update information:\n";
$database->begin();
foreach ($executedScripts as $script => $checksum) {
    if ($database->createTableEntry(Database::TABLE_UPDATE_INFO, array(
            'Script' => $script,
            'Checksum' => $checksum
        )) !== 1) {
        $database->rollBack();
        die('> FAIL: Could not create update_info entry for ' . $script);
    }
}
$database->commit();
echo "> OK\n\n";

$dauer = 1000 * (microtime(true) - $start);
http_response_code(200);
echo "Update finished successfully!\n";
echo "> Execution took " . number_format($dauer, 2) . " ms\n";
echo "> " . Database::getInstance()->getQueryCount() . " queries were executed\n";