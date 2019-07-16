<?php

class FCMTraitTest extends \PMRAtk\tests\phpunit\Data\BaseModelA {
    use \PMRAtk\Data\Traits\FieldChangedMessageTrait;
    use \PMRAtk\Data\Traits\DateTimeHelpersTrait;
}

class FCMTraitTestNoDTH extends \PMRAtk\tests\phpunit\Data\BaseModelA {
    use \PMRAtk\Data\Traits\FieldChangedMessageTrait;
}

class FCMTApp extends \PMRAtk\tests\phpunit\TestApp {
    use \PMRAtk\View\Traits\UserMessageTrait;
}

class FieldChangedMessageTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    public $testApp;

    /*
     * empty userMessages before each test
     */
    public function setUp():void {
        parent::setUp();
        $this->testApp = new \FCMTApp(['admin']);
    }


    /*
     *
     */
    public function testAddMessage() {
        $m = new FCMTraitTest($this->testApp->db);
        $m->addFieldChangedMessage('name', 'hansi', 'peter');
        $this->assertEquals(1, count($this->testApp->userMessages));
    }


    /*
     *
     */
    public function testAddMessageHasOne() {
        //create 2 records of BaseModelB
        $b1 = new \PMRAtk\tests\phpunit\Data\BaseModelB($this->testApp->db);
        $b1->set('name', 'Lala');
        $b1->save();
        $b2 = new \PMRAtk\tests\phpunit\Data\BaseModelB($this->testApp->db);
        $b2->set('name', 'Happa');
        $b2->save();

        $m = new FCMTraitTest($this->testApp->db);
        $m->addFieldChangedMessage('BaseModelB_id', $b1->id, $b2->id);
        $this->assertEquals(1, count($this->testApp->userMessages));
        //Title field should be used instead of ids
        $this->assertTrue(strpos($this->testApp->userMessages[0]['message'], 'Lala') !== false);
        $this->assertTrue(strpos($this->testApp->userMessages[0]['message'], 'Happa') !== false);
    }


    /*
     *
     */
    public function testDateFields() {
        $m = new FCMTraitTest($this->testApp->db);
        $m->addFieldChangedMessage('date', new \DateTime(),  (new \DateTime())->modify('+ 1 Day'));
        $m->addFieldChangedMessage('time', new \DateTime(),  (new \DateTime())->modify('+ 1 Hour'));

        $this->assertEquals(2, count($this->testApp->userMessages));
    }


    /*
     *
     */
    public function testNoValueChangeNoMessage() {
        $m = new FCMTraitTest($this->testApp->db);
        $m->addFieldChangedMessage('name', 'ha', 'ha');

        $this->assertEquals(0, count($this->testApp->userMessages));
    }


    /*
     *
     */
    public function testExceptionOnDateTimeHelpersTraitNotUsed() {
        $m = new FCMTraitTestNoDTH($this->testApp->db);
        $this->expectException(\atk4\data\Exception::class);
        $m->addFieldChangedMessage('name', 'ha', 'ha');
    }
}
