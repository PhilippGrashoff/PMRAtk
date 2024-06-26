<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use atk4\data\Model;
use atk4\ui\Form\Control\Dropdown;
use PMRAtk\Data\BaseModel;
use traitsforatkdata\EncryptedFieldTrait;

class EmailAccount extends BaseModel
{

    use EncryptedFieldTrait;

    public $table = 'email_account';

    protected bool $enableInternalAccounts = false;


    protected function init(): void
    {
        parent::init();
        $this->addFields(
            [
                [
                    'name',
                    'type' => 'string',
                    'caption' => 'Email-Adresse'
                ],
                [
                    'sender_name',
                    'type' => 'string',
                    'caption' => 'Name des Versenders'
                ],
                [
                    'details',
                    'type' => 'text'
                ],
                [
                    'credentials',
                    'type' => 'text',
                    'system' => true
                ],
                [
                    'user',
                    'type' => 'string',
                    'caption' => 'Benutzername',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'password',
                    'type' => 'string',
                    'caption' => 'Passwort',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'imap_host',
                    'type' => 'string',
                    'caption' => 'IMAP Host',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'imap_port',
                    'type' => 'string',
                    'caption' => 'IMAP Port',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'imap_sent_folder',
                    'type' => 'string',
                    'caption' => 'IMAP: Gesendet-Ordner',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'imap_encryption',
                    'type' => 'string',
                    'caption' => 'Verschlüsselung',
                    'system' => true,
                    'values' => ['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'keine'],
                    'never_persist' => true,
                    'ui' => ['editable' => true, 'form' => [Dropdown::class]]
                ],
                [
                    'smtp_host',
                    'type' => 'string',
                    'caption' => 'SMTP Host',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'smtp_port',
                    'type' => 'string',
                    'caption' => 'SMTP Port',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true]
                ],
                [
                    'allow_self_signed_ssl',
                    'type' => 'integer',
                    'caption' => 'SSL: Self-signed Zertifikate erlauben',
                    'system' => true,
                    'never_persist' => true,
                    'ui' => ['editable' => true, 'form' => [Dropdown::class, 'values' => [0 => 'Nein', '1' => 'Ja']]]
                ],
            ]
        );

        if(!$this->enableInternalAccounts) {
            $this->addCondition('id', '>', 0);
        }

        $this->encryptField($this->getField('credentials'), ENCRYPTFIELD_KEY);

        //after load, unserialize value field
        $this->onHook(
            Model::HOOK_AFTER_LOAD,
            function (self $model) {
                $a = unserialize($model->get('credentials'));
                foreach ($a as $key => $value) {
                    if ($model->hasField($key)) {
                        $model->set($key, $value);
                    }
                }
            }
        );

        //before save, serialize value field
        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $model) {
                $a = [
                    'user' => $model->get('user'),
                    'password' => $model->get('password'),
                    'imap_host' => $model->get('imap_host'),
                    'imap_port' => $model->get('imap_port'),
                    'imap_sent_folder' => $model->get('imap_sent_folder'),
                    'imap_encryption' => $model->get('imap_encryption'),
                    'smtp_host' => $model->get('smtp_host'),
                    'smtp_port' => $model->get('smtp_port'),
                    'allow_self_signed_ssl' => $model->get('allow_self_signed_ssl'),
                ];

                $model->set('credentials', serialize($a));
            }
        );
    }
}