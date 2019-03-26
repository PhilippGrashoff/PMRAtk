<?php

namespace PMRAtk\tests\phpunit\Data;

class AuditTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * see if created_by and created_by_name are set on save
     */
    public function testUserInfoOnSave() {
        $audit = new \PMRAtk\Data\Audit(self::$app->db);
        $audit->save();
        $this->assertEquals($audit->get('created_by'),      self::$app->auth->user->get('id'));
        $this->assertEquals($audit->get('created_by_name'), self::$app->auth->user->get('name'));
    }
}
