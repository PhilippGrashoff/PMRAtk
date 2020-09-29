<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use auditforatk\ModelWithAuditTrait;
use PMRAtk\Data\BaseModel;
use mtomforatk\ModelWithMToMTrait;
use PMRAtk\Data\BaseModelWithEPA;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\Data\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;


class ModelWithEPA extends BaseModelWithEPA
{

    public $table = 'model_with_epa';

    public function init(): void
    {
        parent::init();
        $this->addField('name');
    }
}