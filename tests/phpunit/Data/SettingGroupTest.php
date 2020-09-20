<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\SettingGroup;
use PMRAtk\tests\phpunit\TestCase;

class SettingGroupTest extends TestCase {

    /*
     * test init
     */
    public function testInit() {
        $s = new SettingGroup(self::$app->db);
        $s->save();
        self::assertTrue(true);
    }
}
