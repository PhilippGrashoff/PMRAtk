<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;

use PMRAtk\Data\Audit;
use PMRAtk\Data\Email;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\phpunit\TestCase;


/**
 * Class AuditTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class AuditTraitTest extends TestCase
{

    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $_ENV['CREATE_AUDIT'] = true;
    }


    /**
     *
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $_ENV['CREATE_AUDIT'] = false;
    }


    /**
     *
     */
    public function testAuditCreatedForFields()
    {
        $a = new BaseModelA(self::$app->db);
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
        $b1 = new BaseModelB(self::$app->db);
        $b2 = new BaseModelB(self::$app->db);
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
        foreach ($a->getAuditViewModel() as $audit) {
            if ($audit->get('value') == 'CREATE') {
                $create_found = true;
            }
            if ($audit->get('value') == 'CHANGE') {
                $change_found = true;
            }
        }
        $this->assertTrue($change_found);
        $this->assertTrue($create_found);
    }


    /**
     * test create delete Audit
     */
    public function testDeleteAudit()
    {
        $a = new BaseModelA(self::$app->db);
        $a->save();
        $initial_audit_count = (new Audit(self::$app->db))->action('count')->getOne();
        $a->delete();
        $this->assertEquals($initial_audit_count + 1, (new Audit(self::$app->db))->action('count')->getOne());

        //make sure newest audit is of type delete
        $a = new Audit(self::$app->db);
        $a->setOrder('id DESC');
        $a->setLimit(0, 1);
        $a->loadAny();
        $this->assertEquals('DELETE', $a->get('value'));
    }


    /**
     * test secondary audit is created on EPA things
     */
    public function testEPAAudit()
    {
        $a = new BaseModelA(self::$app->db);
        $a->save();
        $initial_audit_count = (new Audit(self::$app->db))->action('count')->getOne();
        $e = $a->addEmail('tetete');
        $a->updateEmail($e->get('id'), 'jzjzjz');
        $a->deleteEmail($e->get('id'));

        $this->assertEquals($initial_audit_count + 3, (new Audit(self::$app->db))->action('count')->getOne());

        //make sure ADD_EMAIL, CHANGE_EMAIL AND DELETE_EMAIL Audits are there
        $change_found = false;
        $create_found = false;
        $delete_found = false;
        foreach ($a->getAuditViewModel() as $audit) {
            if ($audit->get('value') == 'ADD_EMAIL') {
                $create_found = true;
            }
            if ($audit->get('value') == 'CHANGE_EMAIL') {
                $change_found = true;
            }
            if ($audit->get('value') == 'REMOVE_EMAIL') {
                $delete_found = true;
            }
        }
        $this->assertTrue($change_found);
        $this->assertTrue($create_found);
        $this->assertTrue($delete_found);
    }


    /**
     * test if Audit is not created
     */
    public function testNoAuditCreatedOnSetting()
    {
        $_ENV['CREATE_AUDIT'] = false;
        $initial_audit_count = (new Audit(self::$app->db))->action('count')->getOne();

        $a = new BaseModelA(self::$app->db);
        $a->save();
        $a->addAdditionalAudit('bla', []);
        $e = $a->addEmail('lala');
        $a->updateEmail($e->id, 'fdgdfgdf');
        $a->deleteEmail($e->id);

        $this->assertEquals($initial_audit_count, (new Audit(self::$app->db))->action('count')->getOne());
        $_ENV['CREATE_AUDIT'] = true;
    }


    /**
     *
     */
    public function testMToMAudit()
    {
        $a = new BaseModelA(self::$app->db);
        $a->save();
        $initial_audit_count = (new Audit(self::$app->db))->action('count')->getOne();
        $a->addMToMAudit('ADD', new BaseModelB(self::$app->db));
        $this->assertEquals($initial_audit_count + 1, (new Audit(self::$app->db))->action('count')->getOne());
    }


    /**
     *
     */
    public function testNoAuditOnNoValueChange()
    {
        $a = new BaseModelA(self::$app->db);
        $a->set('name', 'TEST');
        $a->set('dd_test', 1);
        $a->save();
        $a->set('name', 'TEST');
        $a->set('dd_test', 2);
        $a->save();

        $i = 0;
        foreach ($a->ref('Audit') as $audit) {
            $i++;
            //first audit should carry name change
            if ($i === 1) {
                self::assertTrue(isset($audit->get('data')['name']));
            } //second shouldnt
            elseif ($i === 2) {
                self::assertFalse(isset($audit->get('data')['name']));
            }
        }

        //make sure it was 2
        self::assertEquals(2, $i);
    }


    /**
     *
     */
    public function testNoAuditOnNoValueChangeStringsLooseCompare()
    {
        $a = new BaseModelA(self::$app->db);
        $a->set('name', '');
        $a->set('dd_test', 1);
        $a->save();
        $a->set('name', null);
        $a->set('dd_test', 2);
        $a->save();

        $i = 0;
        foreach ($a->ref('Audit') as $audit) {
            $i++;
            self::assertFalse(isset($audit->get('data')['name']));

        }

        //make sure it was 2
        self::assertEquals(2, $i);
    }


    /**
     * This case shouldnt happen, but that line makes sense. Test it here
     */
    public function testContinueIfDirtyValueEqualsNewValue() {
        $a = new BaseModelA(self::$app->db);
        $a->set('dd_test', 1);
        $a->dirty = ['dd_test' => 1];
        $a->set('name', 'SomeName');
        $a->save();
        self::assertEquals(1, $a->ref('Audit')->action('count')->getOne());
        $au = $a->ref('Audit')->loadAny();
        $data = $au->get('data');
        self::assertFalse(isset($data['dd_test']));
    }


    /**
     *
     */
    public function testFieldsValuePropertyIsCorrectlyAudited() {
        $withValues = new class extends BaseModelA {

            public function init(): void {
                parent::init();
                $this->getField('dd_test')->values = [0 => 'Nein', 1 => 'Ja'];
            }
        };

        $instance = new $withValues(self::$app->db);
        $instance->set('dd_test', 1);
        $instance->save();
        $instance->set('dd_test', 0);
        $instance->save();
        self::assertEquals(2, $instance->ref('Audit')->action('count')->getOne());
        foreach ($instance->ref('Audit') as $au) {
            $data = $au->get('data');
            self::assertTrue(isset($data['dd_test']));
        }
    }


    /**
     *
     */
    public function testAddSecondaryAudit() {
        $baseModelA = new BaseModelA(self::$app->db);
        $baseModelA->save();
        
        $baseModelB = new BaseModelB(self::$app->db);
        $baseModelB->save();
        
        $email = new Email(self::$app->db);
        $email->set('value', 'SOMEEMAIL');
        $email->save();

        //with 2 params, standard
        $baseModelA->addSecondaryAudit('ADD', $email);
        $baseModelA->addSecondaryAudit('CHANGE', $email, 'value', BaseModelB::class, $baseModelB->get('id'));

        self::assertEquals(2, $baseModelA->ref('Audit')->action('count')->getOne());
        self::assertEquals(2, $baseModelB->ref('Audit')->action('count')->getOne());
    }
}
