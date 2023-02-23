<?php
/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */

class Config
{
    public const SECTION_BASE = 'base';
    public const SECTION_CAPTCHA = 'captcha';
    public const SECTION_DATABASE = 'database';
    public const SECTION_PLANTAGE = 'plantage';
    public const SECTION_RESEARCH_LAB = 'research_lab';
    public const SECTION_SHOP = 'shop';
    public const SECTION_KEBAB_STAND = 'kebab_stand';
    public const SECTION_BUILDING_YARD = 'building_yard';
    public const SECTION_SCHOOL = 'school';
    public const SECTION_FENCE = 'fence';
    public const SECTION_PIZZERIA = 'pizzeria';
    public const SECTION_STARTING_VALUES = 'starting_values';
    public const SECTION_BANK = 'bank';
    public const SECTION_MARKET = 'market';
    public const SECTION_CONTRACT = 'contract';
    public const SECTION_MAFIA = 'mafia';
    public const SECTION_MAFIA_ESPIONAGE = 'mafia_espionage';
    public const SECTION_MAFIA_ROBBERY = 'mafia_robbery';
    public const SECTION_MAFIA_HEIST = 'mafia_heist';
    public const SECTION_MAFIA_ATTACK = 'mafia_attack';
    public const SECTION_GROUP = 'group';
    public const SECTION_DBCONF = 'runtime_conf';

    private static ?Config $INSTANCE = null;
    private array $iniFile;

    function __construct()
    {
        $defaultsIni = parse_ini_file(__DIR__ . '/../config/config-defaults.ini', true);
        $userIni = parse_ini_file(__DIR__ . '/../config/config.ini', true);
        $this->iniFile = array_replace_recursive($defaultsIni, $userIni);
    }

    public static function enhanceFromDb(?array $executeAndExtractRows)
    {
        if ($executeAndExtractRows === null) return;
        foreach ($executeAndExtractRows as $row) {
            switch ($row['conf_name']) {
                case 'roundstart':
                case 'lastcron':
                    self::getInstance()->iniFile[self::SECTION_DBCONF][$row['conf_name']] = intval($row['conf_value']);
                    break;
                default:
                    self::getInstance()->iniFile[self::SECTION_DBCONF][$row['conf_name']] = $row['conf_value'];
                    break;
            }
        }
    }

    public static function getSection(string $section): array
    {
        $result = self::getInstance()->_get($section, null);
        if ($section === self::SECTION_STARTING_VALUES) {
            foreach ($result as $key => $value) {
                if ($value === 'null' || $value === '') {
                    $result[$key] = null;
                }
            }
        }
        return $result;
    }

    public static function get(string $section, string $option)
    {
        return self::getInstance()->_get($section, $option);
    }

    public static function getInt(string $section, string $option): int
    {
        return intval(self::get($section, $option));
    }

    public static function getFloat(string $section, string $option): float
    {
        return floatval(self::get($section, $option));
    }

    public static function getBoolean(string $section, string $option): bool
    {
        switch (self::get($section, $option)) {
            case '1':
            case 'yes':
            case 'true':
                return true;
            default:
                return false;
        }
    }

    private static function getInstance(): Config
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new Config();
        }
        return self::$INSTANCE;
    }

    private function _get(string $section, ?string $option)
    {
        if (!array_key_exists($section, $this->iniFile)) {
            trigger_error(sprintf('Configuration section "%s" is not defined!', $section), E_USER_ERROR);
        }
        $options = $this->iniFile[$section];
        if ($option === null) {
            return $options;
        }
        if (!array_key_exists($option, $options)) {
            trigger_error(sprintf('Configuration option "%s.%s" is not defined!', $section, $option), E_USER_ERROR);
        }
        return $options[$option];
    }
}
