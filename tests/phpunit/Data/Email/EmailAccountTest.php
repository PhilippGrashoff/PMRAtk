<?php

class EANoDecrypt extends \PMRAtk\Data\BaseModel {

    public $table = 'email_account';


    public function init()
    {
        parent::init();

        $this->addFields([
            ['credentials', 'type' => 'string'],
        ]);
    }
}


class EmailAccountTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testHooks() {
        $ea = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea->set('user',      'some1');
        $ea->set('password',  'some2');
        $ea->set('imap_host', 'some3');
        $ea->set('imap_port', 'some4');
        $ea->set('smtp_host', 'some5');
        $ea->set('smtp_port', 'some6');
        $ea->save();

        //check if its encrypted by using normal setting
        $setting = new EANoDecrypt(self::$app->db);
        $setting->load($ea->id);
        //if encrypted, it shouldnt be unserializable
        $this->assertFalse(@unserialize($setting->get('credentials')));
        self::assertFalse(strpos($setting->get('credentials'), 'some1'));

        $ea2 = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea2->load($ea->id);
        $this->assertEquals($ea2->get('user'),       'some1');
        $this->assertEquals($ea2->get('password'),   'some2');
        $this->assertEquals($ea2->get('imap_host'),  'some3');
        $this->assertEquals($ea2->get('imap_port'),  'some4');
        $this->assertEquals($ea2->get('smtp_host'),  'some5');
        $this->assertEquals($ea2->get('smtp_port'),  'some6');
    }
}
