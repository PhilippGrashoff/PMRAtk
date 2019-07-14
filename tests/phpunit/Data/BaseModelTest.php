<?php

class MayNotLoad extends \PMRAtk\Data\User {
    protected function _userHasReadRight():bool {
        return false;
    }
}

class MayNotCUD extends \PMRAtk\Data\User {
    protected function _userHasCreateRight():bool {
        return false;
    }
    protected function _userHasUpdateRight():bool {
        return false;
    }
    protected function _userHasDeleteRight():bool {
        return false;
    }
}


class BaseModelTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * Tests model copying
     * group is the perfect class to use to test: has Email, Phone, Address
     * has all fields that should be excluded by copy
     * has 2 MtoM refs that should not be copied
     */
    public function testCreateCopyFromOtherRecord() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $a->set('name', 'Test');
        $a->save();
        //add some EPA to see if theyre also copied
        $a->addEmail('abc');
        $a->addEmail('def');
        $a->addPhone('1234');
        $a->addAddress('123456');

        //sleep so created_date isnt equal
        sleep(1);

        $b = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b->createCopyFromOtherRecord($a->get('id'));
        $b->save();

        //test if all fields except the non-copy fields have the same value
        foreach($b->data as $field_name => $value) {
            //copy all field values that make sense
            if($field_name !== 'id'
            && $field_name !== 'created_date'
            //name should get " (Kopie)" after value, so not equal
            && $field_name !== 'name'
            && $a->hasElement($field_name)) {
                $this->assertTrue($b->get($field_name) == $a->get($field_name));
            }
        }

        $this->assertEquals('Test (Kopie)', $b->get('name'));

        //all fields that should not be copied by this function should differ,
        //except created_by, this could be the same
        $this->assertNotEquals($b->get('id'), $a->get('id'));
        $this->assertNotEquals($b->get('created_date'), $a->get('created_date'));


        //Email(s), Phone(s) and (Addresses)should be copied
        $this->assertTrue($b->ref('Email')->action('count')->getOne() == $a->ref('Email')->action('count')->getOne());
        $this->assertTrue($b->ref('Phone')->action('count')->getOne() == $a->ref('Phone')->action('count')->getOne());
        $this->assertTrue($b->ref('Address')->action('count')->getOne() == $a->ref('Address')->action('count')->getOne());

        //MtoM Tours and Guests shouldnt be copied
        $this->assertTrue($b->ref('MToMModel')->action('count')->getOne() == '0');
        $this->assertTrue($b->ref('MToMModel')->action('count')->getOne() == '0');
    }


    /*
     * see if exception is thrown when non-loaded record should be copied fro,
     */
    public function testcreateCopyNonLoadedRecordException() {
        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $a->createCopyFromOtherRecord($b);
    }



    /*
     * see if load throws exception if user isnt allowed to load
     */
    public function testUserLoadRightException() {
        $mna = new MayNotLoad(self::$app->db);
        $this->expectException(\PMRAtk\Data\UserException::class);
        $mna->load(1);
    }


    /*
     * see if create throws exception if user isnt allowed to create
     */
    public function testUserCreateRightException() {
        $mna = new MayNotCUD(self::$app->db);
        $mna->set('name', 'A');
        $mna->set('username', 'B');
        $this->expectException(\PMRAtk\Data\UserException::class);
        $mna->save();
    }


    /*
     * see if update throws exception if user isnt allowed to update
     */
    public function testUserUpdateRightException() {
        $u = new \PMRAtk\Data\User(self::$app->db);
        $u->set('name', 'A');
        $u->set('username', 'B');
        $u->save();

        $mna = new MayNotCUD(self::$app->db);
        $mna->load($u->get('id'));
        //change something otherwise save does nothing
        $mna->set('name', 'FR');
        $this->expectException(\PMRAtk\Data\UserException::class);
        $mna->save();
    }


    /*
     * see if delete throws exception if user isnt allowed to deete
     */
    public function testUserDeleteRightException() {
        $u = new \PMRAtk\Data\User(self::$app->db);
        $u->set('name', 'A');
        $u->set('username', 'B');
        $u->save();

        $mna = new MayNotCUD(self::$app->db);
        $mna->load($u->get('id'));
        $this->expectException(\PMRAtk\Data\UserException::class);
        $mna->delete();
    }


    /*
     * see if maySave = true overwrites other rules
     */
    public function testMaySaveCreate() {
        $mna = new MayNotCUD(self::$app->db);
        $mna->set('name', 'A');
        $mna->set('username', 'B');
        $mna->maySave = true;
        $mna->save();
        //some assertion to stop PHPUnit complaining
        $this->assertTrue(true);
    }


    /*
     * see if update throws exception if user isnt allowed to update
     */
    public function testMaySaveUpdate() {
        $u = new \PMRAtk\Data\User(self::$app->db);
        $u->set('name', 'A');
        $u->set('username', 'B');
        $u->save();

        $mna = new MayNotCUD(self::$app->db);
        $mna->load($u->get('id'));
        //change something otherwise save does nothing
        $mna->set('name', 'FR');
        $mna->maySave = true;
        $mna->save();
        //some assertion to stop PHPUnit complaining
        $this->assertTrue(true);
    }


    /*
     * see if delete throws exception if user isnt allowed to deete
     */
    public function testMaySaveDelete() {
        $u = new \PMRAtk\Data\User(self::$app->db);
        $u->set('name', 'A');
        $u->set('username', 'B');
        $u->save();

        $mna = new MayNotCUD(self::$app->db);
        $mna->load($u->get('id'));
        $mna->maySave = true;
        $mna->delete();
        //some assertion to stop PHPUnit complaining
        $this->assertTrue(true);
    }


    /*
     * see if a unrecognized param to userHasRight returns false
     */
     public function testuserHasRightFalseAsDefault() {
        $u = new \PMRAtk\Data\User(self::$app->db);
        $this->assertFalse($u->userHasRight('Duggu'));
    }


    /*
     * delete some record so _userHasDeleteRight() is called
     */
    public function testuserHasDeleteRightCalledOnDelete() {
        $u = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $u->set('name', 'A');
        $u->save();
        $u->delete();
        $this->assertTrue(true);
    }

    /*
     *
     */
    public function teststandardUserRightNoLoggedInUser() {
        $u = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $u->set('name', 'A');
        $initial = self::$app->auth->user;
        self::$app->auth->user = null;
        $res = $this->callProtected($u, '_standardUserRights');
        self::$app->auth->user = $initial;
        $this->assertFalse($res);
    }


    /*
     *
     */
    public function test_exceptionIfThisNotLoaded() {
        $u = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $u->save();
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
        $u->unload();
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
    }


    /*
     *
     */
    public function testloadedHasOneRef() {
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->save();
        $u = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $u->set('BaseModelB_id', $b->id);
        $u->save();
        $ref = $u->loadedHasOneRef('BaseModelB_id');
        $this->assertEquals($b->id, $ref->id);
        $b->delete();
        $this->expectException(\atk4\data\Exception::class);
        $ref = $u->loadedHasOneRef('BaseModelB_id');
    }


    /*
     *
     */
    public function testloadedHasOneRefFieldEmpty() {
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->save();
        $u = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $u->save();
        $this->expectException(\atk4\data\Exception::class);
        $ref = $u->loadedHasOneRef('BaseModelB_id');
    }
}
