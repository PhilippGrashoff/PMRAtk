<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Exception;
use atk4\login\Field\Password;
use PMRAtk\Data\Traits\MaxFailedLoginsTrait;
use traitsforatkdata\UniqueFieldTrait;
use traitsforatkdata\UserException;
use atk4\data\Model;

class User extends BaseModelWithEPA
{

    use MaxFailedLoginsTrait;
    use UniqueFieldTrait;

    public $table = 'User';
    public $caption = 'Benutzer';


    protected function init(): void
    {
        parent::init();
        $this->addfields(
            [
                [
                    'name',
                    'type' => 'string',
                    'caption' => 'Name',
                    'system' => true,
                ],
                [
                    'firstname',
                    'type' => 'string',
                    'caption' => 'Vorname'
                ],
                [
                    'lastname',
                    'type' => 'string',
                    'caption' => 'Nachname'
                ],
                [
                    'username',
                    'type' => 'string',
                    'caption' => 'Benutzername',
                    'ui' => ['form' => ['inputAttr' => ['autocomplete' => 'new-password']]]
                ],
                [
                    'password',
                    Password::class,
                    'caption' => 'Passwort',
                    'system' => true,
                    'ui' => ['form' => ['inputAttr' => ['autocomplete' => 'new-password']]]
                ],
                [
                    'signature',
                    'type' => 'text',
                    'caption' => 'Signatur'
                ],
                [
                    'role',
                    'type' => 'string',
                    'caption' => 'Benutzerrolle'
                ]
            ]
        );

        $this->_addFailedLoginsField();

        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function (self $model, $isUpdate) {
                if(
                    $model->get('username')
                    && !$model->isFieldUnique('username')
                ) {
                    throw new UserException('Der Benutzername ist bereits vergeben! Bitte wähle einen anderen');
                }
            }
        );
    }

    public function setNewPassword(
        string $new_password_1,
        string $new_password_2,
        bool $compare_old_password = false,
        string $old_password = ''
    ): void {
        //other user than logged in user tries saving?
        if (
            $this->app->auth->user->loaded()
            && $this->app->auth->user->get('id') !== $this->get('id')
        ) {
            throw new Exception('Password can only be changed by account owner');
        }

        //old password entered needs to fit saved one
        if (
            $compare_old_password
            && !$this->compare('password', $old_password)
        ) {
            throw new UserException('Das Alte Passwort ist nicht korrekt');
        }

        //new passwords need to match
        if ($new_password_1 !== $new_password_2) {
            throw new UserException('Die Passwörter stimmen nicht überein');
        }

        $this->set('password', $new_password_1);
    }

    /**
     * TODO: THIS IS USER MANAGEMENT, SHOULD NOT BE IN USER MODEL
     *//*
    public function sendResetPasswordEmail(string $username): bool
    {
        //loaded record may not use this function
        $c = $this->newInstance();
        //try load by username
        $c->tryLoadBy('username', $username);
        if (!$c->loaded()) {
            throw new Exception('Record not found in ' . __FUNCTION__);
        }
        //send email
        $t = $this->app->loadEmailTemplate('reset_password.html');
        $t->set('url', URL_BASE_PATH);
        $t->set('token', $c->setNewToken());

        $phpmailer = new PHPMailer($this->app);
        $phpmailer->setBody($t->render());
        $phpmailer->Subject = $this->app->title . ': Passwort zurücksetzen';
        $phpmailer->addAddress($c->getFirstEmail());

        return $phpmailer->send();
    }*/

    public function resetPassword(
        string $token,
        string $new_password_1,
        string $new_password_2
    ) {
        //new passwords need to match
        if ($new_password_1 !== $new_password_2) {
            throw new UserException('Die neuen Passwörter stimmen nicht überein');
        }
        $t = new Token($this->persistence);
        $t->loadBy('value', $token);
        $this->tryLoad($t->get('model_id'));
        if (!$this->loaded()) {
            throw new UserException('Das Token konnte nicht gefunden werden');
        }

        $this->set('password', $new_password_1);
        $this->save();
        $t->delete();
    }

    public function setNewToken(): string
    {
        $t = new Token($this->persistence, ['parentObject' => $this, 'expiresAfterInMinutes' => 180]);
        $t->save();
        return $t->get('value');
    }

    public function getSignature()
    {
        return $this->get('signature');
    }
}