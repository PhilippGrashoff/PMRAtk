<?php declare(strict_types=1);

namespace EOO\tests\phpunit\Data\Cron;

use PMRAtk\Data\Cron\BaseCronJob;
use PMRAtk\tests\phpunit\TestCase;

class SampleCronJob extends BaseCronJob {

    public $name = 'TestName';

    public function _execute() {
        $this->recipients[] = 'test2@easyoutdooroffice.com';
        $this->app->addUserMessage('Test Test');
    }
}


class SampleExceptionCronJob extends BaseCronJob {

    public function _execute() {
        $this->recipients[] = 'test2@easyoutdooroffice.com';
        throw new \atk4\data\Exception('Some shit happened');
    }
}


class DoesNotImplementExecuteCronJob extends BaseCronJob {

}


class NoMessageNoSuccessEmail extends BaseCronJob {

    public function _execute() {
    }
}


class BaseCronJobTest extends TestCase {

    /*
     *
     */
    public function testSuccessfulCronJob() {
        $this->_addStandardEmailAccount();
        $c = new SampleCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
        //App has a userMessage set
        $this->assertTrue($c->successful);
        $this->assertEquals(1, count($c->app->userMessages));
    }


    /*
     *
     */
    public function testExceptionCronJob() {
        $this->_addStandardEmailAccount();
        $c = new SampleExceptionCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
        //an email was sent
        $this->assertFalse($c->successful);
    }


    /*
     *
     */
    public function testExceptionNoExecuteImplemented() {
        $this->_addStandardEmailAccount();
        $this->expectException(\atk4\data\Exception::class);
        $c = new DoesNotImplementExecuteCronJob(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
    }


    /*
     *
     */
    public function testNoEmailOnNoSuccessMessage() {
        $this->_addStandardEmailAccount();
        self::$app->userMessages = [];
        $c = new NoMessageNoSuccessEmail(self::$app);
        $c->execute();
        $this->assertTrue($c->successful);
        $this->assertTrue(empty($c->phpMailer->getLastMessageID()));
    }


    /*
     *
     */
    public function testNoRecipientNoSuccessMessage() {
        $this->_addStandardEmailAccount();
        self::$app->userMessages[] = ['message' => 'Duggu', 'class' => 'error'];
        $c = new NoMessageNoSuccessEmail(self::$app);
        $c->execute();
        $this->assertTrue($c->successful);
        $this->assertTrue(empty($c->phpMailer->getLastMessageID()));
    }


    /*
     *
     */
    public function testGetName() {
        $this->_addStandardEmailAccount();
        $c = new SampleCronJob(self::$app);
        $this->assertEquals('TestName', $c->getName());
        $c = new NoMessageNoSuccessEmail(self::$app);
        $this->assertEquals('NoMessageNoSuccessEmail', $c->getName());
    }
}
