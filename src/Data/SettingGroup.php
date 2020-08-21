<?php declare(strict_types=1);

namespace PMRAtk\Data;

class SettingGroup extends BaseModel {

    public $table = 'setting_group';

    /*
     *
     */
    public function init(): void {
        parent::init();

        $this->addFields([
            ['name',         'type' => 'string'],
            ['description',  'type' => 'text'],
            ['order',        'type' => 'integer'],
        ]);

        $this->hasMany('Setting', Setting::class);
    }
}
