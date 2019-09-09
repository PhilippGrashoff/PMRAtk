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
            ['value',        'type' => 'string'],
        ]);

        $this->hasOne('setting_group_id', [SettingGroup::class, 'type' => 'integer'])
              ->addFields(['setting_group_name' => ['name', 'type' => 'string']]);

        //encrypt value field
        $this->encryptField($this->getField('value'), ENCRYPTFIELD_KEY);
    }
}
