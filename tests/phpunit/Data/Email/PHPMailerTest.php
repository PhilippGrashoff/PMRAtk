<?php

class PHPMailerTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testAddUUID() {
        $tt = new \PMRAtk\Data\Email\PHPMailer(self::$app);
        $_ENV['IS_TEST_MODE'] = true;
        $_ENV['TEST_EMAIL_UUID'] = 'DUDUDU';
        $this->assertFalse($tt->send());
    }
}
