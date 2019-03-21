<?php

class SampleCronJob extends \PMRAtk\Data\Cron\BaseCronJob {

    public function execute() {
        $this->app->addUserMessage('Test Test');
    }
}


class SampleExceptionCronJob extends \PMRAtk\Data\Cron\BaseCronJob {

    public function execute() {
        throw new \atk4\data\Exception('Some shit happened');
    }
}


class DoesNotImplementExecuteCronJob extends \PMRAtk\Data\Cron\BaseCronJob {

}


class BaseCronJobTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testSuccessfulCronJob() {
        $c = new SampleCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
        //an email was sent
        $this->assertTrue(strlen($c->phpMailer->getSentMIMEMessage()) > 0);
        //App has a userMessage set
        $this->assertTrue($c->successful);
        $this->assertEquals(1, count($c->app->userMessages));
    }


    /*
     *
     */
    public function testExceptionCronJob() {
        $c = new SampleExceptionCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
        //an email was sent
        $this->assertTrue(strlen($c->phpMailer->getSentMIMEMessage()) > 0);
        $this->assertFalse($c->successful);
    }


    /*
     *
     */
    public function testExceptionNoExecuteImplemented() {
        $this->expectException(\atk4\data\Exception::class);
        $c = new DoesNotImplementExecuteCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
    }
}
