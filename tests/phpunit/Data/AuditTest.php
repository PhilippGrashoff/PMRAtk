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
        $a->set('dd_test', 1);
        $a->save();
        $this->assertEquals(3, $a->getAuditViewModel()->action('count')->getOne());
        $a->set('time', '10:00');
        $a->set('date', '2019-05-05');
        $a->set('dd_test', 2);
        $a->set('dd_test_2', 'bla');
        $a->save();
        $this->assertEquals(4, $a->getAuditViewModel()->action('count')->getOne());
        $a->addAdditionalAudit('SOMETYPE', []);
        $this->assertEquals(5, $a->getAuditViewModel()->action('count')->getOne());

        //hasOne Audit field all possibilities
        $b1 = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b2 = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b1->save();
        $b2->save();

        $a->set('BaseModelB_id', '1111');
        $a->save();
        $a->set('BaseModelB_id', $b1->get('id'));
        $a->save();
        $a->set('BaseModelB_id', $b2->get('id'));
        $a->save();


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


    /*
     * test MToM audit
     */
    public function testMToMAudit() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        //MToM Adding should create an audit
        $initial_audit_count = (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne();
        $this->assertTrue($this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($initial_audit_count + 2, (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne());

        //MToM Removal too
        $this->assertTrue($this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($initial_audit_count + 4, (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne());

        //make sure ADD_BASEMODELB, AND REMOVE_BASEMODELB Audits are there
        $create_found = false;
        $delete_found = false;
        foreach($a->getAuditViewModel() as $audit) {
            if($audit->get('value') == 'ADD_BASEMODELB') {
                $create_found = true;
            }
            if($audit->get('value') == 'REMOVE_BASEMODELB') {
                $delete_found = true;
            }
        }
        $this->assertTrue($create_found);
        $this->assertTrue($delete_found);
    }


    /*
     * test if Audit is not created
     */
    public function testNoAuditCreatedOnSetting() {
        $_ENV['CREATE_AUDIT'] = false;
        $initial_audit_count = (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne();

        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $a->save();
        $a->addAdditionalAudit('bla', []);
        $e = $a->addEmail('lala');
        $a->updateEmail($e->id, 'fdgdfgdf');
        $a->deleteEmail($e->id);

        $this->assertEquals($initial_audit_count, (new \PMRAtk\Data\Audit(self::$app->db))->action('count')->getOne());
        $_ENV['CREATE_AUDIT'] = true;
    }
}
