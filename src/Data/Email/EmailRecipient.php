<?php

namespace PMRAtk\Data\Email;

class EmailRecipient extends \atk4\data\Model {

    public $table = 'email_recipient';

    /*
     *
     */
    public function init() {
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

        //id of the BaseEmail it belongs to
        $this->hasOne('base_email_id', new BaseEmail());
    }
};