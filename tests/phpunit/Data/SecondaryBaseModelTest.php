<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\Exception;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\SecondaryBaseModelA;


/**
 * Class SecondaryBaseModelTest
 * @package PMRAtk\tests\phpunit\Data
 */
class SecondaryBaseModelTest extends TestCase {

    /**
     * @throws Exception
     */
    public function testGetParentObject() {
        $baseModelA = new BaseModelA(self::$app->db);
        $baseModelA->save();
        $secondaryBaseModelA = new SecondaryBaseModelA(self::$app->db);
        //no model_class set
        $this->assertEquals(null, $secondaryBaseModelA->getParentObject());

        //model_class, but no id
        $secondaryBaseModelA->set('model_class', BaseModelA::class);
        $this->assertEquals(null, $secondaryBaseModelA->getParentObject());

        //Record with valid id
        $secondaryBaseModelA->set('model_id', $baseModelA->get('id'));
        $parentObject = $secondaryBaseModelA->getParentObject();
        $this->assertTrue($parentObject instanceOf BaseModelA);
        $this->assertTrue($parentObject->loaded());
    }


    /**
     *
     */
    public function testGetParentObjectExceptionInvalidModelClass() {
        $baseModelA = new BaseModelA(self::$app->db);
        $baseModelA->save();
        $secondaryBaseModelA = new SecondaryBaseModelA(self::$app->db);
        $secondaryBaseModelA->set('model_class', 'Duggu');
        $secondaryBaseModelA->set('model_id', $baseModelA->get('id'));
        $this->expectException(Exception::class);
        $secondaryBaseModelA->getParentObject();
    }


    /**
     *
     */
    public function testGetParentObjectExceptionInvalidID() {
        $secondaryBaseModelA = new SecondaryBaseModelA(self::$app->db);
        $secondaryBaseModelA->set('model_class', BaseModelA::class);
        $secondaryBaseModelA->set('model_id', 333);
        $this->expectException(Exception::class);
        $secondaryBaseModelA->getParentObject();
    }


    /**
     *
     */
    public function testSetParentObjectDataDuringInit() {
        $bm = new BaseModelA(self::$app->db);
        $bm->save();
        $e = new SecondaryBaseModelA(self::$app->db, ['parentObject' => $bm]);
        $g = $e->getParentObject();
        $this->assertTrue($g instanceOf BaseModelA);
    }


    /**
     * test if value gets trimmed when saving
     */
    public function testTrimValueOnSave() {
        $e = new SecondaryBaseModelA(self::$app->db);
        $e->set('value', '  whitespace@beforeandafter.com  ');
        $e->save();
        $this->assertSame(
            $e->get('value'),
            'whitespace@beforeandafter.com'
        );
    }
}
