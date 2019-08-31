<?php

class SettingNoDecrypt extends \PMRAtk\Data\BaseModel {

    public $table = 'setting';


    public function init()
    {
        parent::init();

        $this->addFields([
            ['ident', 'type' => 'string'],
            ['name', 'type' => 'string'],
            ['description', 'type' => 'text'],
            ['system', 'type' => 'integer', 'system' => true],
            ['category', 'type' => 'string'],
            ['value', 'type' => 'string'],
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
        $setting = new SettingNoDecrypt(self::$app->db);

        $setting->load($ea->id);
        
        //if encrypted, it shouldnt be unserializable
        $this->assertFalse(@unserialize($setting->get('value')));


        $ea2 = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea2->load($ea->id);
        $this->assertEquals($ea2->get('user'),       'some1');
        $this->assertEquals($ea2->get('password'),   'some2');
        $this->assertEquals($ea2->get('imap_host'),  'some3');
        $this->assertEquals($ea2->get('imap_port'),  'some4');
        $this->assertEquals($ea2->get('smtp_host'),  'some5');
        $this->assertEquals($ea2->get('smtp_port'),  'some6');
    }


    /*
     *
     */
    public function testReturnOnNoArray() {
        $ea = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea->set('user', 'Ddfsdfsd');
        $ea->save();

        //check if its encrypted by using normal setting
        $setting = new SettingNoDecrypt(self::$app->db);
        $setting->load($ea->get('id'));
        $setting->set('value', '');
        $setting->save();

        $ea->reload();

        self::assertEquals('', $ea->get('user'));
    }
}
