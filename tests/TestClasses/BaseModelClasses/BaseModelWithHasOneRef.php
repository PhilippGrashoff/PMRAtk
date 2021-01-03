<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\tests\TestClasses\AToB;


class BaseModelWithHasOneRef extends BaseModel
{

    public $table = 'basemodel_with_has_one_ref';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');

        $this->hasOne('just_a_basemodel_id', [JustABaseModel::class]);
    }
}