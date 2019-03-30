<?php

namespace PMRAtk\Data;

class User extends BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;

    public $table = 'User';

    public function init() {
        parent::init();
        $this->addfields([
            ['name',            'type' => 'string',  'caption' => 'Name'],
            ['username',        'type' => 'string',  'caption' => 'Benutzername'],
        ]);

        //password field from atk login
        $p = new \atk4\login\Field\Password();
        $this->addField('password', [$p, 'caption' => 'Passwort', 'system' => true]);

        $this->_addEPARefs();
     }


    /*
     *  saves the new password
     */
    public function setNewPassword(string $new_password_1, string $new_password_2, bool $compare_old_password = false, string $old_password = '') {
        //other user than logged in user tries saving?
        if($this->app->auth->user->loaded()
        && $this->app->auth->user->get('id') !== $this->get('id')) {
            throw new \atk4\data\Exception('Password can only be changed by account owner');
        }

        //old password entered needs to fit saved one
        if($compare_old_password
        && !$this->compare('password', $old_password)) {
            throw new \PMRAtk\Data\UserException('Das Alte Passwort ist nicht korrekt');
        }

        //new passwords need to match
        if($new_password_1 !== $new_password_2) {
            throw new \PMRAtk\Data\UserException('Die Passwörter stimmen nicht überein');
        }

        $this->set('password', $new_password_1);
    }


    /*
     *
     */
    public function sendResetPasswordEmail(string $username):bool {
        //loaded record may not use this function
        $c = $this->newInstance();
        //try load by username
        $c->tryLoadBy('username', $username);
        if(!$c->loaded()) {
            throw new \atk4\data\Exception('Record not found in'.__FUNCTION__);
        }
        //send email
        $t = $this->app->loadEmailTemplate('reset_password.html');
        $t->set('url',   URL_BASE_PATH);
        $t->set('token', $c->setNewToken());

        $phpmailer = new \PMRAtk\Data\Email\PHPMailer($this->app);
        $phpmailer->setBody($t->render());
        $phpmailer->Subject = $this->app->title.': Passwort zurücksetzen';
        $phpmailer->addAddress($c->getFirstEmail());

        return $phpmailer->send();
    }


    /*
     * reset password on token base
     */
    public function resetPassword(string $token, string $new_password_1, string $new_password_2) {
        //new passwords need to match
        if($new_password_1 !== $new_password_2) {
            throw new \PMRAtk\Data\UserException('Die neuen Passwörter stimmen nicht überein');
        }
        $t = new \PMRAtk\Data\Token($this->persistence);
        $t->loadBy('value', $token);
        $this->tryLoad($t->get('model_id'));
        if(!$this->loaded()) {
            throw new \PMRAtk\Data\UserException('Der Eintrag konnte nicht gefunden werden');
        }

        $this->set('password', $new_password_1);
        //allow save without logged in user
        $this->maySave = true;
        $this->save();
        $this->maySave = false;
        //delete the token
        $t->delete();
    }


    /*
     * sets a new token and timeout for it
     */
    public function setNewToken():string {
        $t = new \PMRAtk\Data\Token($this->persistence, ['parentObject' => $this, 'expiresInMinutes' => 180]);
        $t->save();
        return $t->get('value');
    }


    /*
     * check if required params are set
     */
    public function validate($intent = null) {
        $errors = [];
        if(empty($this->get('name'))) {
            $errors['name'] = 'Ein (Firmen)Name muss angegeben werden';
        }
        //make name is unique
        elseif($this->isDirty(['name'])) {
            $c = $this->newInstance();
            $c->addCondition($this->id_field, 'not', $this->get($this->id_field));
            $c->tryLoadBy('name', $this->get('name'));
            if($c->loaded()) {
                $errors['name'] = 'Dieser (Firmen)Name wird bereits verwendet, bitte wähle einen anderen';
            }
        }

        if(empty($this->get('username'))) {
            $errors['username'] = 'Ein Benutzername muss angegeben werden';
        }
        //make name is unique
        elseif($this->isDirty(['username'])) {
            $c = $this->newInstance();
            $c->addCondition($this->id_field, 'not', $this->get($this->id_field));
            $c->tryLoadBy('username', $this->get('username'));
            if($c->loaded()) {
                $errors['username'] = 'Dieser Benutzername wird bereits verwendet, bitte wähle einen anderen';
            }
        }

        return array_merge(parent::validate($intent), $errors);
    }


    /*
     *
     */
    protected function _standardUserRights() {
        //no logged in user?
        if(!$this->app->auth->user) {
            return false;
        }

        //user is owner of current record?
        if($this->get('id') === $this->app->auth->user->get('id')) {
            return true;
        }

        return false;
    }


    /*
     * Overwrite in actual User Model implementation. Returns a signature like
     * Best Regards
     * Philipp
     *
     * which can be used to customize Emails etc.
     */
    public function getSignature() {
        return '';
    }
}