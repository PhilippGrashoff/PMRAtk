<?php
    //include config
    require_once(dirname(dirname(__FILE__)).'/config.php');

    $output = [];
    $exit_code = 0;

    echo 'drop database'.PHP_EOL;
    exec("mysql -h 127.0.0.1 -u ".DB_USER." -p'".DB_PASSWORD."' -e 'DROP DATABASE IF EXISTS `".substr(DB_STRING, strrpos(DB_STRING, '=') + 1)."`;'", $output, $exit_code);
    if($exit_code !== 0) {
        exit($exit_code);
    }


    echo 'create database'.PHP_EOL;
    exec("mysql -h 127.0.0.1 -u ".DB_USER." -p'".DB_PASSWORD."' -e 'CREATE DATABASE IF NOT EXISTS `".substr(DB_STRING, strrpos(DB_STRING, '=') + 1)."` DEFAULT CHARACTER SET latin1 COLLATE latin1_german1_ci;'", $output, $exit_code);
    if($exit_code !== 0) {
        exit($exit_code);
    }


    echo 'run setup sql'.PHP_EOL;
    exec("mysql -h 127.0.0.1 -u ".DB_USER." -p'".DB_PASSWORD."' ".substr(DB_STRING, strrpos(DB_STRING, '=') + 1)." < .circleci/setup/20190114.sql", $output, $exit_code);
    if($exit_code !== 0) {
        exit($exit_code);
    }


    echo 'insert test data'.PHP_EOL;
    exec("mysql -h 127.0.0.1 -u ".DB_USER." -p'".DB_PASSWORD."' ".substr(DB_STRING, strrpos(DB_STRING, '=') + 1)." < tests/insert_test_data.sql", $output, $exit_code);
    if($exit_code !== 0) {
        exit($exit_code);
    }
?>
