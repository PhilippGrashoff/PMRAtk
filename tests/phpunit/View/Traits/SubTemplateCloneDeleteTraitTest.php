<?php

class TCADTest extends \atk4\ui\View {

    use \PMRAtk\View\Traits\SubTemplateCloneDeleteTrait;

    public $_tLala;
    public $_tDada;
}


class SubTemplateCloneDeleteTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testtemplateCloneAndDelete() {
        $t = new TCADTest();
        $t->template = new \atk4\ui\Template();
        $t->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $t->templateCloneAndDelete(['Lala', 'Dada']);
        $this->assertEquals('test1', $t->_tLala->render());
        $this->assertEquals('test2', $t->_tDada->render());
    }


    /*
     *
     */
    public function testtemplateCloneAndDeleteExceptionNonExistantRegion() {
        $t = new TCADTest();
        $t->template = new \atk4\ui\Template();
        $t->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $this->expectException(\atk4\data\Exception::class);
        $t->templateCloneAndDelete(['Lala', 'Dada', 'NonExistantRegion']);
    }
}
