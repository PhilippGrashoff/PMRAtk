<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;

use PMRAtk\Data\User;
use PMRAtk\tests\phpunit\TestCase;

class MaxFailedLoginsTraitTest extends TestCase {

    public static $userBefore;


    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if(isset(self::$app->auth->user)) {
            self::$userBefore = self::$app->auth->user;
        }
    }


    /**
     *
     */
    public static function tearDownAfterClass(): void
    {
        self::$app->auth->user = self::$userBefore;
        parent::tearDownAfterClass();
    }


    /**
     *
     */
    public function testFailedLoginIncrease() {
        $u = new User(self::$app->db);
        $u->set('username', 'DUggu');
        $u->set('name', 'Jsdfsdf');
        $u->save();
        self::$app->auth->user = $u;
        self::assertEquals(0, $u->get('failed_logins'));
        $u->addFailedLogin();
        self::assertEquals(1, $u->get('failed_logins'));
    }


    /**
     *
     */
    public function testGetRemainingLogins() {
        $u = new User(self::$app->db);
        $u->set('username', 'DUggu');
        $u->set('name', 'Jsdfsdf');
        $u->save();
        self::$app->auth->user = $u;
        self::assertEquals(10, $u->getRemainingLogins());
        $u->addFailedLogin();
        self::assertEquals(9, $u->getRemainingLogins());
        $u->maxFailedLogins = 1;
        self::assertEquals(0, $u->getRemainingLogins());
    }


    /**
     *
     */
    public function testSetFailedLoginsToZero() {
        $u = new User(self::$app->db);
        $u->set('username', 'DUggu');
        $u->set('name', 'Jsdfsdf');
        $u->save();
        self::$app->auth->user = $u;
        $u->addFailedLogin();
        self::assertEquals(1, $u->get('failed_logins'));
        $u->setFailedLoginsToZero();
        self::assertEquals(0, $u->get('failed_logins'));
    }


    /**
     *
     */
    public function testHasTooManyFailedLogins() {
        $u = new User(self::$app->db);
        $u->set('username', 'DUggu');
        $u->set('name', 'Jsdfsdf');
        $u->save();
        self::$app->auth->user = $u;
        $u->addFailedLogin();
        $u->maxFailedLogins = 1;
        self::assertFalse($u->hasTooManyFailedLogins());
        $u->addFailedLogin();
        self::assertTrue($u->hasTooManyFailedLogins());
    }
}