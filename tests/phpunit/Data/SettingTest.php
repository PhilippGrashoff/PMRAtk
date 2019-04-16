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
}
