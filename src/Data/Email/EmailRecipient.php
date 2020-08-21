<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use atk4\data\Model;

class EmailRecipient extends Model {

    public $table = 'email_recipient';

    /*
     *
     */
    public function init(): void {
        parent::init();
        $this->addFields([
            //id of model this email comes from
            ['model_id',        'type' => 'integer'],
            ['model_class',     'type' => 'string'],
            //email address
            ['email',           'type' => 'string'],
            ['firstname',       'type' => 'string'],
            ['lastname',        'type' => 'string'],
        ]);
    }
}