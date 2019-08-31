<?php

namespace PMRAtk\Data\Email;

class EmailAccount extends \PMRAtk\Data\Setting {

    public $table = 'setting';

    /*
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['user',      'type' => 'string', 'never_persist' => true],
            ['password',  'type' => 'string', 'never_persist' => true],
            ['imap_host', 'type' => 'string', 'never_persist' => true],
            ['imap_port', 'type' => 'string', 'never_persist' => true],
            ['smtp_host', 'type' => 'string', 'never_persist' => true],
            ['smtp_port', 'type' => 'string', 'never_persist' => true],
        ]);

        //after load, unserialize value field
        $this->addHook('afterLoad', function($m) {
            $a = unserialize($m->get('value'));
            foreach($a as $key => $value) {
                if($m->hasField($key)) {
                    $m->set($key, $value);
                }
            }
        });

        //before save, serialize value field
        $this->addHook('beforeSave', function($m) {
            $a = [
                'user'          => $m->get('user'),
                'password'      => $m->get('password'),
                'imap_host'     => $m->get('imap_host'),
                'imap_port'     => $m->get('imap_port'),
                'smtp_host'     => $m->get('smtp_host'),
                'smtp_port'     => $m->get('smtp_port'),
            ];

            $m->set('value', serialize($a));
        });
    }
}