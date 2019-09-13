<?php

namespace PMRAtk\tests\phpunit\Data;

class SettingTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * test init
     */
    public function testInit() {
        $s = new \PMRAtk\Data\Setting(self::$app->db);
        $this->assertTrue(true);
    }


    /*
     *
     */
    public function testSystemSettingNotDeletable() {
        $s = new \PMRAtk\Data\Setting(self::$app->db);
        $s->set('system', 1);
        $s->save();
        $this->expectException(\PMRAtk\Data\UserException::class);
        $s->delete();
    }


    /*
     *
     */
    public function testSystemSettingIdentNotEditable() {
        $s = new \PMRAtk\Data\Setting(self::$app->db);
        $s->set('system', 1);
        $s->set('ident', 'SOMEIDENT');
        $s->save();
        $this->expectException(\atk4\data\Exception::class);
        $s->set('ident', 'SOMEOTHERIDENT');
    }
}
