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
}
