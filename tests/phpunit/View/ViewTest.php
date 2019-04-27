<?php

class TCADTest extends \PMRAtk\View\View {
    public $_tLala;
    public $_tDada;
}


class ViewTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testtemplateCloneAndDelete() {
        $t = new TCADTest();
        $t->template = new \atk4\ui\Template();
        $t->template->loadTemplateFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $t->templateCloneAndDelete(['Lala', 'Dada', 'NonExisting']);
        $this->assertEquals('test1', $t->_tLala->render());
        $this->assertEquals('test2', $t->_tDada->render());
    }
}
