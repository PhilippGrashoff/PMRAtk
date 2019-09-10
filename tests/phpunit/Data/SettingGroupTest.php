<?php

namespace PMRAtk\tests\phpunit\Data;

class SettingGroupTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * test init
     */
    public function testInit() {
        $s = new \PMRAtk\Data\SettingGroup(self::$app->db);
        $s->save();
        $this->assertTrue(true);
    }
}
