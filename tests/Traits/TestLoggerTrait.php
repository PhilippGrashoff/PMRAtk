<?php declare(strict_types=1);

namespace PMRAtk\tests\Traits;

use PMRAtk\Data\FileLogger;
use Psr\Log\LogLevel;

trait TestLoggerTrait {

    public $logger;

    public $createLog = false;

    public $queryCount = 0;


    /*
     * adds a "headline" to sql log
     */
    public function addLogHeadLine(string $text) {
        if(!$this->createLog) {
            return;
        }
        $this->_getLogger()->log(LogLevel::DEBUG, PHP_EOL.PHP_EOL.$text.PHP_EOL);
    }


    /*
     *
     */
    public function addLogFootLine(string $text) {
        if(!$this->createLog) {
            return;
        }
        $this->_getLogger()->log(LogLevel::DEBUG, PHP_EOL.$text.PHP_EOL);
    }


    /*
     *
     */
    public function dblog($expr, $took) {
        if(!$this->createLog) {
            return;
        }
        $this->queryCount++;
        $this->_getLogger()->log(LogLevel::DEBUG, sprintf("[%02.6f] %s\n", $took, $expr->getDebugQuery()));
    }


    /*
     *
     */
    protected function _getLogger() {
        if(!$this->logger) {
            $this->logger = new FileLogger(FILE_BASE_PATH.'tests/logs/dblog.txt');
            $this->logger->emptyLogFile();
        }

        return $this->logger;
    }
}