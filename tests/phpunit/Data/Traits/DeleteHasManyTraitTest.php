<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\tests\phpunit\Data\BaseModelA;
use PMRAtk\tests\phpunit\Data\BaseModelB;
use PMRAtk\tests\phpunit\Data\MToMModel;
use PMRAtk\tests\phpunit\TestCase;

/**
 * Class DeleteHasManyTraitTest
 */
class DeleteHasManyTraitTest extends TestCase {

    public function testDeleteHasManyObjects() {
        //save initial DB table count of ref
        $initial_count = (new MToMModel(self::$app->db))->action('count')->getOne();
        $a = new BaseModelA(self::$app->db);
        $a->save();
        $b = new BaseModelB(self::$app->db);
        $b->save();
        $c = new BaseModelB(self::$app->db);
        $c->save();

        $this->callProtected($a, '_addMToMRelation', [$b, new MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
        $this->callProtected($a, '_addMToMRelation', [$c, new MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);


        //now GroupToTour table should have 2 entries for that group
        $this->assertEquals(2, $a->ref('MToMModel')->action('count')->getOne());

        //now delete refs
        $this->assertTrue($a->deleteHasMany('MToMModel'));
        $this->assertEquals(0, $a->ref('MToMModel')->action('count')->getOne());
    }


    /*
     * if a non-loaded object is using deleteMToMRefObjects, exception should be thrown
     */
    public function testDeleteRefObjectsThisNotLoadedException() {
        $a = new BaseModelA(self::$app->db);
        $initial_count = (new MToMModel(self::$app->db))->action('count')->getOne();
        $this->expectException(\atk4\data\Exception::class);
        $a->deleteHasMany('MToMModel');
    }


    /*
     * exception should be thrown if non-existing reference is passed
     */
    public function testDeleteRefObjectsNonExistingRefException() {
        $a = new BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $a->deleteHasMany('FHAFDF');
    }


    /*
     * test if passing hasOne ref does throw exception
     */
    public function testDeleteRefObjectsHasOneThrowsException() {
        $a = new BaseModelA(self::$app->db);
        $a->save();
        $this->expectException(\atk4\data\Exception::class);
        $a->deleteHasMany('BaseModelB_id');
    }
}