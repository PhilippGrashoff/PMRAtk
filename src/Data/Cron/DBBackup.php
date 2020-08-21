<?php declare(strict_types=1);

namespace PMRAtk\Data\Cron;

use atk4\data\Exception;

class DBBackup extends BaseCronJob {

    public $name = 'Datenbank Backup';

    public $description = 'Erstellt ein Gzip komprimiertes Update der Datenbank';

    /**
     *
     */
    public function _execute() {
        $sql_file = FILE_BASE_PATH.CRON_FILE_PATH.substr(DB_STRING, strrpos(DB_STRING, '=') + 1)."_".date('Ymd_Hi').".sql";
        $output = [];
        $return_var = null;

        //create dump
        exec('mysqldump -h '.substr(DB_STRING, strpos(DB_STRING, '=') + 1, (strpos(DB_STRING, ';') - (strpos(DB_STRING, '=') + 1))).' -u '.DB_USER.' -p\''.DB_PASSWORD.'\' --quick --allow-keywords --add-drop-table --complete-insert --quote-names '.substr(DB_STRING, strrpos(DB_STRING, '=') + 1).' > '.$sql_file, $output, $return_var);
        if($return_var !== 0) {
            throw new Exception('The DB Backup could not be created (exit code '.$return_var.'): '.implode(PHP_EOL, $output)); // @codeCoverageIgnore
        }
        //gzip it
        exec("gzip $sql_file", $output, $return_var);
        if($return_var !== 0) {
            throw new Exception('The DB Backup File could not be gzipped (exit code '.$return_var.'): '.implode(PHP_EOL, $output)); // @codeCoverageIgnore
        }
    }
}