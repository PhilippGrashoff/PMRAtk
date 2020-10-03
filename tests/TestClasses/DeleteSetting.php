<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses;

use atk4\data\Model;

class DeleteSetting extends Model {

    public $table = 'setting';

    protected function init(): void {
        parent::init();
        $this->addFields([
            ['ident', 'type' => 'string'],
        ]);
    }
}