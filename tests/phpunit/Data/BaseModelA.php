<?php

namespace PMRAtk\tests\phpunit\Data;

class BaseModelA extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;
    use \PMRAtk\Data\Traits\MToMTrait;

    public $table = 'BaseModelA';

    public function init() {
        parent::init();

        $this->addFields([
            ['name', 'type' => 'string'],
        ]);

        $this->_addEPARefs();

        $this->hasMany('MToMModel', new MToMModel());

        $this->hasOne('BaseModelB_id', new BaseModelB());
    }
}