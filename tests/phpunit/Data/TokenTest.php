<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use DateTime;
use PMRAtk\Data\Token;
use PMRAtk\Data\UserException;
use PMRAtk\tests\phpunit\TestCase;

class TokenTest extends TestCase {

    /*
     * test if token length can be set
     */
    public function testTokenLength() {
        //standard: 64 long
        $t = new Token(self::$app->db);
        $t->save();
        self::assertEquals(64, strlen($t->get('value')));

        //try to set differently
        $t = new Token(self::$app->db, ['tokenLength' => 128]);
        $t->save();
        self::assertEquals(128, strlen($t->get('value')));
    }


    /*
     * see if expires can be set
     */
     public function testSetExpires() {
        //try to set to 180 minutes
        $t = new Token(self::$app->db, ['expiresAfterInMinutes' => 180]);
        $t->save();
        self::assertEquals(
            (new DateTime())->modify('+180 Minutes')->format('Ymd Hi'),
            $t->get('expires')->format('Ymd Hi')
        );
    }


    /*
     * see if exception is thrown when trying to load expired token
     */
    public function testExceptionLoadExpired() {
        $t = new Token(self::$app->db);
        $t->reload_after_save = false;
        $t->set('expires', (new DateTime())->modify('-1 Minutes'));
        $t->save();

        $this->expectException(UserException::class);
        $t->reload();
    }
}
