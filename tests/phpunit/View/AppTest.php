<?php

class AppTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testAppConstruct() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $this->assertTrue($app->auth->user instanceOf \PMRAtk\Data\User);
    }


    /*
     * test if exception is thrown if no array with user roles which may
     * see this page is passed
     */
    public function testExceptionEmptyRoleArray() {
        $this->expectException(\atk4\data\Exception::class);
        $app = new \PMRAtk\View\App([], ['always_run' => false]);
    }


    /*
     * tests TokenLogin
     */
    public function testTokenLogin() {
        $token = self::$app->auth->user->setNewToken();
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $app->loadUserByToken($token);
        //some assertion so PHPUnit does not complain
        $this->assertTrue(true);
    }


    /*
     *
     */
    public function testTokenLoginTokenNotFoundException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $this->expectException(\atk4\data\Exception::class);
        $app->loadUserByToken('sfsdfssdfeg');
    }


    /*
     *
     */
    public function testTokenLoginUserForTokenNotFoundException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $token = new \PMRAtk\Data\Token(self::$app->db);
        $token->save();
        $this->expectException(\atk4\data\Exception::class);
        $app->loadUserByToken($token->get('value'));
    }


    /*
     *
     */
    public function testaddSummerNote() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $app->addSummernote();
        $this->assertTrue($app->auth->user instanceOf \PMRAtk\Data\User);

    }


    /*
     *
     */
    public function testDeviceWidth() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $_SESSION['device_width'] = 500;
        $app->getDeviceWidth();
        $this->assertEquals(500, $app->deviceWidth);
        $_POST['device_width'] = 800;
        $app->getDeviceWidth();
        $this->assertEquals(800, $app->deviceWidth);
    }


    /*
     *
     */
    public function testgetEmailTemplateExceptionIfTemplateNotFound() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $this->expectException(\atk4\data\Exception::class);
        $app->loadEmailTemplate('DDFUSFsfdfse');
    }


    /*
     *
     */
    public function testgetCachedValue() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $app->setCachedValue('LALA', 'hamma');
        $this->assertEquals('hamma', $app->getCachedValue('LALA'));
    }

    /*
     *
     */
    public function testgetCachedValueWithTimeout() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $app->setCachedValue('LALA', 'hamma');
        usleep(1100000);
        $this->assertEquals(null, $app->getCachedValue('LALA', 1));
        $this->assertEquals('hamma', $app->getCachedValue('LALA'));
    }
}
