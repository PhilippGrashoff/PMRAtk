<?php

namespace PMRAtk\Data;

class FileLogger {

    public $fileName;


    /*
     *
     */
    public function __construct(string $filename) {
        if(!file_exists($filename)) {
            throw new \atk4\data\Exception('The file '.$filename.' was not found in '.__CLASS__);
        }
        $this->fileName = $filename;
    }


    /*
     *
     */
    public function emptyLogFile() {
        if(false === file_put_contents($this->fileName, '')) {
            throw new \atk4\data\Exception('Could not empty log file: '.$this->fileName. ' in '.__FUNCTION__);
        }
    }


    /*
     *
     */
    public function log(string $level, string $message, array $context = []) {

        if(false === file_put_contents(
        $this->fileName,
        //$message. PHP_EOL.'    '.$level.' '.implode(', ', $context).PHP_EOL,
        $message. PHP_EOL,
        FILE_APPEND)) {
            throw new \atk4\data\Exception('Failed to write Logger info to file: '.$this->fileName. ' in '.__FUNCTION__);
        }
    }
}