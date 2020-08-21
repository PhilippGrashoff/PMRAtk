<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses;

use PMRAtk\Data\MToMModel;
use atk4\data\Model;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;


/**
 * Class MToMModel
 * @package PMRAtk\tests\phpunit\Data
 */
class AToB extends MToMModel {

    public $table = 'AToB';

    protected string $className1 = BaseModelA::class;
    protected string $fieldName1 = 'BaseModelA_id';
    protected string $className2 = BaseModelB::class;
    protected string $fieldName2 = 'BaseModelB_id';


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addField('test1');

        $this->onHook(MODEL::HOOK_AFTER_INSERT, function($m) {
            $baseModelA = $m->ref('BaseModelA_id');
            $baseModelB = $m->ref('BaseModelB_id');
            $baseModelA->addMToMAudit('ADD', $baseModelB);
            $baseModelB->addMToMAudit('ADD', $baseModelB);
        });
    }
}