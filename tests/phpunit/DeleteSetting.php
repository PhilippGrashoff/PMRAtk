<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit;

use atk4\data\Model;


/**
 *
 */
class DeleteSetting extends Model {

    public $table = 'setting';

    public function init(): void {
        parent::init();
        $this->addFields([
            ['ident', 'type' => 'string'],
        ]);
    }
}