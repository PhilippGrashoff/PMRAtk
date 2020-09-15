<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use secondarymodelforatk\SecondaryModel;

class EmailRecipient extends SecondaryModel
{

    public $table = 'email_recipient';


    public function init(): void
    {
        parent::init();
        $this->addFields(
            [
                ['email', 'type' => 'string'],
                ['firstname', 'type' => 'string'],
                ['lastname', 'type' => 'string'],
            ]
        );
    }
}