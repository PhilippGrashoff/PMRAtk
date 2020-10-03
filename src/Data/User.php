<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Exception;
use atk4\login\Field\Password;
use PMRAtk\Data\Email\PHPMailer;
use PMRAtk\Data\Traits\MaxFailedLoginsTrait;
use traitsforatkdata\UserException;

class User extends BaseModel
{

    use MaxFailedLoginsTrait;

    public $table = 'User';

    protected function init(): void
    {
        parent::init();
        $this->addfields(
            [
                [
                    'name',
                    'type' => 'string',
                    'caption' => 'Name'
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
                ]
            ]
        );

        $this->_addFailedLoginsField();
    }

    public function setNewPassword(
        string $new_password_1,
        string $new_password_2,
        bool $compare_old_password = false,
        string $old_password = ''
    ) {
        //other user than logged in user tries saving?
        if ($this->app->auth->user->loaded()
            && $this->app->auth->user->get('id') !== $this->get('id')) {
            throw new Exception('Password can only be changed by account owner');
        }

        //old password entered needs to fit saved one
        if ($compare_old_password
            && !$this->compare('password', $old_password)) {
            throw new UserException('Das Alte Passwort ist nicht korrekt');
        }

        //new passwords need to match
        if ($new_password_1 !== $new_password_2) {
            throw new UserException('Die Passwörter stimmen nicht überein');
        }

        $this->set('password', $new_password_1);
    }

    public function sendResetPasswordEmail(string $username): bool {
        //loaded record may not use this function
        $c = $this->newInstance();
        //try load by username
        $c->tryLoadBy('username', $username);
        if (!$c->loaded()) {
            throw new Exception('Record not found in' . __FUNCTION__);
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
    }

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
            throw new UserException('Der Eintrag konnte nicht gefunden werden');
        }

        $this->set('password', $new_password_1);
        //allow save without logged in user
        $this->maySave = true;
        $this->save();
        $this->maySave = false;
        //delete the token
        $t->delete();
    }

    //TODO: NEEDED?
    public function setNewToken(): string
    {
        $t = new Token($this->persistence, ['parentObject' => $this, 'expiresAfterInMinutes' => 180]);
        $t->save();
        return $t->get('value');
    }

    protected function _standardUserRights()
    {
        //no logged in user?
        if (!$this->app->auth->user) {
            return false;
        }

        //user is owner of current record?
        if ($this->get('id') === $this->app->auth->user->get('id')) {
            return true;
        }

        return false;
    }

    public function getSignature()
    {
        return '';
    }
}