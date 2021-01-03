<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;

use atk4\ui\Template;
use atk4\ui\View;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\Traits\SubTemplateCloneDeleteTrait;

class SubTemplateCloneDeleteTraitTest extends TestCase {

    /**
     *
     */
    public function testtemplateCloneAndDelete() {
        $view = $this->getTCADTestClass();
        $view->template = new Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada']);
        self::assertEquals('test1', $view->_tLala->render());
        self::assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    public function testtemplateCloneAndDeleteWithoutArgs() {
        $view = $this->getTCADTestClass();
        $view->template = new Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete();
        self::assertEquals('test1', $view->_tLala->render());
        self::assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    public function testwithNonExistantRegion() {
        $view = $this->getTCADTestClass();
        $view->template = new Template();
        $view->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada', 'NonExistantRegion']);
        self::assertEquals('test1', $view->_tLala->render());
        self::assertEquals('test2', $view->_tDada->render());
    }


    /**
     *
     */
    protected function getTCADTestClass(): View {
        $class = new class extends View {
            use SubTemplateCloneDeleteTrait;

            public $_tLala;
            public $_tDada;
        };

        return new $class();
    }
}
