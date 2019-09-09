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
        $s = new \PMRAtk\Data\Setting(self::$app->db);
        $s->set('ident', 'STD_DADAPRA');
        $s->set('value', 'LALA');
        self::$app->addSetting($s);
        $t->loadTemplateFromString('Hallo {$STD_DADAPRA} Test');
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
    public function testSetTagsFromModelWithNonExistingTagAndField() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$nottext}!');
        $t->setTagsFromModel($model, ['name', 'value', 'text', 'nilla']);
        $this->assertEquals('Hallo BlaDU Test 3 Miau !', $t->render());
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
        $this->assertEquals('Hallo 05.05.2019 10:30 Test 05.05.2019 Miau 10:30!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithLimitedFields() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, ['name', 'value']);
        $this->assertEquals('Hallo BlaDU Test 3 Miau !', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithEmptyFieldArray() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, []);
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithPrefix() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$group_name} Test {$group_value} Miau {$group_text}!');
        $t->setTagsFromModel($model, [], 'group_');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithTwoModelsWithPrefix() {
        $model = new TestModel(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $model2 = new TestModel(self::$app->db);
        $model2->set('name', 'ABC');
        $model2->set('value', 9);
        $model2->set('text', 'DEF');

        $t = new \PMRAtk\View\Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$group_name} Test {$group_value} Miau {$group_text}, du {$tour_name} Hans {$tour_value} bist toll {$tour_text}!');
        $t->setTagsFromModel($model, [], 'group_');
        $t->setTagsFromModel($model2, [], 'tour_');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA, du ABC Hans 9 bist toll DEF!', $t->render());
    }
}
