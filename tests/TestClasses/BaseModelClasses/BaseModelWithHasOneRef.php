<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use auditforatk\ModelWithAuditTrait;
use PMRAtk\Data\BaseModel;
use mtomforatk\ModelWithMToMTrait;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\Data\Email;
use secondarymodelforatk\SecondaryModelRelationTrait;


class BaseModelWithHasOneRef extends BaseModel
{

    public $table = 'basemodel_with_has_one_ref';

    public function init(): void
    {
        parent::init();
        $this->addField('name');

        $this->hasOne('just_a_basemodel_id', [JustABaseModel::class]);
    }
}