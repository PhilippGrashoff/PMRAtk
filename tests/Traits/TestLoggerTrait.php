<?php

namespace PMRAtk\tests\Traits;

trait TestLoggerTrait {

    public $logger;


    /*
     * adds a "headline" to sql log
     */
    public function addLogHeadLine(string $function_name) {
        $this->_getLogger()->log(\Psr\Log\LogLevel::DEBUG, PHP_EOL.PHP_EOL.$function_name.PHP_EOL);
    }


    /*
     *
     */
    public function dblog($expr, $took) {
        $this->_getLogger()->log(\Psr\Log\LogLevel::DEBUG, sprintf("[%02.6f] %s\n", $took, $expr->getDebugQuery()));
    }


    /*
     *
     */
    protected function _getLogger() {
        if(!$this->logger) {
            $this->logger = new \PMRAtk\Data\FileLogger(FILE_BASE_PATH.'tests/logs/dblog.txt');
            $this->logger->emptyLogFile();
        }

        return $this->logger;
    }
}