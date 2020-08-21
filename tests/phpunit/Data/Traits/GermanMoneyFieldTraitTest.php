<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use atk4\data\Model;
use atk4\data\Persistence\Array_;
use atk4\ui\Persistence\UI;
use PMRAtk\Data\Traits\GermanMoneyFormatFieldTrait;
use PMRAtk\tests\phpunit\TestCase;

/**
 * Class GMF
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class GMF extends Model {

    use GermanMoneyFormatFieldTrait;

    public $table = 'gmf';
    public function init(): void {
        parent::init();
        $this->addFields( [
            ['money_test', 'type' => 'money'],
        ]);
        $this->_germanPriceForMoneyField($this->getField('money_test'));
    }
}


/**
 * Class GermanMoneyFieldTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class GermanMoneyFieldTraitTest extends TestCase {


    /*
     *
     */
    public function testLoadValueToUI() {
        $a = [];
        $gmf = new GMF(new Array_($a));
        $gmf->set('money_test', '25.25');
        $gmf->save();

        $pui = new UI();
        $pui->currency = null;
        $res = $pui->typecastSaveField($gmf->getField('money_test'), 25.25);
        self::assertEquals(25.25, $res);
    }


    /*
     *
     */
    public function testSaveValueFromUI() {
        $a = [];
        $gmf = new GMF(new Array_($a));
        $gmf->set('money_test', '25.25');
        $gmf->save();

        $pui = new UI();
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