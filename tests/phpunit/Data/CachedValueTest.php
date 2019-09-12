<?php

namespace PMRAtk\tests\phpunit\Data;

class CachedValueTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * test init
     */
    public function testInit() {
        $s = new \PMRAtk\Data\CachedValue(self::$app->db);
        $this->assertTrue(true);
    }


    /*
     * make sure setting is only saved once
     */
    public function testUnique() {
        $initial_count = (new \PMRAtk\Data\CachedValue(self::$app->db))->action('count')->getOne();
        $s = new \PMRAtk\Data\CachedValue(self::$app->db);
        $s->set('ident', 'LALA');
        $s->set('value', '1');
        $s->save();

        $s = new \PMRAtk\Data\CachedValue(self::$app->db);
        $s->set('ident', 'LALA');
        $s->set('value', '2');
        $s->save();
    
        $this->assertEquals($initial_count + 1, (new \PMRAtk\Data\CachedValue(self::$app->db))->action('count')->getOne());
    }
}
