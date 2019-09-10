<?php

namespace PMRAtk\Data;

class SettingGroup extends BaseModel {

    public $table = 'setting_group';

    /*
     *
     */
    public function init() {
        parent::init();

        $this->addFields([
            ['name',         'type' => 'string'],
            ['description',  'type' => 'text'],
            ['order',        'type' => 'integer'],
        ]);

        $this->hasMany('Setting', \PMRAtk\Data\Setting::class);
    }
}
