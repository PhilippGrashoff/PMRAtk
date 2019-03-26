<?php

namespace PMRAtk\Data;

class FileLogger {

    public $fileName;


    /*
     *
     */
    public function __construct(string $filename) {
        $this->fileName = $filename;
    }


    /*
     *
     */
    public function emptyLogFile() {
        try {
            file_put_contents($this->fileName, '');
        }
        catch(\Exception $e) {
            throw new \atk4\data\Exception('Could not empty log file: '.$this->fileName. ' in '.__FUNCTION__.'. Message: '.$e->getMessage());
        }
    }


    /*
     *
     */
    public function log(string $level, string $message, array $context = []) {
        try {
            file_put_contents(
                $this->fileName,
                //$message. PHP_EOL.'    '.$level.' '.implode(', ', $context).PHP_EOL,
                $message,
                FILE_APPEND);
        }
        catch(\Exception $e) {
            throw new \atk4\data\Exception('Failed to write Logger info to file: '.$this->fileName. ' in '.__FUNCTION__.'. Message: '.$e->getMessage());
        }
    }
}