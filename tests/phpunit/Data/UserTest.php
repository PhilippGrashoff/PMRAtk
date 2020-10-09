<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\ValidationException;
use auditforatk\Audit;
use Exception;
use PMRAtk\Data\Token;
use PMRAtk\Data\User;
use traitsforatkdata\UserException;
use PMRAtk\tests\phpunit\TestCase;

class UserTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        User::class,
        Audit::class,
    ];

    public function testUserNameUnique()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->save();

        $c2 = new User($persistence);
        $c2->set('name', 'sfsdf');
        $c2->set('username', 'ABC');
        self::expectException(Exception::class);
        $c2->save();
    }

    public function testNameUnique()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->save();

        $c2 = new User($persistence);
        $c2->set('name', 'Duggu');
        $c2->set('username', 'AdasdsadBC');
        $exception_found = false;
        try {
            $c2->save();
        } catch (Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);
    }

    public function testValidateEmptyName()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('username', 'ABC');
        self::expectException(ValidationException::class);
        $c->save();
    }

    public function testValidateEmptyUserName()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'ABC');
        self::expectException(ValidationException::class);
        $c->save();
    }

    public function testExceptionSetNewPasswordOtherUserLoggedIn()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->set('password', 'ABC');
        $c->save();

        self::expectException(\atk4\data\Exception::class);
        $c->setNewPassword('ggg', 'ggg');
    }

    public function testExceptionSetNewPasswordOldPasswordWrong()
    {
        $persistence = $this->getSqliteTestPersistence();
        self::$app->auth->user->set('password', 'EW');
        self::$app->auth->user->save();

        self::expectException(UserException::class);
        self::$app->auth->user->setNewPassword('ggg', 'ggg', true, 'falseoldpw');
    }

    public function testExceptionSetNewPasswordsDoNotMatch()
    {
        self::expectException(UserException::class);
        self::$app->auth->user->setNewPassword('gggfgfg', 'ggg');
    }

    public function testSetNewPassword()
    {
        self::$app->auth->user->setNewPassword('gggg', 'gggg');
        self::assertTrue(true);
    }

    public function testsendResetPasswordEmail()
    {
        $persistence = $this->getSqliteTestPersistence();
        $this->_addStandardEmailAccount($persistence);
        $c = new User($persistence);

        //unexisting username should throw exception
        $exception_found = false;
        try {
            $c->sendResetPasswordEmail('LOBO');
        } catch (Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //with correct username it should work
        $initial_token_count = (new Token($persistence))->action('count')->getOne();
        $c->sendResetPasswordEmail('test');
        self::assertEquals($initial_token_count + 1, (new Token($persistence))->action('count')->getOne());
    }

    public function testResetPassword()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'Duggu');
        $c->set('username', 'Duggudd');
        $c->save();
        $token = $c->setNewToken();

        //unexisting username should throw exception
        $exception_found = false;
        try {
            $c->resetPassword('nonexistingtoken', 'nuggu', 'nuggu');
        } catch (Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //non matching passwords should cause exception
        $exception_found = false;
        try {
            $c->resetPassword($token, 'nuggu', 'duggu');
        } catch (Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //that should work
        $c->resetPassword($token, 'nuggu', 'nuggu');

        //token should be deleted
        $t = new Token($persistence);
        $t->tryLoadBy('value', $token);
        self::assertFalse($t->loaded());
    }

    public function testResetPasswordTokenNotConnectedToModel()
    {
        $persistence = $this->getSqliteTestPersistence();
        $c = new User($persistence);
        $c->set('name', 'Duggu');
        $c->set('username', 'Duggudd');
        $c->save();
        $token = $c->setNewToken();

        //token should be deleted
        $t = new Token($persistence);
        $t->loadBy('value', $token);
        $t->set('model_id', 99999);
        $t->save();

        self::expectException(UserException::class);
        $c->resetPassword($token, 'DEDE', 'DEDE');
    }

    public function testUserRights()
    {
        $persistence = $this->getSqliteTestPersistence();
        //first test logged in user, should be true
        self::assertTrue($this->callProtected(self::$app->auth->user, '_standardUserRights'));

        //different user than the logged in one, should be false
        $u = new User($persistence);
        self::assertFalse($this->callProtected($u, '_standardUserRights'));

        //no logged in user? false
        $initial = self::$app->auth->user;
        self::$app->auth->user = null;
        $res = $this->callProtected($u, '_standardUserRights');
        self::$app->auth->user = $initial;
        self::assertFalse($res);
    }
}
