<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\Audit;
use PMRAtk\tests\phpunit\TestCase;

class AuditTest extends TestCase {

    /**
     * see if created_by and created_by_name are set on save
     */
    public function testUserInfoOnSave() {
        $audit = new Audit(self::$app->db);
        $audit->save();
        $this->assertEquals($audit->get('created_by_name'), self::$app->auth->user->get('name'));
    }
}
