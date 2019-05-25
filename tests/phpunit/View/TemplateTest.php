<?php

class TestModel extends \atk4\data\Model {
    public $table = 'blalba';

    public function init() {
        parent::init();
        $this->addFields([
            ['name',     'type' => 'string'],
            ['value',    'type' => 'integer'],
            ['text',     'type' => 'text'],
            ['datetime', 'type' => 'datetime'],
            ['date',     'type' => 'date'],
            ['time',     'type' => 'time'],
        ]);
    }
}


class TemplateTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testSTDValues() {
        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        if(!defined('STD_SET_ARRAY')) {
            define('STD_SET_ARRAY', ['DADA' => 'LALA']);
        }
        $t->loadTemplateFromString('Hallo {$DADA} Test');
        $t->setSTDValues();
        $this->assertTrue(strpos($t->render(), 'LALA') !== false);
    }


    /*
     * test if exception is thrown if no array with user roles which may
     * see this page is passed
     */
    public function testSetGermanList() {
        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$DADA} Test');
        $t->setGermanList('DADA', ['Hansi', '', 'Peter', 'Klaus']);
        $this->assertTrue(strpos($t->render(), 'Hansi, Peter und Klaus') !== false);
    }


    /*
     *
     */
    public function testSetTagsFromModel() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, ['name', 'value', 'text']);
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithDates() {
        $model = new TestModel(self::$app->db);
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-05-05 10:30:00');
        $model->set('datetime', clone $dt);
        $model->set('date',     clone $dt);
        $model->set('time',     clone $dt);

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$datetime} Test {$date} Miau {$time}!');
        $t->setTagsFromModel($model, ['datetime', 'date', 'time']);
        $this->assertEquals('Hallo 05.05.2019 10:30:00 Test 05.05.2019 Miau 10:30:00!', $t->render());
    }
}
