<?php

namespace PMRAtk\tests\phpunit\Data;

class AuditTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        $_ENV['CREATE_AUDIT'] = true;
    }

    /*
     *
     */
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        $_ENV['CREATE_AUDIT'] = false;
    }


    /*
     * see if created_by and created_by_name are set on save
     */
    public function testUserInfoOnSave() {
        $audit = new \PMRAtk\Data\Audit(self::$app->db);
        $audit->save();
        $this->assertEquals($audit->get('created_by'),      self::$app->auth->user->get('id'));
        $this->assertEquals($audit->get('created_by_name'), self::$app->auth->user->get('name'));
    }


    /*
     *
     */
    public function testAuditCreatedForFields() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $a->save();
        $this->assertEquals(1, $a->getAuditViewModel()->action('count')->getOne());
        $a->set('name', 'TEST');
        $a->save();
        $this->assertEquals(2, $a->getAuditViewModel()->action('count')->getOne());
        $a->set('BaseModelB_id', '1');
        $a->save();
        $this->assertEquals(3, $a->getAuditViewModel()->action('count')->getOne());
        $a->set('time', '10:00');
        $a->set('date', '2019-05-05');
        $a->save();
        $this->assertEquals(4, $a->getAuditViewModel()->action('count')->getOne());
        $a->addAdditionalAudit('SOMETYPE', []);
        $this->assertEquals(5, $a->getAuditViewModel()->action('count')->getOne());
    }
}
