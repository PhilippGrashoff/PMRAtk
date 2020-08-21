<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Model;


/**
 * Class CachedValue
 * @package PMRAtk\Data
 */
class CachedValue extends BaseModel {

    public $table    = 'cached_value';

    public $id_field = 'ident';

    //doesnt need reloading after save
    public $reload_after_save = false;


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addFields(
            [
                [
                    'value',
                    'type' => 'string'
                ],
            ]
        );

        //if setting with ident exists, only update
        //TODO: If somehow ON DUPLICATE KEY UPDATE is available in ATK, it would save a query
        $this->onHook(Model::HOOK_BEFORE_SAVE, function($model, $isUpdate) {
            if($isUpdate) {
                return;
            }
            $cv = $model->newInstance();
            $cv->tryLoad($model->get('ident'));
            if($cv->loaded()) {
                $cv->set('value', $model->get('value'));
                $cv->save();
                $model->breakHook(false);
            }
        });
    }
}
