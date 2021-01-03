<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;

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