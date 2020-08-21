<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Traits\EncryptedFieldTrait;

class EmailAccount extends BaseModel {

    use EncryptedFieldTrait;

    public $table = 'email_account';

    /*
     *
     */
    public function init(): void {
        parent::init();
        $this->addFields([
            ['name',                   'type' => 'string',  'caption' => 'Email-Adresse'],
            ['sender_name',            'type' => 'string',  'caption' => 'Name des Versenders'],
            ['details',                'type' => 'text'],
            ['credentials',            'type' => 'text',    'system' => true],
            ['user',                   'type' => 'string',  'caption' => 'Benutzername',                              'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['password',               'type' => 'string',  'caption' => 'Passwort',                                  'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['imap_host',              'type' => 'string',  'caption' => 'IMAP Host',                                 'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['imap_port',              'type' => 'string',  'caption' => 'IMAP Port',                                 'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['imap_sent_folder',       'type' => 'string',  'caption' => 'IMAP: Gesendet-Ordner',                     'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['smtp_host',              'type' => 'string',  'caption' => 'SMTP Host',                                 'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['smtp_port',              'type' => 'string',  'caption' => 'SMTP Port',                                 'system' => true, 'never_persist' => true, 'ui' => ['editable' => true]],
            ['allow_self_signed_ssl',  'type' => 'integer', 'caption' => 'SSL: Self-signed Zertifikate erlauben',     'system' => true, 'never_persist' => true, 'ui' => ['editable' => true, 'form' => ['DropDown', 'values' => [0 => 'Nein', '1' => 'Ja']]]],
        ]);

        $this->encryptField($this->getField('credentials'), ENCRYPTFIELD_KEY);

        //after load, unserialize value field
        $this->onHook('afterLoad', function($m) {
            $a = unserialize($m->get('credentials'));
            foreach($a as $key => $value) {
                if($m->hasField($key)) {
                    $m->set($key, $value);
                }
            }
        });

        //before save, serialize value field
        $this->onHook('beforeSave', function($m) {
            $a = [
                'user'                  => $m->get('user'),
                'password'              => $m->get('password'),
                'imap_host'             => $m->get('imap_host'),
                'imap_port'             => $m->get('imap_port'),
                'imap_sent_folder'      => $m->get('imap_sent_folder'),
                'smtp_host'             => $m->get('smtp_host'),
                'smtp_port'             => $m->get('smtp_port'),
                'allow_self_signed_ssl' => $m->get('allow_self_signed_ssl'),
            ];

            $m->set('credentials', serialize($a));
        });
    }
}