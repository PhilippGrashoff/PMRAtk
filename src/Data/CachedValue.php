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
        $this->addHook('beforeSave', function($m) {
            $cv = $m->newInstance();
            $cv->tryLoad($m->get('ident'));
            if($cv->loaded()) {
                $m->id = $m->get('ident');
            }
        });
    }
}
