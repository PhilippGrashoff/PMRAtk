<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModelWithEPA;
use PMRAtk\tests\TestClasses\AToB;


class ModelWithEPA extends BaseModelWithEPA
{

    public $table = 'model_with_epa';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');
    }
}