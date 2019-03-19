<?php

class TestViewWithForm extends \atk4\ui\View {
    use \PMRAtk\View\Traits\FormHelpersTrait;
}


class FormHelpersTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testFieldIdsAndSubmitButtonId() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewWithForm());
        $f = $v->add('Form');
        $f->id = 'testForm';
        $field1 = $f->addField('test1');
        $field2 = $f->addField('test2');
        $this->assertNotEquals('test1', $field1->id);
        $v->setHTMLIds($f);
        $this->assertEquals('test1', $field1->id);
        $this->assertEquals('testForm_submit', $f->buttonSave->id);
    }
}
