<?php

namespace PMRAtk\tests\phpunit\Data;

class SecondaryBaseModelTest extends \PMRAtk\tests\phpunit\TestCase {

    public function testGetParentObject() {
        $bm = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $bm->save();
        $e = new \PMRAtk\tests\phpunit\Data\SecondaryBaseModelA(self::$app->db);
        //no model_class set
        $this->assertEquals(null, $e->getParentObject());

        //model_class, but no id
        $e->set('model_class', '\PMRAtk\tests\phpunit\Data\BaseModelA');
        $g = $e->getParentObject();
        $this->assertEquals(null, $e->getParentObject());

        //Record with invalid id
        $e->set('model_id', 333);
        $g = $e->getParentObject();
        $this->assertTrue($g instanceOf \PMRAtk\tests\phpunit\Data\BaseModelA);
        $this->assertFalse($g->loaded());

        //Record with valid id
        $e->set('model_class', '\PMRAtk\tests\phpunit\Data\BaseModelA');
        $e->set('model_id', $bm->get('id'));
        $g = $e->getParentObject();
        $this->assertTrue($g instanceOf \PMRAtk\tests\phpunit\Data\BaseModelA);
        $this->assertTrue($g->loaded());

        //wrong model_class set, should throw exception
        $e->set('model_class', 'Duggu');
        $this->expectException(\atk4\data\Exception::class);
        $e->getParentObject();
    }


    /*
     *
     */
    public function testSetAndGetParentObject() {
        $bm = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $bm->save();
        $e = new \PMRAtk\tests\phpunit\Data\SecondaryBaseModelA(self::$app->db, ['parentObject' => $bm]);
        $g = $e->getParentObject();
        $this->assertTrue($g instanceOf \PMRAtk\tests\phpunit\Data\BaseModelA);
    }


    /*
     * test if value gets trimmed when saving
     */
    public function testTrimValueOnSave() {
        $e = new \PMRAtk\tests\phpunit\Data\SecondaryBaseModelA(self::$app->db);
        $e->set('value', '  whitespace@beforeandafter.com  ');
        $e->save();
        $this->assertTrue($e->get('value') === 'whitespace@beforeandafter.com');
    }
}
