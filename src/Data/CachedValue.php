<?php

namespace PMRAtk\Data;

class CachedValue extends BaseModel {

    public $table    = 'cached_value';

    public $id_field = 'ident';

    //doesnt need reloading after save
    public $reload_after_save = false;


    /*
     *
     */
    public function init() {
        parent::init();

        $this->addFields([
            ['value',        'type' => 'string'],
        ]);

        //if setting with ident exists, only update
        //TODO: If somehow ON DUPLICATE KEY UPDATE is available in ATK, it would save a query
        $this->addHook('beforeSave', function($m, $is_update) {
            $m->set('last_updated', new \DateTime());
            if($is_update) {
                return;
            }
            $cv = $m->newInstance();
            $cv->tryLoad($m->get('ident'));
            if($cv->loaded()) {
                $cv->set('value', $m->get('value'));
                $cv->set('last_updated', new \DateTime());
                $cv->save();
                $m->breakHook(false);
            }
        });
    }
}
