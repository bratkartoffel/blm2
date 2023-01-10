<?php /** @noinspection DuplicatedCode */
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

/*
 * Copy this file to 'config.inc.php' and adjust as needed
 */
// title for this game
const game_title = 'Der Bioladenmanager 2';

// a random secret which is used to initialize the random number generators
// for the deterministic interest and item selling rates
// please generate a random value, e.g. at
// https://www.random.org/strings/?num=5&len=20&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
const random_secret = '!!replace this!!';

// a random secret which allows to upgrade the database schema
// please generate a random value, e.g. at
// https://www.random.org/strings/?num=5&len=20&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
const upgrade_secret = '!!replace this!!';

// database access credentials
const database_hostname = 'localhost';
const database_username = 'blm2';
const database_password = 'blm2';
const database_database = 'blm2';

// base url for this game (needed for absolute urls like in mails)
const base_url = 'https://blm2.example.com';

// operator name
const admin_name = 'Insert Name Here';

// operator email address
const admin_email = 'contact-address@example.com';

// address line 1 (for impressum)
const admin_addr_line_1 = 'Street Name, may be empty';

// address line 2 (for impressum)
const admin_addr_line_2 = 'Zip-Code and Country, may be empty';

// is the maintenance mode active
const maintenance_active = false;

// message to display when maintenance mode is on
const maintenance_message = 'Das Spiel befindet sich gerade im Wartungsmodus (Einspielen von Updates und Bugfixes).
Bitte versuchen Sie es in ein paar Minuten erneut.';

// is the registration closed?
// if true, then registration of new accounts is disabled
const registration_closed = true;

// set the timezone for this game
date_default_timezone_set('Europe/Berlin');

/*
 * ------------------------------------------------------------------------------------------------------
 * The settings below this line are settings you normally don't need to adjust.
 * Like balancing stuff and various restrictions.
 * ------------------------------------------------------------------------------------------------------
 */
// version string as displayed in the footer
require_once 'game_version.inc.php';

// should only be set when running tests
// disables the captcha validation
const is_testing = false;

// minimum length of passwords
// any passwords shorter than this length are not allowed
const password_min_len = 6;

// password security settings
// only Argon2 variants should be used (PASSWORD_ARGON2I, PASSWORD_ARGON2D or PASSWORD_ARGON2ID)
const password_hash_algorithm = PASSWORD_ARGON2ID;

// options for the password hashing algorithm
const password_hash_options = array('memory_cost' => 16384, 'time_cost' => 8, 'threads' => 2);

// minimum length of usernames
const username_min_len = 2;

// maximum length of usernames
// should match "mitglieder.Name" column
const username_max_len = 20;

// maximum length of email addresses
// should match "mitglieder.EMail" column
const email_max_len = 96;

// redirect all mails sent to the users to the administrator?
// can be used to debug / test various stuff without flooding the users with spam mails
const redirect_all_mails_to_admin = false;

// session timeout (in seconds)
// inactive users will be force logged out if they are idle longer than this period
const session_timeout = 3600;

// number of implemented wares
const count_wares = 15;

// number of implemented buildings
const count_buildings = 8;

// base income per item and level each cronjob interval
// x kg per research level, each half hour
const item_base_production = 4;

// minimum number of points required to use mafia
const mafia_min_ponts = 18000;

// minimum percentage of money stolen from opponent
const mafia_raub_min_rate = 0.4;

// maximum percentage of money stolen from opponent
const mafia_raub_max_rate = 0.75;

// blocking times for the various mafia actions
const mafia_sperrzeit_factor_war = 0.5;

// point range for mafia actions (own points / enemy points must be within this range)
const mafia_faktor_punkte = 1.5;

// enum constant for group diplomacy types
const group_diplomacy_nap = 1;
const group_diplomacy_bnd = 2;
const group_diplomacy_war = 3;

// how long group diplomacy relations have to be active before they can be canceled (days)
const group_diplomacy_min_duration = 7;

// enum constants for mafia actions
const mafia_action_espionage = 0;
const mafia_action_robbery = 1;
const mafia_action_heist = 2;
const mafia_action_attack = 3;

// bonus factors (for chances) for the various mafia buildings
// each building level increments the success chances by these percentages (1 = 100%, 0.5 = 50%, 0 = 0%)
const mafia_bonus_factor_pizzeria = 0.025;
const mafia_bonus_factor_fence = 0.025;

// minimum duration of all researches (in seconds)
const research_min_duration = 7200;

// base income for all players
const income_base = 30;

// income bonus per shop level
const income_bonus_shop = 5;

// income bonus per doenerstand level
const income_bonus_kebab_stand = 8;

// maximum count of members per group
const group_max_members = 15;

// maximum length of group name
// should match "gruppe.Name" column
const group_max_name_length = 32;

// maximum length of group tag
// should match "gruppe.Kuerzel" column
const group_max_tag_length = 6;

// minimum plantage level required to join a group
const min_plantage_level_join_group = 5;

// minimum plantage level required to create a group
const min_plantage_level_create_group = 8;

// when surrendering a war, each member will lose this amount of points (1 = 100%, 0.5 = 50%, 0 = 0%)
const group_war_loose_points = 0.10;

// when surrendering a war, each member will lose that many levels of plantage
const group_war_loose_plantage = 1;

// minimum amount of money to fight a war for
const group_war_min_amount = 100000;

// length of a game round (in seconds; default is 3 months)
const game_round_duration = 7776000;

// pause after a game round (in seconds, default is 7 days)
const game_pause_duration = 604800;

// each level of research lab reduces research duration (1 = 100%, 0.5 = 50%, 0 = 0%)
const research_lab_bonus_factor = 0.055;

// each level of building yard reduces building duration (1 = 100%, 0.5 = 50%, 0 = 0%)
const building_yard_bonus_factor = 0.061;

// base price for each item
const item_price_base = 1.35;

// bonus to sell price for each research level
const item_price_research_bonus = 0.15;

// bonus to sell price for each shop level
const item_price_shop_bonus = 0.08;

// bonus to sell price for each school level
const item_price_school_bonus = 0.12;

// bonus to sell price for later items
const item_price_item_id_factor = 0.40;

// bonus to production amount for each research level
const research_production_weight_factor = 8;

// malus to production cost for each research level
const research_production_cost_factor = 4;

// bonus to production amount for later items
const production_weight_item_id_factor = 20;

// bonus to production amount for each plantage level
const production_plantage_item_id_factor = 10;

// number of entries to show in ranking table
const ranking_page_size = 15;

// number of entries to show in group ranking table
const group_page_size = 10;

// number of entries to show in admin log table
const admin_log_page_size = 25;

// number of entries to show in the market table
const market_page_size = 20;

// number of entries to show in the message table
const messages_page_size = 20;

// maximum size for a profile picture (in bytes, default 256k)
const max_profile_image_size = 262144;

// when retracting an offer from the market, return that amount of the item (1 = 100%, 0.5 = 50%, 0 = 0%)
const market_retract_rate = 0.90;

// when successfully selling an item on the market, the market keeps a provision (1 = 100%, 0.5 = 50%, 0 = 0%)
const market_provision_rate = 0.02;

// when canceling a building job, then this amount of money is refunded (1 = 100%, 0.5 = 50%, 0 = 0%)
const action_retract_rate = 0.75;

// minimum and maximum sell price of an item in percent of the current shop price
// (1 = 100%, 0.5 = 50%, 0 = 0%)
const market_min_sell_price = 0.75;
const market_max_sell_price = 2;

// minimum and maximum sell price of an item in percent of the current shop price
// (1 = 100%, 0.5 = 50%, 0 = 0%)
const contract_min_sell_price = 0.5;
const contract_max_sell_price = 2;

// starting values for each new player or reset account
// key is the database table name, value is an associative array with column => value pairs
const starting_values = array(
    'mitglieder' => array(
        'Geld' => 5000,
        'Bank' => 0,
        'Punkte' => 0,
        'IgmGesendet' => 0,
        'IgmEmpfangen' => 0,
        'NextMafia' => null,
        'OnlineZeit' => 0,
        'OnlineZeitSinceLastCron' => 0,
        'Gruppe' => null,
        'GruppeLastMessageZeit' => null,
        'LastLogin' => null,
        'Gebaeude1' => 1,
        'Gebaeude2' => 0,
        'Gebaeude3' => 0,
        'Gebaeude4' => 0,
        'Gebaeude5' => 0,
        'Gebaeude6' => 0,
        'Gebaeude7' => 0,
        'Gebaeude8' => 0,
        'Forschung1' => 1,
        'Forschung2' => 0,
        'Forschung3' => 0,
        'Forschung4' => 0,
        'Forschung5' => 0,
        'Forschung6' => 0,
        'Forschung7' => 0,
        'Forschung8' => 0,
        'Forschung9' => 0,
        'Forschung10' => 0,
        'Forschung11' => 0,
        'Forschung12' => 0,
        'Forschung13' => 0,
        'Forschung14' => 0,
        'Forschung15' => 0,
        'Lager1' => 100,
        'Lager2' => 0,
        'Lager3' => 0,
        'Lager4' => 0,
        'Lager5' => 0,
        'Lager6' => 0,
        'Lager7' => 0,
        'Lager8' => 0,
        'Lager9' => 0,
        'Lager10' => 0,
        'Lager11' => 0,
        'Lager12' => 0,
        'Lager13' => 0,
        'Lager14' => 0,
        'Lager15' => 0,
    ),
    'statistik' => array(
        'AusgabenGebaeude' => 0,
        'AusgabenForschung' => 0,
        'AusgabenZinsen' => 0,
        'AusgabenProduktion' => 0,
        'AusgabenMarkt' => 0,
        'AusgabenVertraege' => 0,
        'AusgabenMafia' => 0,
        'EinnahmenGebaeude' => 0,
        'EinnahmenVerkauf' => 0,
        'EinnahmenZinsen' => 0,
        'EinnahmenMarkt' => 0,
        'EinnahmenVertraege' => 0,
        'EinnahmenMafia' => 0,
        'GebaeudePlus' => 0,
        'ForschungPlus' => 0,
        'ProduktionPlus' => 0,
        'MafiaPlus' => 0,
        'MafiaMinus' => 0,
        'KriegMinus' => 0,
    ),
);

const mafia_base_data = array(
    // espionage
    array(
        array('cost' => 200, 'chance' => .2),
        array('cost' => 400, 'chance' => .3),
        array('cost' => 600, 'chance' => .4),
        array('cost' => 800, 'chance' => .5),
        'points' => 25,
        'waittime' => 300,
    ),
    // robbery
    array(
        array('cost' => 300, 'chance' => .2),
        array('cost' => 600, 'chance' => .3),
        array('cost' => 900, 'chance' => .4),
        array('cost' => 1200, 'chance' => .5),
        'points' => 75,
        'waittime' => 900,
    ),
    // heist
    array(
        array('cost' => 500, 'chance' => .2),
        array('cost' => 1000, 'chance' => .3),
        array('cost' => 1500, 'chance' => .4),
        array('cost' => 2000, 'chance' => .5),
        'points' => 150,
        'waittime' => 1800,
    ),
    // attack
    array(
        array('cost' => 10000, 'chance' => .05),
        array('cost' => 25000, 'chance' => .10),
        array('cost' => 40000, 'chance' => .15),
        array('cost' => 65000, 'chance' => .20),
        'points' => 1000,
        'waittime' => 14400,
    ),
);

// building data for plantage
const plantage_base_cost = 260;
const plantage_base_duration = 1780;
const plantage_base_points = 120;
const plantage_factor_cost = 1.35;
const plantage_factor_duration = 1.20;
const plantage_factor_points = 1.23;

// building data for research lab
const research_lab_base_cost = 320;
const research_lab_base_duration = 1900;
const research_lab_base_points = 105;
const research_lab_factor_cost = 1.37;
const research_lab_factor_duration = 1.23;
const research_lab_factor_points = 1.20;

// building data for shop
const shop_base_cost = 260;
const shop_base_duration = 1800;
const shop_base_points = 90;
const shop_factor_cost = 1.35;
const shop_factor_duration = 1.22;
const shop_factor_points = 1.20;

// building data for kebab stand
const kebab_stand_base_cost = 310;
const kebab_stand_base_duration = 2150;
const kebab_stand_base_points = 115;
const kebab_stand_factor_cost = 1.38;
const kebab_stand_factor_duration = 1.22;
const kebab_stand_factor_points = 1.21;

// building data for building yard
const building_yard_base_cost = 620;
const building_yard_base_duration = 2250;
const building_yard_base_points = 235;
const building_yard_factor_cost = 1.40;
const building_yard_factor_duration = 1.24;
const building_yard_factor_points = 1.22;

// building data for school
const school_base_cost = 300;
const school_base_duration = 2050;
const school_base_points = 110;
const school_factor_cost = 1.39;
const school_factor_duration = 1.24;
const school_factor_points = 1.19;

// building data for fence
const fence_base_cost = 650;
const fence_base_duration = 2800;
const fence_base_points = 285;
const fence_factor_cost = 1.45;
const fence_factor_duration = 1.28;
const fence_factor_points = 1.17;

// building data for pizza
const pizzeria_base_cost = 650;
const pizzeria_base_duration = 2800;
const pizzeria_base_points = 285;
const pizzeria_factor_cost = 1.45;
const pizzeria_factor_duration = 1.28;
const pizzeria_factor_points = 1.17;

// base production amount of all items
const production_base_amount = 350;

// base production cost for all items
const production_base_cost = 200;

// maximum duration of a single production (in hours)
const production_hours_max = 12;

// base data for researches
const research_base_cost = 230;
const research_base_duration = 2400;
const research_base_points = 80;
const research_factor_cost = 1.29;
const research_factor_duration = 1.19;
const research_factor_points = 1.18;

// minimum rate for selling prices (1 = 100%, 0.5 = 50%, 0 = 0%)
const wares_rate_min = 0.7;

// maximum rate for selling prices (1 = 100%, 0.5 = 50%, 0 = 0%)
const wares_rate_max = 1.0;

// interval of cronjob (in minutes)
const cron_interval = 30;

// maximum amount a user may deposit on the bank account
const deposit_limit = 100000;

// maximum amount a user may overdraw a bank account
const credit_limit = -15000;

// when reaching this bank account limit, a user will be reset
const dispo_limit = -100000;

// minimum interest rate (1 = 100%, 0.5 = 50%, 0 = 0%)
const interest_debit_rate_min = 0.011;

// maximum interest rate (1 = 100%, 0.5 = 50%, 0 = 0%)
const interest_debit_rate_max = 0.015;

// minimum credit rate (1 = 100%, 0.5 = 50%, 0 = 0%)
const interest_credit_rate_min = 0.017;

// maximum credit rate (1 = 100%, 0.5 = 50%, 0 = 0%)
const interest_credit_rate_max = 0.023;

if (maintenance_active) {
    if (defined('IS_CRON')) {
        die("Maintenance active\n");
    } else {
        die(sprintf('<!DOCTYPE html><html lang="de"><body><img src="/pics/big/clock.webp" alt="maintenance"/><h2>%s</h2></body></html>', maintenance_message));
    }
}

// start a http session
session_start();

$lastResetfile = dirname(__FILE__) . '/last_reset.inc.php';
if (!file_exists($lastResetfile)) {
    $startTimestamp = date('Y-m-d H:00:00');
    $fp = fopen($lastResetfile, 'w');
    if ($fp === false) {
        die('Could not create ' . $lastResetfile . ', please check for write permissions!');
    }
    fwrite($fp, "<?php
define('last_reset', strtotime('$startTimestamp'));
");
    fclose($fp);
}
require_once($lastResetfile);
