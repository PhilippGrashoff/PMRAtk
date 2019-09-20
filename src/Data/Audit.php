<?php

namespace PMRAtk\Data;

class Audit extends SecondaryBaseModel {

    public $table = 'audit';

    public function init() {
        parent::init();

        $this->addFields([
            ['data',            'type'      => 'array',       'serialize' => 'serialize'],
            ['created_by_name', 'type'      => 'string'],
            ['created_by',      'type'      => 'integer'],
        ]);

        $this->setOrder(['created_date desc']);


        //if the model also has a field created_by_name (Audit models), fill in the current name of the admin user.
        $this->addHook('beforeInsert', function($m, &$data) {
            if(isset($m->app->auth->user)
                && $m->app->auth->user->loaded()) {
                $data['created_by_name'] = $m->app->auth->user->get('name');
                $data['created_by'] = $m->app->auth->user->get('id');
            }
        });
    }
}
