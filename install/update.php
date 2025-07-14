<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
$start = microtime(true);
header('Content-Type: text/plain; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 'true');
ob_start();
chdir(__DIR__);
http_response_code(500);

const status_ok = 1;
const status_needs_upgrade = 2;
const status_fail = 3;

function status_text(int $status): string
{
    switch ($status) {
        case status_ok:
            return 'OK';
        case status_needs_upgrade:
            return 'NEEDS UPGRADE';
        case status_fail:
            return 'FAIL';
        default:
            return 'UNKNOWN';
    }
}

function print_status(string $step, int $status, ?string $extraInfo = null): void
{
    if ($extraInfo !== null) {
        printf("[%s]: %s (%s)\n", status_text($status), $step, $extraInfo);
    } else {
        printf("[%s]: %s\n", status_text($status), $step);
    }
    if ($status == status_fail) {
        die();
    }
}


require_once '../include/game_version.inc.php';
$step = sprintf('Checking installation for version %s (running with PHP %s):', game_version, phpversion());
{
    if (!file_exists('../config/config.ini')) {
        print_status($step, status_fail, 'config/config.ini not found');
    }
    if (!file_exists('../config/config-defaults.ini')) {
        print_status($step, status_fail, 'config/config-defaults.ini not found');
    }
    print_status($step, status_ok);
}

require_once '../include/functions.inc.php';
require_once '../include/database.class.php';

$step = 'Verifying upgrade credentials';
{
    if (Config::get(Config::SECTION_BASE, 'upgrade_secret') === '!!replace this!!') {
        http_response_code(401);
        print_status($step, status_fail, 'the upgrade_secret is not configured');
    }
}
{
    if (php_sapi_name() !== 'cli' && getOrDefault($_GET, 'secret', 'unset') !== Config::get(Config::SECTION_BASE, 'upgrade_secret')) {
        http_response_code(401);
        print_status($step, status_fail, 'invalid credentials');
    }
    print_status($step, status_ok, sprintf('Called by %s', php_sapi_name()));
}

$step = 'Verifying secrets changed';
{
    if (Config::get(Config::SECTION_BASE, 'random_secret') === '!!replace this!!') {
        http_response_code(401);
        print_status($step, status_fail, 'the random_secret is not configured');
    }
    if (Config::get(Config::SECTION_BASE, 'upgrade_secret') == Config::get(Config::SECTION_BASE, 'random_secret')) {
        http_response_code(401);
        print_status($step, status_fail, "'upgrade_secret' and 'random_secret' may not be equal");
    }
    print_status($step, status_ok);
}

$step = 'Verifying database connection';
$database = null;
{
    try {
        $database = Database::getInstanceForInstallCheck();
    } catch (PDOException $e) {
        print_status($step, status_fail, $e->getMessage());
    }
    print_status($step, status_ok);
}

$step = 'Verifying PHP modules installed';
{
    if (!class_exists('ZipArchive')) {
        print_status($step, status_fail, 'zip not found');
    }
    if (!function_exists('gd_info')) {
        print_status($step, status_fail, 'gd not found');
    }
    if (defined('PASSWORD_ARGON2_PROVIDER') && PASSWORD_ARGON2_PROVIDER === 'sodium'
            && Config::get(Config::SECTION_BASE, 'password_hash_options')['threads'] !== 1) {
        print_status($step, status_fail, "argon2 provider (libsodium) doesn't support more than 1 thread");
    }
    print_status($step, status_ok);
}

$step = 'Checking base installation';
$executedScripts = array();
{
    if (!$database->tableExists('auftrag')) {
        print_status($step, status_needs_upgrade);

        $step = 'Executing basic setup script';
        {
            // initial setup
            $script = 'sql/00-1.10.0-setup.sql';
            $result = $database->executeFile($script);
            if ($result !== null) {
                print_status($step, status_fail, sprintf('Could not execute setup script, failed step: %s', $result));
            }
            $executedScripts[$script] = sha1_file($script);
        }
    }
    print_status($step, status_ok);
}

$step = 'Checking for update information';
{
    if (!$database->tableExists('update_info')) {
        print_status($step, status_needs_upgrade);

        $step = 'Creating initial update information';
        {  // coming from v1.10.0
            $script = 'sql/01-1.10.1-update_info.sql';
            $result = $database->executeFile($script);
            if ($result !== null) {
                print_status($step, status_fail, sprintf('Could not execute setup script, failed step: %s', $result));
            }
            $executedScripts[$script] = sha1_file($script);
        }
    }
    print_status($step, status_ok);
}

$step = 'Enumerating update scripts';
$scripts = array();
{
    $scripts = glob('sql/*.sql');
    if ($scripts === false) {
        print_status($step, status_fail, 'Could not list scripts in install/sql/, please check access permissions');
    }
    sort($scripts);
    print_status($step, status_ok, sprintf('Found %d scripts', count($scripts)));
}


foreach ($scripts as $script) {
    $step = sprintf('Checking %s', basename($script));
    {
        if (strpos(basename($script), '0') === 0) {
            print_status($step, status_ok, 'skipped');
            continue;
        }

        $dbChecksum = $database->getInstallScriptChecksum($script);
        if ($dbChecksum === null) {
            $step = sprintf('Executing new %s', $script);
            {
                $result = $database->executeFile($script);
                if ($result !== null) {
                    print_status($step, status_fail, sprintf('Could not execute setup script, failed step: %s', $result));
                }
                $executedScripts[$script] = sha1_file($script);
            }
        } else {
            $step = sprintf('Verifying checksum for %s', $script);
            {
                $fsChecksum = sha1_file($script);
                if ($dbChecksum !== $fsChecksum) {
                    print_status($step, status_fail, sprintf('Calculated checksum is different between database (%s) and filesystem (%s). Please correct manually!',
                            $dbChecksum, $fsChecksum));
                }
            }
        }
        print_status($step, status_ok);
    }
}

$step = 'Saving update information';
{
    $database->begin();
    foreach ($executedScripts as $script => $checksum) {
        if ($database->createTableEntry(Database::TABLE_UPDATE_INFO, array(
                        'Script' => $script,
                        'Checksum' => $checksum
                )) !== 1) {
            $database->rollBack();
            print_status($step, status_fail, sprintf('Could not create update_info entry for %s', $script));
        }
    }
    $database->commit();
    print_status($step, status_ok);
}

$step = 'Verifying existing accounts';
{
    if (Database::getInstance()->getPlayerCount() === 0) {
        print_status($step, status_needs_upgrade, 'No accounts found');
        $step = 'Create new admin account';
        {
            Database::getInstance()->begin();
            $password = createRandomPassword();
            $id = Database::getInstance()->createUser('admin', 'admin@localhost', null, $password);
            if ($id === null) {
                print_status($step, status_fail, 'Could not create new user');
            }
            if (Database::getInstance()->updateTableEntry(Database::TABLE_USERS, $id, array('Admin' => 1)) !== 1) {
                print_status($step, status_fail, 'Could not grant admin rights');
            }

            Database::getInstance()->commit();
            print_status($step, status_ok, sprintf("New user 'admin' with password '%s'", $password));
        }
    } else {
        print_status($step, status_ok);
    }
}

$step = 'Checking for runtime configuration';
{
    if (file_exists('../config/roundstart.ini')) {
        print_status($step, status_needs_upgrade, 'Found roundstart.ini');
        $step = 'Migrating roundstart.ini to database';
        {
            $roundStartIni = parse_ini_file('../config/roundstart.ini');
            $database->begin();
            if ($database->createTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'roundstart', 'conf_value' => $roundStartIni['roundstart'])) === null) {
                $database->rollBack();
                print_status($step, status_fail, 'Could not insert roundstart information');
            }
            if (!unlink('../config/roundstart.ini')) {
                $database->rollBack();
                print_status($step, status_fail, 'Could not delete obsolete config/roundstart.ini');
            }
            $database->commit();
            print_status($step, status_ok);
        }
    } else {
        print_status($step, status_ok);
    }
}

$step = 'Verifying last cronjob run timestamp';
{
    if ($database->existsTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'lastcron'))) {
        print_status($step, status_ok);
    } else {
        print_status($step, status_needs_upgrade, 'Entry not found');
        $step = 'Create lastcron entry';
        {
            $database->begin();
            $lastCron = time();
            $lastCron -= ($lastCron % (Config::getInt(Config::SECTION_BASE, 'cron_interval') * 60));
            if ($database->createTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'lastcron', 'conf_value' => $lastCron)) === null) {
                $database->rollBack();
                print_status($step, status_fail, 'Could not insert lastcron information');
            }
            $database->commit();
            print_status($step, status_ok);
        }
    }
}

$step = 'Verifying last points calculation timestamp';
{
    if ($database->existsTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'lastpoints'))) {
        print_status($step, status_ok);
    } else {
        print_status($step, status_needs_upgrade, 'Entry not found');
        $step = 'Create lastpoints entry';
        {
            $database->begin();
            $lastCron = time();
            $lastCron -= ($lastCron % (Config::getInt(Config::SECTION_BASE, 'cron_interval') * 60));
            if ($database->createTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'lastpoints', 'conf_value' => $lastCron)) === null) {
                $database->rollBack();
                print_status($step, status_fail, 'Could not insert lastpoints information');
            }
            $database->commit();
            print_status($step, status_ok);
        }
    }
}

$step = 'Verifying currently active round';
{
    if ($database->existsTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'roundstart'))) {
        print_status($step, status_ok);
    } else {
        print_status($step, status_needs_upgrade, 'No active round found');
        $step = 'Starting new round';
        {
            $database->begin();
            if ($database->createTableEntry(Database::TABLE_RUNTIME_CONFIG, array('conf_name' => 'roundstart', 'conf_value' => time())) === null) {
                $database->rollBack();
                print_status($step, status_fail, 'Could not insert roundstart information');
            }
            $database->commit();
            print_status($step, status_ok);
        }
    }
}

$dauer = 1000 * (microtime(true) - $start);
http_response_code(200);
echo "\n";
echo "Update finished successfully!\n";
echo 'Execution took ' . number_format($dauer, 2) . " ms\n";
echo Database::getInstance()->getQueryCount() . " queries were executed\n";