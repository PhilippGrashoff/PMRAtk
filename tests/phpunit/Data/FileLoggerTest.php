<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\Exception;
use PMRAtk\Data\FileLogger;
use PMRAtk\tests\phpunit\TestCase;

class FileLoggerTest extends TestCase {


    /*
     *
     */
    public function testLogger() {
        $fl = new FileLogger(FILE_BASE_PATH.'tests/logs/testlog.txt');
        $fl->emptyLogFile();
        $fl->log('1', 'bla');
        self::assertTrue(file_exists(FILE_BASE_PATH.'tests/logs/testlog.txt'));
    }


    /*
     * test exceptions
     */
    public function testEmptyLogFileException() {
        $fl = new FileLogger(FILE_BASE_PATH.'tests/logs/forbiddenlog.txt');
        $this->expectException(Exception::class);
        $fl->emptyLogFile();
    }


    /*
     * test exceptions
     */
    public function testLogException() {
        $fl = new FileLogger(FILE_BASE_PATH.'tests/logs/forbiddenlog.txt');
        $this->expectException(Exception::class);
        $fl->log('1', 'bla');
    }
}
