<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\tests\TestClasses\AToB;


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