<?php declare(strict_types=1);

namespace PMRAtk\Data;

use PMRAtk\Data\Traits\EncryptedFieldTrait;
use atk4\data\Model;

/**
 *
 */
class Setting extends BaseModel
{

    use EncryptedFieldTrait;

    public $table = 'setting';


    /**
     *
     */
    public function init(): void
    {
        parent::init();

        $this->addFields(
            [
                [
                    'ident',
                    'type' => 'string',
                    'caption' => 'Schlüssel'
                    , 'ui' => ['readonly' => true]
                ],
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
                    'system',
                    'type' => 'integer',
                    'system' => true
                ],
                [
                    'value',
                    'type' => 'string',
                    'system' => true, //system = true to prevent audit logging field value
                    'caption' => 'Wert',
                    'ui' => ['editable' => true]
                ],
            ]
        );

        $this->hasOne(
            'setting_group_id',
            [
                SettingGroup::class,
                'type' => 'integer',
                'system' => true,
                'ui' => ['form' => ['DropDown']]
            ]
        )
        ->addFields(
            [
                'setting_group_name' =>
                    [
                        'name',
                        'type' => 'string',
                        'system' => true
                    ]
            ]
        );

        //encrypt value field in case sensitive data is stored in there
        $this->encryptField($this->getField('value'), ENCRYPTFIELD_KEY);

        //system settings cannot be deleted
        $this->onHook(
            Model::HOOK_BEFORE_DELETE,
            function ($model) {
                if ($model->get('system')) {
                    throw new UserException(
                        'Diese Einstellung ist eine Systemeinstellung und kann nicht gelöscht werden.'
                    );
                }
            }
        );

        //ident of system setting cannot be edited if set
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function ($model) {
                if (
                    $model->get('system')
                    && $model->get('ident')
                ) {
                    $model->getField('ident')->read_only = true;
                }
            }
        );
    }
}
