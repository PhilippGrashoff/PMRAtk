<?php
//Show all errors on testing env
error_reporting(E_ALL);

//Database access
define('DB_STRING',           'sqlite::memory:');
define('DB_USER',             '');
define('DB_PASSWORD',         '');

//Subdomain + Domain
define('URL_BASE_PATH',       'http://localhost:8080/');

define('CUSTOMER_ADMIN_EMAIL', 'test2@easyoutdooroffice.com');

//test field encryption
define('ENCRYPTFIELD_KEY',     '1234567890abcefd1234567890abcefd');