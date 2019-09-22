<?php

namespace PMRAtk\tests\phpunit;

class DeleteSetting extends \atk4\data\Model {

    public $table = 'setting';

    public function init() {
        parent::init();
        $this->addFields([
            ['ident', 'type' => 'string'],
        ]);
    }
}