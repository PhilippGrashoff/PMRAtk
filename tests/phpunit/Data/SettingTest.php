<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\Exception;
use PMRAtk\Data\Setting;
use PMRAtk\Data\UserException;
use PMRAtk\tests\phpunit\TestCase;

class SettingTest extends TestCase {

    /*
     * test init
     */
    public function testInit() {
        $s = new Setting(self::$app->db);
        $this->assertTrue(true);
    }


    /*
     *
     */
    public function testSystemSettingNotDeletable() {
        $s = new Setting(self::$app->db);
        $s->set('system', 1);
        $s->save();
        $this->expectException(UserException::class);
        $s->delete();
    }


    /*
     *
     */
    public function testSystemSettingIdentNotEditable() {
        $s = new Setting(self::$app->db);
        $s->set('system', 1);
        $s->set('ident', 'SOMEIDENT');
        $s->save();
        $this->expectException(Exception::class);
        $s->set('ident', 'SOMEOTHERIDENT');
    }
}
