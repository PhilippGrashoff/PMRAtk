<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use auditforatk\ModelWithAuditTrait;
use PMRAtk\Data\BaseModel;
use mtomforatk\ModelWithMToMTrait;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\Data\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;


class JustABaseModel extends BaseModel
{

    public $table = 'just_a_basemodel';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');
        $this->addField('firstname');
        $this->addField('lastname');
    }
}