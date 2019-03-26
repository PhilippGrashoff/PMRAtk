<?php

namespace PMRAtk\tests\phpunit\Data;

class FileLoggerTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testLogger() {
        $fl = new \PMRAtk\Data\FileLogger(FILE_BASE_PATH.'tests/logs/testlog.txt');
        $fl->emptyLogFile();
        $fl->log('1', 'bla');
        $this->assertTrue(file_exists(FILE_BASE_PATH.'tests/logs/testlog.txt'));
    }


    /*
     * test exceptions
     */
    public function testEmptyLogFileException() {
        $fl = new \PMRAtk\Data\FileLogger(FILE_BASE_PATH.'tests/logs/forbiddenlog.txt');
        $this->expectException(\atk4\data\Exception::class);
        $fl->emptyLogFile();
    }


    /*
     * test exceptions
     */
    public function testLogException() {
        $fl = new \PMRAtk\Data\FileLogger(FILE_BASE_PATH.'tests/logs/forbiddenlog.txt');
        $this->expectException(\atk4\data\Exception::class);
        $fl->log('1', 'bla');
    }
}
