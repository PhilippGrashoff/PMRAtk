<?php


class MToMTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * Tests the MToM adding functionality
     */
    public function testMToMAdding() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne();
        $this->assertTrue($this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($mtom_count + 1, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());

        //adding again shouldnt create a new record
        $this->assertFalse($this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($mtom_count + 1, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());
    }


    /*
     * see if $this not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionThisNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionObjectNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * test adding by id
     */
    public function testMToMAddingById() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne();
        $this->assertTrue($this->callProtected($a, '_addMToMRelation', [$b->get('id'), new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($mtom_count + 1, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());
    }


    /*
     * test adding by invalid id
     */
    public function testMToMAddingByInvalidId() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [11111, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * Tests the MToM removal functionality
     */
    public function testMToMRemoval() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne();
        $this->assertTrue($this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertEquals($mtom_count + 1, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());

        $this->assertTrue($this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        //should be removed
        $this->assertEquals($mtom_count, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        $this->expectException(\atk4\data\Exception::class);
        $this->assertFalse($this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
    }


    /*
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionThisNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionObjectNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * test hasMToM
     */
    public function testHasMToMReference() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);

        $this->assertTrue($this->callProtected($a, '_hasMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertTrue($this->callProtected($b, '_hasMToMRelation', [$a, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelA', 'BaseModelB_id', 'BaseModelA_id']));

        $this->callProtected($a, '_removeMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertFalse($this->callProtected($a, '_hasMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertFalse($this->callProtected($b, '_hasMToMRelation', [$a, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelA', 'BaseModelB_id', 'BaseModelA_id']));
    }


    /*
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMHasThrowExceptionThisNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMHasThrowExceptionObjectNotLoaded() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMAdding
     */
    public function testMToMAddingWrongClassException() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$a, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMRemoval
     */
    public function testMToMRemovalWrongClassException() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$a, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in HasMToM
     */
    public function testMToMHasWrongClassException() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$a, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     *
     */
    public function testAddAdditionalFields() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne();
        $this->assertTrue($this->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id', ['test1' => 'LALA']]));
        $this->assertEquals($mtom_count + 1, (new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db))->action('count')->getOne());

        $mtommodel = new \PMRAtk\tests\phpunit\Data\MToMModel(self::$app->db);
        $mtommodel->setOrder('id desc');
        $mtommodel->setLimit(0,1);
        foreach($mtommodel as $m) {
            $this->assertEquals($m->get('test1'), 'LALA');
            echo "LALA";
        }
    }
}