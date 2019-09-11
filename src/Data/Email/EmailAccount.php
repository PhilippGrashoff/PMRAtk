<?php

namespace PMRAtk\Data\Email;

class EmailAccount extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EncryptedFieldTrait;

    public $table = 'email_account';

    /*
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['email_address', 'type' => 'string', 'caption' => 'Email-Adresse'],
            ['details',       'type' => 'text'],
            ['credentials',   'type' => 'text',   'system' => true],
            ['user',          'type' => 'string', 'caption' => 'Benutzername', 'never_persist' => true],
            ['password',      'type' => 'string', 'caption' => 'Passwort',     'never_persist' => true],
            ['imap_host',     'type' => 'string', 'caption' => 'IMAP Host',    'never_persist' => true],
            ['imap_port',     'type' => 'string', 'caption' => 'IMAP Port',    'never_persist' => true],
            ['smtp_host',     'type' => 'string', 'caption' => 'SMTP Host',    'never_persist' => true],
            ['smtp_port',     'type' => 'string', 'caption' => 'SMTP Port',    'never_persist' => true],
        ]);

        $this->encryptField($this->getField('credentials'), ENCRYPTFIELD_KEY);

        //after load, unserialize value field
        $this->addHook('afterLoad', function($m) {
            $a = unserialize($m->get('credentials'));
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

            $m->set('credentials', serialize($a));
        });
    }
}