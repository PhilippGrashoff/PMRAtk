<?php

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
}
