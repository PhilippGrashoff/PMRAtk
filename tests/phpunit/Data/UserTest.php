<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\ValidationException;
use Exception;
use PMRAtk\Data\Token;
use PMRAtk\Data\User;
use traitsforatkdata\UserException;
use PMRAtk\tests\phpunit\TestCase;

class UserTest extends TestCase {


    /*
     * test if username is unique
     */
    public function testUserNameUnique() {
        $c = new User(self::$app->db);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->save();

        $c2 = new User(self::$app->db);
        $c2->set('name', 'sfsdf');
        $c2->set('username', 'ABC');
        $exception_found = false;
        try {
            $c2->save();
        }
        catch(Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);
    }


    /*
     * test if name is unique
     */
    public function testNameUnique() {
        $c = new User(self::$app->db);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->save();

        $c2 = new User(self::$app->db);
        $c2->set('name', 'Duggu');
        $c2->set('username', 'AdasdsadBC');
        $exception_found = false;
        try {
            $c2->save();
        }
        catch(Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);
    }


    /*
     *
     */
    public function testValidateEmptyName() {
        $c = new User(self::$app->db);
        $c->set('username', 'ABC');
        self::expectException(ValidationException::class);
        $c->save();
    }


    /*
     *
     */
    public function testValidateEmptyUserName() {
        $c = new User(self::$app->db);
        $c->set('name', 'ABC');
        self::expectException(ValidationException::class);
        $c->save();
    }


    /*
     * see if exception is thrown if setNewPassword is called with a different user
     * logged in
     */
    public function testExceptionSetNewPasswordOtherUserLoggedIn() {
        $c = new User(self::$app->db);
        $c->set('name', 'Duggu');
        $c->set('username', 'ABC');
        $c->set('password', 'ABC');
        $c->save();

        self::expectException(\atk4\data\Exception::class);
        $c->setNewPassword('ggg', 'ggg');
    }


    /*
     * see if exception is thrown if setNewPassword is called and old password
     * does not match
     */
    public function testExceptionSetNewPasswordOldPasswordWrong() {
        self::$app->auth->user->set('password', 'EW');
        self::$app->auth->user->save();

        self::expectException(UserException::class);
        self::$app->auth->user->setNewPassword('ggg', 'ggg', true, 'falseoldpw');
    }


    /*
     * see if exception is thrown if setNewPassword is called and new passwords
     * do not match
     */
    public function testExceptionSetNewPasswordsDoNotMatch() {
        self::expectException(UserException::class);
        self::$app->auth->user->setNewPassword('gggfgfg', 'ggg');
    }


    /*
     * this setPassword should work
     */
    public function testSetNewPassword() {
        self::$app->auth->user->setNewPassword('gggg', 'gggg');
        self::assertTrue(true);
    }


    /*
     * test sending of password reset email
     */
    public function testsendResetPasswordEmail() {
        $this->_addStandardEmailAccount();
        $c = new User(self::$app->db);

        //unexisting username should throw exception
        $exception_found = false;
        try {
            $c->sendResetPasswordEmail('LOBO');
        }
        catch(Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //with correct username it should work
        $initial_token_count = (new Token(self::$app->db))->action('count')->getOne();
        $c->sendResetPasswordEmail('test');
        self::assertEquals($initial_token_count + 1, (new Token(self::$app->db))->action('count')->getOne());
    }


    /*
     *
     */
    public function testResetPassword() {
         $c = new User(self::$app->db);
         $c->set('name', 'Duggu');
         $c->set('username', 'Duggudd');
         $c->save();
         $token = $c->setNewToken();

        //unexisting username should throw exception
        $exception_found = false;
        try {
            $c->resetPassword('nonexistingtoken', 'nuggu', 'nuggu');
        }
        catch(Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //non matching passwords should cause exception
        $exception_found = false;
        try {
            $c->resetPassword($token, 'nuggu', 'duggu');
        }
        catch(Exception $e) {
            $exception_found = true;
        }
        self::assertTrue($exception_found);

        //that should work
        $c->resetPassword($token, 'nuggu', 'nuggu');

        //token should be deleted
        $t = new Token(self::$app->db);
        $t->tryLoadBy('value', $token);
        self::assertFalse($t->loaded());
    }


    /*
     *
     */
    public function testResetPasswordTokenNotConnectedToModel() {
        $c = new User(self::$app->db);
        $c->set('name', 'Duggu');
        $c->set('username', 'Duggudd');
        $c->save();
        $token = $c->setNewToken();

        //token should be deleted
        $t = new Token(self::$app->db);
        $t->loadBy('value', $token);
        $t->set('model_id', 99999);
        $t->save();

        self::expectException(UserException::class);
        $c->resetPassword($token, 'DEDE', 'DEDE');
    }


    /*
     *
     */
    public function testUserRights() {
        //first test logged in user, should be true
        self::assertTrue($this->callProtected(self::$app->auth->user, '_standardUserRights'));

        //different user than the logged in one, should be false
        $u = new User(self::$app->db);
        self::assertFalse($this->callProtected($u, '_standardUserRights'));

        //no logged in user? false
        $initial = self::$app->auth->user;
        self::$app->auth->user = null;
        $res = $this->callProtected($u, '_standardUserRights');
        self::$app->auth->user = $initial;
        self::assertFalse($res);


    }
}
