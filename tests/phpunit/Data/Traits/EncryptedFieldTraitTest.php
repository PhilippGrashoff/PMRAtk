<?php

namespace PMRAtk\tests\phpunit\Traits\Data;


class EmailWithEncryptedField extends \PMRAtk\Data\Email {

    use \PMRAtk\Data\Traits\EncryptedFieldTrait;

    public function init() {
        parent::init();
        $this->encryptField($this->getElement('value'), sodium_crypto_stream_keygen());
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
}
