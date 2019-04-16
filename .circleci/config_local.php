<?php
//Show all errors on testing env
error_reporting(E_ALL);


//Tech Contact
define('TECH_ADMIN_EMAIL',   'test1@easyoutdooroffice.com');
define('TECH_ADMIN_NAME',    'Philipp Reisigl');

//Database access
define('DB_STRING',           'mysql:host=127.0.0.1;dbname=PMRAtk_test');
define('DB_USER',             'root');
define('DB_PASSWORD',         'test');

//Subdomain + Domain
define('URL_BASE_PATH',       'http://localhost:8080/');

//email sending
//TODO: Move To DB!

//testing
define('TEST_DATEFORMAT',     'us');


//test field encryption
define('ENCRYPTFIELD_KEY',     '1234567890abcefd');