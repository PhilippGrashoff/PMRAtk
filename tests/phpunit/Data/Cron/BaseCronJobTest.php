<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Cron;

use PMRAtk\Data\Cron\BaseCronJob;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\CronTestClasses\SampleCron;
use PMRAtk\tests\TestClasses\CronTestClasses\SampleCronWithEmailMessage;
use PMRAtk\tests\TestClasses\CronTestClasses\SampleCronWithException;
use PMRAtk\tests\TestClasses\CronTestClasses\SampleCronWithoutExecuteImplemented;

class BaseCronJobTest extends TestCase {

    public function testSuccessfulCronJob() {
        $this->_addStandardEmailAccount();
        $c = new SampleCronWithEmailMessage(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
        self::assertTrue($c->successful);
        self::assertEquals(1, count($c->app->userMessages));
    }

    public function testExceptionCronJob() {
        $this->_addStandardEmailAccount();
        $c = new SampleCronWithException(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
        //an email was sent
        self::assertFalse($c->successful);
    }

    public function testExceptionNoExecuteImplemented() {
        $this->_addStandardEmailAccount();
        self::expectException(\atk4\data\Exception::class);
        $c = new SampleCronWithoutExecuteImplemented(self::$app, ['addAdminToSuccessEmail' => true]);
        $c->execute();
    }

    public function testNoEmailOnNoSuccessMessage() {
        $this->_addStandardEmailAccount();
        self::$app->userMessages = [];
        $c = new SampleCron(self::$app);
        $c->execute();
        self::assertTrue($c->successful);
        self::assertTrue(empty($c->phpMailer->getLastMessageID()));
    }

    public function testNoRecipientNoSuccessMessage() {
        $this->_addStandardEmailAccount();
        self::$app->userMessages[] = ['message' => 'Duggu', 'class' => 'error'];
        $c = new SampleCron(self::$app);
        $c->execute();
        self::assertTrue($c->successful);
        self::assertTrue(empty($c->phpMailer->getLastMessageID()));
    }

    public function testGetName() {
        $this->_addStandardEmailAccount();
        $c = new SampleCronWithEmailMessage(self::$app);
        self::assertEquals('TestName', $c->getName());
        $c = new SampleCron(self::$app);
        self::assertEquals('SomeTestCron', $c->getName());
    }
}
