<?php

class EPARelationsTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * Tests adding of new Email, Phone, Address, altering it and then deleting it
     */
    public function testCreateUpdateDeleteEPA() {
        $this->_createUpdateDeleteEPA('PMRAtk\tests\phpunit\Data\BaseModelA', 'Email', 'test@easyoutdooroffice.com');
        $this->_createUpdateDeleteEPA('PMRAtk\tests\phpunit\Data\BaseModelA', 'Phone', '0176 21544664');
        $this->_createUpdateDeleteEPA('PMRAtk\tests\phpunit\Data\BaseModelA', 'Address', 'Teststraße 11 12345 Testhausen');
    }


    /*
     * tests if _getFirstEPA throws exception when called with invalid param
     */
    public function testGetFirstEPAException() {
        //pass some inexisting reference
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($g, '_getFirstEPA', ['Duggu']);
    }


    /*
     * tests if _getEPAById throws exception if ref does not exist in model
     */
    public function testGetEPAByIdException() {
        //pass some inexisting reference
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($g, '_getEPAById', ['Duggu', 1]);
    }


    /*
     * tests if createEPA throws exception if ref does not exist in model
     */
    public function testCreateEPAException() {
        //pass some inexisting reference
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $g->createEPA('Duggu', 'luggu');
    }


    /*
     * createEPA should return null when value is empty
     */
    public function testCreateEPAWithEmptyValue() {
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->assertEquals(null, $g->createEPA('Email', ''));
    }


    /*
     * tests if updateEPA throws exception if ref does not exist in model
     */
    public function testUpdateEPAException() {
        //pass some inexisting reference
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $g->updateEPA('Duggu', 1, 'luggu');
    }


    /*
     * tests if deleteEPA throws exception if ref does not exist in model
     */
    public function testDeleteEPAException() {
        //pass some inexisting reference
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->expectException(\atk4\data\Exception::class);
        $g->deleteEPA('Duggu', 'luggu');
    }


    /*
     * Helper fuction for testCreateUpdateDeleteEPA
     */
    protected function _createUpdateDeleteEPA($model, $type, $text) {
        $m_classname = $model;
        $m = new $m_classname(self::$app->db);
        $m->save();
        //create 2 new EPAs of this type to test delete does not delete all
        $m->createEPA($type, 'a');
        $m->createEPA($type, 'b');

        //store initial count of referenced EPAs
        $initial_count = $m->ref($type)->action('count')->getOne();
        //now create new EPA
        $epa_id = $m->createEPA($type, $text)->get('id');
        //now a new referenced EPA should be there
        $this->assertTrue(($initial_count + 1) == $m->ref($type)->action('count')->getOne());
        //update with same text should return true
        $this->assertTrue($m->updateEPA($type, $epa_id, $text));
        //update with different text should return true
        $this->assertTrue($m->updateEPA($type, $epa_id, $text.'duggu'));
        //make sure value was updated
        $classname = '\PMRAtk\\Data\\'.$type;
        $epa = new $classname(self::$app->db);
        $epa->load($epa_id);
        $this->assertEquals($epa->get('value'), $text.'duggu');
        //now delete
        $this->assertTrue($m->deleteEPA($type, $epa_id));
        //number of references should equal start value now
        $this->assertTrue($initial_count == $m->ref($type)->action('count')->getOne());
    }


    /*
     * tests convenience functions for EPA
     */
    public function testEPAConvenienceFunctions() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->save();

        $id = $m->addEmail('Lala')->get('id');
        $this->assertEquals(1, $m->ref('Email')->action('count')->getOne());
        $m->updateEmail($id, 'Duggu');
        $this->assertEquals('Duggu', $m->getFirstEmail());
        $m->deleteEmail($id);
        $this->assertEquals(0, $m->ref('Email')->action('count')->getOne());

        $id = $m->addPhone('Lala')->get('id');
        $this->assertEquals(1, $m->ref('Phone')->action('count')->getOne());
        $m->updatePhone($id, 'Duggu');
        $this->assertEquals('Duggu', $m->getFirstPhone());
        $m->deletePhone($id);
        $this->assertEquals(0, $m->ref('Phone')->action('count')->getOne());

        $id = $m->addAddress('Lala')->get('id');
        $this->assertEquals(1, $m->ref('Address')->action('count')->getOne());
        $m->updateAddress($id, 'Duggu');
        $this->assertEquals('Duggu', $m->getFirstAddress());
        $m->deleteAddress($id);
        $this->assertEquals(0, $m->ref('Address')->action('count')->getOne());
    }


    /*
     * tests if EPA creation works also if model has no id yet
     */
    public function testCreateEPAHook() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->addEmail('Duggu1');
        $m->addPhone('Duggu2');
        $m->addAddress('Duggu3');
        $m->save();

        $this->assertEquals(1, $m->ref('Email')->action('count')->getOne());
        $this->assertEquals(1, $m->ref('Phone')->action('count')->getOne());
        $this->assertEquals(1, $m->ref('Address')->action('count')->getOne());

        $this->assertEquals('Duggu1', $m->getFirstEmail());
        $this->assertEquals('Duggu2', $m->getFirstPhone());
        $this->assertEquals('Duggu3', $m->getFirstAddress());
    }


    /*
     * Tests EPA functions with false parameters
     */
    public function testEPAParameters() {
        $this->_EPAParameters('\PMRAtk\tests\phpunit\Data\BaseModelA', 'Email');
        $this->_EPAParameters('\PMRAtk\tests\phpunit\Data\BaseModelA', 'Phone');
        $this->_EPAParameters('\PMRAtk\tests\phpunit\Data\BaseModelA', 'Address');
    }


    /*
     * Helper function for testEPAParameters
     */
    protected function _EPAParameters($model, $type) {
        $m_classname = $model;
        $m = new $m_classname(self::$app->db);
        $m->save();

        //passing invalid ids to updateEPA and deleteEPA should cause false
        $this->assertFalse($m->updateEPA($type, 1111, 'someValue'));
        $this->assertFalse($m->deleteEPA($type, 1111));
    }


    /*
     * tests if loadEmailByID etc work as expected
     */
    public function testloadEPAById() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->save();

        $m->addEmail('Lala')->get('id');
        $id_new = $m->addEmail('Duggu')->get('id');
        $this->assertEquals($m->getEmailById($id_new), 'Duggu');

        $m->addPhone('Lala')->get('id');
        $id_new = $m->addPhone('Duggu')->get('id');
        $this->assertEquals($m->getPhoneById($id_new), 'Duggu');

        $m->addAddress('Lala')->get('id');
        $id_new = $m->addAddress('Duggu')->get('id');
        $this->assertEquals($m->getAddressById($id_new), 'Duggu');
    }


    /*
     * tests getFirstPhone, getFirstAddres and getFirstEmail functions
     */
    public function testGetFirstEPA() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->save();

        $m->addEmail('hansi@easyoutdooroffice.com');
        $m->addEmail('gfgdgdgfd');
        $this->assertEquals($m->getFirstEmail(), 'hansi@easyoutdooroffice.com');

        $m->addPhone('0176 21544664');
        $m->addPhone('gfgdgdgfd');
        $this->assertEquals($m->getFirstPhone(), '0176 21544664');

        $m->addAddress('Teststraße 12 12345 Testhausen');
        $m->addAddress('gfgdgdgfd');
        $this->assertEquals($m->getFirstAddress(), 'Teststraße 12 12345 Testhausen');
    }


    /*
     * tests if EPAs get deleted when parent object is deleted
     */
    public function testdeleteEPAsOnParentDelete() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->save();
        $initial_count = (new \PMRAtk\Data\Email(self::$app->db))->action('count')->getOne();
        $m->addEmail('hansi@easyoutdooroffice.com');
        $m->addEmail('gfgdgdgfd');

        $this->assertEquals($initial_count + 2, (new \PMRAtk\Data\Email(self::$app->db))->action('count')->getOne());

        $m->delete();

        $this->assertEquals($initial_count, (new \PMRAtk\Data\Email(self::$app->db))->action('count')->getOne());

    }


    /*
     * test if _getEpaById throws an exception if record wasnt found
     */
    public function testGetEPAByIdExceptionIfRefNotFound() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $m->save();
        $this->expectException(\atk4\data\Exception::class);
        $m->getEmailById(111);
    }
}