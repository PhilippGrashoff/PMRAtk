<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use auditforatk\ModelWithAuditTrait;
use PMRAtk\Data\BaseModel;
use mtomforatk\ModelWithMToMTrait;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\Data\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;


class ModelWithDateAndTimeFields extends BaseModel
{

    public $table = 'model_with_date_and_time_fields';

    protected function init(): void
    {
        parent::init();
        $this->addField('date', ['type' => 'date']);
        $this->addField('time', ['type' => 'time']);
        $this->addField('datetime', ['type' => 'datetime']);
    }
}