<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit;

use PMRAtk\tests\Traits\TestLoggerTrait;
use PMRAtk\App\App;

class TestApp extends App {

    use TestLoggerTrait;

    public $always_run = false;
    public $catch_exceptions = false;
    public $debug = false;


    /*
     *  automatically login user with ID=1
     */
    protected function _addAuth() {
        parent::_addAuth();
        //TODO DISABLED FOR NOW$this->auth->user->load(1);
    }
}
