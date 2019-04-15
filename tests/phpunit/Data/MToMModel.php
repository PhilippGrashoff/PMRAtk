<?php

namespace PMRAtk\tests\phpunit\Data;

class MToMModel extends \atk4\data\Model {

    public $table = 'AToB';

    public function init() {
        parent::init();

        $this->addFields([
            ['test1', 'type' => 'string'],
        ]);

        $this->hasOne('BaseModelA_id', [new BaseModelA()]);
        $this->hasOne('BaseModelB_id', [new BaseModelB()]);
    }
}