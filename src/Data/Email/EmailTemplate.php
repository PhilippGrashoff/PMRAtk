<?php

namespace PMRAtk\Data\Email;

class EmailTemplate extends \PMRAtk\Data\SecondaryBaseModel {

    public $table = 'email_template';

    /*
     *
     */
    public function init() {
        parent::init();

        $this->addFields([
            ['ident',        'type' => 'string'],
        ]);
    }
}
