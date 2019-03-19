<?php

namespace PMRAtk\tests\phpunit\Data;

class TokenTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * test if token length can be set
     */
    public function testTokenLength() {
        //standard: 64 long
        $t = new \PMRAtk\Data\Token(self::$app->db);
        $t->save();
        $this->assertEquals(64, strlen($t->get('value')));

        //try to set differently
        $t = new \PMRAtk\Data\Token(self::$app->db, ['tokenLength' => 128]);
        $t->save();
        $this->assertEquals(128, strlen($t->get('value')));
    }


    /*
     * see if expires can be set
     */
     public function testSetExpires() {
        //try to set to 180 minutes
        $t = new \PMRAtk\Data\Token(self::$app->db, ['expiresInMinutes' => 180]);
        $t->save();
        $this->assertEquals((new \DateTime())->modify('+180 Minutes')->format('Ymd Hi'), $t->get('expires')->format('Ymd Hi'));
    }


    /*
     * see if exception is thrown when trying to load expired token
     */
    public function testExceptionLoadExpired() {
        $t = new \PMRAtk\Data\Token(self::$app->db);
        $t->reload_after_save = false;
        $t->set('expires', (new \DateTime())->modify('-1 Minutes'));
        $t->save();

        $this->expectException(\PMRAtk\Data\UserException::class);
        $t->reload();
    }
}
