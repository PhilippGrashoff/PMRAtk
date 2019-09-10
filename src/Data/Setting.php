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
            ['ident',        'type' => 'string',     'caption' => 'Schlüssel', 'ui' => ['editable' => false]],
            ['name',         'type' => 'string'],
            ['description',  'type' => 'text',       'caption' => 'Beschreibung'],
            ['system',       'type' => 'integer',    'system' => true],
            ['value',        'type' => 'string',     'caption' => 'Wert'],
        ]);

        $this->hasOne('setting_group_id', [SettingGroup::class, 'type' => 'integer', 'ui' => ['form' => ['DropDown']]])
              ->addFields(['setting_group_name' => ['name', 'type' => 'string', 'system' => true]]);

        //encrypt value field
        $this->encryptField($this->getField('value'), ENCRYPTFIELD_KEY);
    }
}
