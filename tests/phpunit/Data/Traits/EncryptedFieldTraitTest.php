<?php

namespace PMRAtk\tests\phpunit\Data\Traits;


class EmailWithEncryptedField extends \PMRAtk\Data\Email {

    use \PMRAtk\Data\Traits\EncryptedFieldTrait;

    public function init() {
        parent::init();
        $this->encryptField($this->getField('value'), ENCRYPTFIELD_KEY);
    }
}


class EncryptedFieldTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testFieldValueSameAfterLoading() {
        $e = new EmailWithEncryptedField(self::$app->db);
        $e->set('value', 'Duggu');
        $e->save();
        $id = $e->get('id');
        $e->unload();

        $e->load($id);
        $this->assertEquals($e->get('value'), 'Duggu');
    }


    /*
     * hack: set value with crypted class, load with uncrypted class
     */
    public function testValueStoredEncrypted() {
        $e = new EmailWithEncryptedField(self::$app->db);
        $e->set('value', 'Duggu');
        $e->save();

        $e2 = new \PMRAtk\Data\Email(self::$app->db);
        $e2->load($e->id);
        $this->assertNotEquals($e2->get('value'), 'Duggu');
        $this->assertTrue(strlen($e2->get('value')) > 50);
    }


    /*
     * hack: set value with uncrypted class, load with crypted class
     */
    /* temporarily disabledpublic function testExceptionOnDecryptFail() {
        $e = new \PMRAtk\Data\Email(self::$app->db);
        $e->set('value', 'Duggu');
        $e->save();

        $e2 = new EmailWithEncryptedField(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $e2->load($e->id);
    }


    /*
     * hack: set value with crypted class, load with crypted class, truncate
     * load again with crypted class
     */
    /* temporarily disabledpublic function testExceptionOnDecryptFailTwo() {
        $e = new EmailWithEncryptedField(self::$app->db);
        $e->set('value', 'Duggu');
        $e->save();

        $e2 = new \PMRAtk\Data\Email(self::$app->db);
        $e2->load($e->id);
        $v = $e2->get('value');
        $v[0] = 'a';
        $e2->set('value', $v);
        $e2->save();

        $this->expectException(\atk4\data\Exception::class);
        $e->reload();
    }
    /**/
}
