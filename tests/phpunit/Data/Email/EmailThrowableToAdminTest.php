<?php

class ThrowableTest {
    use \PMRAtk\Data\Email\EmailThrowableToAdminTrait;

    public $app;


    public function throwThat() {
        try {
            throw new \Exception();
        }
        catch (\Throwable $e) {
            $this->sendErrorEmailToAdmin($e, 'Error', ['test3@easyoutdooroffice.com']);
            return false;
        }
    }
}


class EmailThrowableToAdminTest extends \PMRAtk\tests\phpunit\TestCase {
    /*
     *
     */
    public function testNewPHPMailer() {
        $tt = new ThrowableTest();
        $tt->app = self::$app;
        $this->assertFalse($tt->throwThat());
    }
}
