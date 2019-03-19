<?php

namespace PMRAtk\tests\phpunit\Traits\Data;


class CryptTest extends \atk4\data\Model {

    use \PMRAtk\Data\Traits\CryptIdTrait;

    public $table = 'SecondaryBaseModel';
}

class CryptWhileTest extends \PMRAtk\Data\SecondaryBaseModel {

    use \PMRAtk\Data\Traits\CryptIdTrait;

    public $table = 'SecondaryBaseModel';
    public $counter = 0;
    public $useA = true;


    protected function _generateCryptId() {
        $this->counter ++;
        if($this->counter < 3 || $this->useA) {
            return 'a';
        }
        else {
            return $this->getRandomChar();
        }
    }
}


class CryptIdTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * test getting a random char
     */
    public function testgetRandomChar() {
        $t = new CryptTest(self::$app->db);
        for($i = 0; $i < 10; $i++) {
            $this->assertTrue(in_array($t->getRandomChar(), $t->possibleChars));
        }
    }


    /*
     * _generateCryptId needs to be explicitely overwritten in child class
     * see if exception is thrown if not.
     */
    public function testExceptionOverwriteGenerate() {
        $t = new CryptTest(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($t, '_generateCryptId');
    }


    /*
     * use Token class to test setCryptId
     */
    public function testsetCryptId() {
        $t = new \PMRAtk\Data\Token(self::$app->db);
        $t->setCryptId('value');
        $this->assertEquals(strlen($t->get('value')), 64);
    }


    /*
     * test if cryptId is recalcualted if existing one is found. Wrote stupid
     * test model for that :)
     */
    public function testCryptIdRegeneratedOnExist() {
        $t = new CryptWhileTest(self::$app->db);
        $t->setCryptId('value');
        $t->save();
        //should be 'a'
        $this->assertEquals('a', $t->get('value'));

        $t2 = new CryptWhileTest(self::$app->db);
        $t2->useA = false;
        $t2->setCryptId('value');
        $this->assertNotEquals('a', $t2->get('value'));
    }
}
