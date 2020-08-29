<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;

use atk4\data\Exception;
use PMRAtk\Data\Token;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\BaseModelClasses\CryptIdModel;
use PMRAtk\tests\TestClasses\BaseModelClasses\CryptIdSecondaryModel;


class CryptIdTraitTest extends TestCase
{

    public function testgetRandomChar()
    {
        $t = new CryptIdModel(self::$app->db);
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(in_array($t->getRandomChar(), $t->possibleChars));
        }
    }

    /**
     * _generateCryptId needs to be explicitly overwritten in child class
     * see if exception is thrown if not.
     */
    public function testExceptionOverwriteGenerate()
    {
        $t = new CryptIdModel(self::$app->db);
        $this->expectException(Exception::class);
        $this->callProtected($t, '_generateCryptId');
    }

    public function testsetCryptId()
    {
        $t = new Token(self::$app->db);
        $t->setCryptId('value');
        $this->assertEquals(strlen($t->get('value')), 64);
    }

    /**
     * test if cryptId is recalculated if existing one is found. Wrote stupid
     * test model for that :)
     */
    public function testCryptIdRegeneratedOnExist()
    {
        $t = new CryptIdSecondaryModel(self::$app->db);
        $t->setCryptId('value');
        $t->save();
        $this->assertEquals('a', $t->get('value'));

        $t2 = new CryptIdSecondaryModel(self::$app->db);
        $t2->useA = false;
        $t2->setCryptId('value');
        $this->assertNotEquals('a', $t2->get('value'));
    }
}
