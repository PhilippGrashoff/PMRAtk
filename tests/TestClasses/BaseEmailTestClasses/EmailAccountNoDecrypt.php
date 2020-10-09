<?php

declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseEmailTestClasses;

use atk4\data\Model;


class EmailAccountNoDecrypt extends Model
{

    public $table = 'email_account';


    protected function init(): void
    {
        parent::init();

        $this->addFields(
            [
                ['credentials', 'type' => 'string'],
            ]
        );
    }
}