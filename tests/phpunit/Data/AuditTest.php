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


        //make sure CREATE AND CHANGE Audits are there
        $change_found = false;
        $create_found = false;
        foreach($a->getAuditViewModel() as $audit) {
            if($audit->get('value') == 'CREATE') {
                $create_found = true;
            }
            if($audit->get('value') == 'CHANGE') {
                $change_found = true;
            }
        }
        $this->assertTrue($change_found);
        $this->assertTrue($create_found);
    }


    /*
     * test create delete Audit
     */
      public function testDeleteAudit() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $a->save();
        $initial_audit_count = (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne();
        $a->delete();
        $this->assertEquals($initial_audit_count + 1, (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne());

        //make sure newest audit is of type delete
        $a = new \PMRAtk\Data\Audit(self::$app->db);
        $a->setOrder('id DESC');
        $a->setLimit(0,1);
        $a->loadAny();
        $this->assertEquals('DELETE', $a->get('value'));
    }


    /*
     * test secondary audit is created on EPA things
     */
    public function testEPAAudit() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $a->save();
        $initial_audit_count = (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne();
        $e = $a->addEmail('tetete');
        $a->updateEmail($e->get('id'), 'jzjzjz');
        $a->deleteEmail($e->get('id'));

        $this->assertEquals($initial_audit_count + 3, (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne());

        //make sure ADD_EMAIL, CHANGE_EMAIL AND DELETE_EMAIL Audits are there
        $change_found = false;
        $create_found = false;
        $delete_found = false;
        foreach($a->getAuditViewModel() as $audit) {
            if($audit->get('value') == 'ADD_EMAIL') {
                $create_found = true;
            }
            if($audit->get('value') == 'CHANGE_EMAIL') {
                $change_found = true;
            }
            if($audit->get('value') == 'REMOVE_EMAIL') {
                $delete_found = true;
            }
        }
        $this->assertTrue($change_found);
        $this->assertTrue($create_found);
        $this->assertTrue($delete_found);
    }

}
