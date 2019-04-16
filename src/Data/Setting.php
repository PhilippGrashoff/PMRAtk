<?php

namespace PMRAtk\Data;

class Setting extends BaseModel {

    use \PMRAtk\Data\Traits\EncryptedFieldTrait;

    public $table = 'setting';


    /*
     *
     */
    public function init() {
        parent::init();

        $this->addFields([
            ['ident',        'type' => 'string'],
            ['name',         'type' => 'string'],
            ['description',  'type' => 'text'],
            ['system',       'type' => 'integer', 'system' => true],
            ['category',     'type' => 'string'],
            ['value',        'type' => 'string'],
        ]);

        //encrypt value field
        $this->encryptField($this->getElement('value'), $this->app->getSetting('ENCRYPTFIELD_KEY'));
    }
}
