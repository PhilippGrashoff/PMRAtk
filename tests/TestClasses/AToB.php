<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses;

use atk4\data\Model;
use mtomforatk\MToMModel;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;


/**
 * Class MToMModel
 * @package PMRAtk\tests\phpunit\Data
 */
class AToB extends MToMModel {

    public $table = 'AToB';

    protected $fieldNamesForReferencedClasses = [
        'BaseModelA_id' => BaseModelA::class,
        'BaseModelB_id' => BaseModelB::class
    ];


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addField('test1');

        //add Audit to both ref models
        $this->onHook(
            MODEL::HOOK_AFTER_INSERT,
            function(AToB $model) {
                $baseModelA = $model->getObject(BaseModelA::class);
                $baseModelB = $model->getObject(BaseModelB::class);
                $baseModelA->addMToMAudit('ADD', $baseModelB);
                $baseModelB->addMToMAudit('ADD', $baseModelA);
            }
        );
    }
}