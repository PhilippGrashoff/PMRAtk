<?php declare(strict_types=1);

namespace PMRAtk\Data;


/**
 * Class SettingGroup
 * @package PMRAtk\Data
 */
class SettingGroup extends BaseModel {

    public $table = 'setting_group';

    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addFields(
            [
                [
                    'name',
                    'type' => 'string'
                ],
                [
                    'description',
                    'type' => 'text',
                    'caption' => 'Beschreibung'
                ],
                [
                    'order',
                    'type' => 'integer',
                    'caption' => 'Sortierung'
                ],
            ]
        );

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();

        $this->hasMany('Setting', Setting::class);
    }
}
