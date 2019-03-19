<?php

namespace PMRAtk\tests\phpunit;

class TestApp extends \PMRAtk\View\App {
    public $always_run = false;
    public $catch_exceptions = false;
    public $debug = false;


    /*
     *  automatically login user with ID=1
     */
    protected function _addAuth() {
        parent::_addAuth();
        $this->auth->user->load(1);
    }


    /*
     *  dump DSQL query in console in debug mode
     */
    public function debug($expr, $took) {
        if (!$this->debug) {
            return;
        }
        $stderr = fopen('php://stderr', 'w');
        $Message =  sprintf("[%02.6f] %s\n", $took, $expr->getDebugQuery());
        fwrite($stderr,$Message);
        fclose($stderr);
    }
}
