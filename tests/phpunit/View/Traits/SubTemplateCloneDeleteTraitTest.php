<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;

use atk4\ui\View;

class SubTemplateCloneDeleteTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /**
     *
     */
    public function testtemplateCloneAndDelete() {
        $view = $this->getTCADTestClass();
        $view->template = new \atk4\ui\Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada']);
        $this->assertEquals('test1', $view->_tLala->render());
        $this->assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    public function testtemplateCloneAndDeleteWithoutArgs() {
        $view = $this->getTCADTestClass();
        $view->template = new \atk4\ui\Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete();
        $this->assertEquals('test1', $view->_tLala->render());
        $this->assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    public function testwithNonExistantRegion() {
        $view = $this->getTCADTestClass();
        $view->template = new \atk4\ui\Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada', 'NonExistantRegion']);
        $this->assertEquals('test1', $view->_tLala->render());
        $this->assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    protected function getTCADTestClass(): View {
        $class = new class extends View {
            use \PMRAtk\View\Traits\SubTemplateCloneDeleteTrait;

            public $_tLala;
            public $_tDada;
        };

        return new $class();
    }
}
