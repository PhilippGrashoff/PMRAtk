<?php

namespace PMRAtk\tests\phpunit\Data;

class BaseModelB extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;
    use \PMRAtk\Data\Traits\MToMTrait;

    public $table = 'BaseModelB';

    public function init() {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string'],
            ['time_test', 'type' => 'time'],
            ['date_test', 'type' => 'date'],
        ]);

        $this->_addEPARefs();

        $this->hasMany('MToMModel', new MToMModel());
    }
}