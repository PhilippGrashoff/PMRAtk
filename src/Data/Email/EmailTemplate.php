<?php

namespace PMRAtk\Data\Email;

class EmailTemplate extends \PMRAtk\Data\BaseModel {

    public $table = 'email_template';

    /*
     *
     */
    public function init() {
        parent::init();

        $this->addFields([
            ['ident',        'type' => 'string'],
            ['value',        'type' => 'text'],
        ]);
    }
}
