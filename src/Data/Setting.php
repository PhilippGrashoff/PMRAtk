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
            ['ident',        'type' => 'string',     'caption' => 'Schlüssel', 'ui' => ['readonly' => true]],
            ['name',         'type' => 'string'],
            ['description',  'type' => 'text',       'caption' => 'Beschreibung'],
            ['system',       'type' => 'integer',    'system' => true],
            //system = true to prevent audit
            ['value',        'type' => 'string',     'system' => true, 'caption' => 'Wert', 'ui' => ['editable' => true]],
        ]);

        $this->hasOne('setting_group_id', [SettingGroup::class, 'type' => 'integer', 'system' => true, 'ui' => ['form' => ['DropDown']]])
              ->addFields(['setting_group_name' => ['name', 'type' => 'string', 'system' => true]]);

        //encrypt value field
        $this->encryptField($this->getField('value'), ENCRYPTFIELD_KEY);

        //system settings cannot be deleted
        $this->addHook('beforeDelete', function($m) {
            if($m->get('system')) {
                throw new \PMRAtk\Data\UserException('Diese Einstellung ist eine Systemeinstellung und kann nicht gelöscht werden.');
            }
        });

        //ident of system setting cannot be edited if set
        $this->addHook('afterLoad', function($m) {
            if($m->get('system') && $m->get('ident')) {
                $m->getField('ident')->read_only = true;
            }
        });
    }
}
