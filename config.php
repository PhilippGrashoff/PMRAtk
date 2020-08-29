<?php

declare(strict_types=1);
//Load ATK framework
require_once(__DIR__.'/vendor/autoload.php');

//include local config
if(file_exists(__DIR__.'/config_local.php')) {
    require_once(__DIR__.'/config_local.php');
}

//Start Session, used for user login
session_start();

//Tech Contact
define('TECH_ADMIN_EMAIL',   'test1@easyoutdooroffice.com');
define('TECH_ADMIN_NAME',    'Philipp Reisigl');

//Standard path to root dir
define('FILE_BASE_PATH',        __DIR__.'/');
define('SAVE_FILES_IN',         'output');

//where cronjobs creating files should store them. Password protect that folder/disable http access
define('CRON_FILE_PATH',        'cron/files/');

//German Weekday names
define('STD_GERMAN_WEEKDAYS',
[
    1 => 'Montag',
    2 => 'Dienstag',
    3 => 'Mittwoch',
    4 => 'Donnerstag',
    5 => 'Freitag',
    6 => 'Samstag',
    7 => 'Sonntag',
]);

//German Month names
define('STD_GERMAN_MONTHNAMES',
[
    1  => 'Januar',
    2  => 'Februar',
    3  => 'März',
    4  => 'April',
    5  => 'Mai',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'August',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Dezember',
]);
