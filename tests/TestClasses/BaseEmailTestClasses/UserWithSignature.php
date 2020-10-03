<?php

declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseEmailTestClasses;

use atk4\data\Model;

class UserWithSignature extends Model {

    public $table = 'user';

    protected function init(): void
    {
        parent::init();
        $this->addField('signature');
        $this->set('signature', 'TestSignature');
    }
}