<?php

namespace PMRAtk\tests\phpunit\View;

use atk4\data\Model;
use PMRAtk\tests\phpunit\Data\BaseModelA;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\Template;

class TemplateTest extends TestCase {

    /*
     *
     */
    public function testSTDValues() {
        $t = new Template();
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
        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$DADA} Test');
        $t->setGermanList('DADA', ['Hansi', '', 'Peter', 'Klaus']);
        $this->assertTrue(strpos($t->render(), 'Hansi, Peter und Klaus') !== false);
    }


    /**
     *
     */
    protected function getTestModel(): Model {
        $class = new class extends Model {
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
        };

        return new $class(self::$app->db);
    }


    /*
     *
     */
    public function testSetTagsFromModel() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, ['name', 'value', 'text'], '');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }

    /*
     *
     */
    public function testSetTagsFromModelWithNonExistingTagAndField() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$nottext}!');
        $t->setTagsFromModel($model, ['name', 'value', 'text', 'nilla'], '');
        $this->assertEquals('Hallo BlaDU Test 3 Miau !', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithDates() {
        $model = $this->getTestModel();
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-05-05 10:30:00');
        $model->set('datetime', clone $dt);
        $model->set('date',     clone $dt);
        $model->set('time',     clone $dt);

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$datetime} Test {$date} Miau {$time}!');
        $t->setTagsFromModel($model, ['datetime', 'date', 'time'], '');
        $this->assertEquals('Hallo 05.05.2019 10:30 Test 05.05.2019 Miau 10:30!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithLimitedFields() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, ['name', 'value'], '');
        $this->assertEquals('Hallo BlaDU Test 3 Miau !', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithEmptyFieldArray() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$name} Test {$value} Miau {$text}!');
        $t->setTagsFromModel($model, [], '');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithPrefix() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$group_name} Test {$group_value} Miau {$group_text}!');
        $t->setTagsFromModel($model, [], 'group_');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithOnlyOneParameter() {
        $model = new BaseModelA(self::$app->db);
        $model->set('name', 'BlaDU');
        $model->set('firstname', 'GuGuGu');
        $model->set('lastname', 'LALALALA');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$basemodela_name} Test {$basemodela_firstname} Miau {$basemodela_lastname}!');
        $t->setTagsFromModel($model);
        $this->assertEquals('Hallo BlaDU Test GuGuGu Miau LALALALA!', $t->render());
    }


    /*
     *
     */
    public function testSetTagsFromModelWithTwoModelsWithPrefix() {
        $model = $this->getTestModel();
        $model->set('name', 'BlaDU');
        $model->set('value', 3);
        $model->set('text', 'LALALALA');

        $model2 = $this->getTestModel();
        $model2->set('name', 'ABC');
        $model2->set('value', 9);
        $model2->set('text', 'DEF');

        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$group_name} Test {$group_value} Miau {$group_text}, du {$tour_name} Hans {$tour_value} bist toll {$tour_text}!');
        $t->setTagsFromModel($model, [], 'group_');
        $t->setTagsFromModel($model2, [], 'tour_');
        $this->assertEquals('Hallo BlaDU Test 3 Miau LALALALA, du ABC Hans 9 bist toll DEF!', $t->render());
    }


    /**
     *
     */
    public function testWithLineBreaks() {
        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {$with_line_break} Test');
        $t->setWithLineBreaks('with_line_break', 'Hans'.PHP_EOL.'Neu');
        $ex = 'Hallo Hans<br />'.PHP_EOL.'Neu Test';
        self::assertEquals($ex, $t->render());
    }


    /**
     *
     */
    public function testReplaceHTML() {
        $t = new Template();
        $t->app = self::$app;
        $t->loadTemplateFromString('Hallo {SomeRegion}{/SomeRegion} Test');

        $t->appendHTML('SomeRegion', '<div>Buzz</div>');
        self::assertSame(
            'Hallo <div>Buzz</div> Test',
            $t->render()
        );

        $t->replaceHTML('SomeRegion', '<span>Wizz</span>');
        self::assertSame(
          'Hallo <span>Wizz</span> Test',
          $t->render()
        );
    }
}
