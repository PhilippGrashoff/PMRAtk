<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;


use atk4\ui\Form;
use atk4\ui\Layout\Admin;
use atk4\ui\View;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\App\App;
use PMRAtk\View\Traits\FormHelpersTrait;

/**
 * Class TestViewWithForm
 * @package PMRAtk\tests\phpunit\View\Traits
 */
class TestViewWithForm extends View {
    use FormHelpersTrait;
}


class FormHelpersTraitTest extends TestCase {

    public function testFieldIdsAndSubmitButtonId() {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->initLayout([Admin::class]);
        $v = TestViewWithForm::addTo($app);
        $f = Form::addTo($v);
        $f->id = 'testForm';
        $field1 = $f->addControl('test1');
        $field2 = $f->addControl('test2');
        self::assertNotEquals('test1', $field1->id);
        $v->setHTMLIds($f);
        self::assertEquals('test1', $field1->id);
        self::assertEquals('testForm_submit', $f->buttonSave->id);
    }
}
