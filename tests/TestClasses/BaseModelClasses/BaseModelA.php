<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Traits\AuditTrait;
use mtomforatk\ModelWithMToMTrait;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\tests\TestClasses\SecondaryModelClasses\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;

/**
 * Class BaseModelA
 * @package PMRAtk\tests\phpunit\Data\Cron\CronTestClasses\BaseModelClasses
 */
class BaseModelA extends BaseModel {

    use ModelWithMToMTrait;
    use SecondaryModelRelationTrait;
    use AuditTrait;

    public $table = 'BaseModelA';

    public $caption = 'BMACAPTION';


    public function init(): void {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string'],
            ['date',      'type' => 'date'],
            ['time',      'type' => 'time'],
            ['dd_test',   'type' => 'integer', 'ui' => ['form' => ['DropDown', 'values' => [0 => 'Nein', 1 => 'Ja']]]],
            ['dd_test_2', 'type' => 'string', 'ui' => ['form' => ['DropDown', 'empty' => 'Hans']]],
            ['firstname', 'type' => 'string', 'caption' => 'Vorname'],
            ['lastname',  'type' => 'string'],

        ]);

        $this->_addAuditRef();
        $this->addSecondaryModelHasMany(Email::class);

        $this->addMToMReferenceAndDeleteHook(AToB::class);

        $this->hasOne('BaseModelB_id', new BaseModelB());
    }

    public function getFieldsForEmailTemplate():array {
        return ['name', 'firstname'];
    }
}