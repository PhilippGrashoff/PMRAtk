<?php

class GMF extends \atk4\data\Model {

    use \PMRAtk\Data\Traits\GermanMoneyFormatFieldTrait;

    public $table = 'gmf';
    public function init() {
        parent::init();
        $this->addFields( [
            ['money_test', 'type' => 'money'],
        ]);
        $this->_germanPriceForMoneyField($this->getField('money_test'));
    }
}


class GermanMoneyFieldTraitTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testLoadValueToUI() {
        $a = [];
        $gmf = new GMF(new \atk4\data\Persistence\Array_($a));
        $gmf->set('money_test', '25.25');
        $gmf->save();

        $pui = new \atk4\ui\Persistence\UI();
        $res = $pui->typecastSaveField($gmf->getField('money_test'), 25.25);
        self::assertEquals(25.25, $res);
    }


    /*
     *
     */
    public function testSaveValueFromUI() {
        $a = [];
        $gmf = new GMF(new \atk4\data\Persistence\Array_($a));
        $gmf->set('money_test', '25.25');
        $gmf->save();

        $pui = new \atk4\ui\Persistence\UI();
        $res = $pui->typecastLoadField($gmf->getField('money_test'), '25,25');
        self::assertEquals(25.25, $res);
        $res = $pui->typecastLoadField($gmf->getField('money_test'), 25.25);
        self::assertEquals(25.25, $res);
        $res = $pui->typecastLoadField($gmf->getField('money_test'), '025.25');
        self::assertEquals(25.25, $res);
        $res = $pui->typecastLoadField($gmf->getField('money_test'), '025,2');
        self::assertEquals(25.20, $res);
        $res = $pui->typecastLoadField($gmf->getField('money_test'), '25');
        self::assertEquals(25.00, $res);
    }
}