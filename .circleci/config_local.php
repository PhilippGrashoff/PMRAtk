<?php
//Show all errors on testing env
error_reporting(E_ALL);


//Tech Contact
define('TECH_ADMIN_EMAIL',   'test1@easyoutdooroffice.com');
define('TECH_ADMIN_NAME',    'Philipp Reisigl');

//Database access
define('DB_STRING',           'mysql:host=localhost;dbname=PMRAtk_test');
define('DB_USER',             'root');
define('DB_PASSWORD',         'test');

//Subdomain + Domain
define('URL_BASE_PATH',       'http://localhost:8080/');

//email sending
//TODO: Move To DB!
define('STD_EMAIL',           'dgfgdfgd');
define('STD_EMAIL_NAME',      'dgfdgdfg');
define('EMAIL_HOST',          'vdgdfgdfg');
define('EMAIL_PORT',          'dgfddfggd');
define('EMAIL_USERNAME',      'gdgdfgdg');
define('EMAIL_PASSWORD',      'dfgfdgdfgdfgd');
define('IMAP_PATH_SENT_MAIL', 'dgfdgfdgdfg');


//testing
define('TEST_DATEFORMAT',     'us');