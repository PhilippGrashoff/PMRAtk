<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use mtomforatk\ModelWithMToMTrait;
use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Traits\AuditTrait;
use PMRAtk\Data\Traits\FileRelationTrait;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\tests\TestClasses\SecondaryModelClasses\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;

class BaseModelB extends BaseModel {

    use AuditTrait;
    use FileRelationTrait;
    use ModelWithMToMTrait;
    use SecondaryModelRelationTrait;

    public $table = 'BaseModelB';

    public function init(): void {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string', 'caption' => 'AName'],
            ['time_test', 'type' => 'time',   'caption' => 'Startzeit'],
            ['date_test', 'type' => 'date',   'caption' => 'Startdatum'],
        ]);

        $this->addSecondaryModelHasMany(Email::class);
        $this->_addAuditRef();
        $this->_addFileRef();

        $this->addMToMReferenceAndDeleteHook(AToB::class);
    }
}