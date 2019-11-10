<?php

namespace PMRAtk\View;

use atk4\ui\Exception;
use atk4\ui\Template;

class App extends \atk4\ui\App {

    use \PMRAtk\View\Traits\UserMessageTrait;

    //used to determine which layout to use
    public $deviceWidth;

    //atk data Persistence
    public $db;

    //atk login Auth
    public $auth;

    //array of user roles which may see the requested page. Checked in __construct
    public $userRolesMaySeeThisPage = [];

    //most settings are now stored in database. The Settings are loaded once and stored in _settings array
    protected $_settings = [];
    protected $_settingsLoaded = false;

    //models which are requested often during a single script can be loaded once in here and re-used. Make sure its
    //read-only usage otherwise caching may cause trouble
    protected $_cachedModels = [];

    //if API uses App, it sets this property to true
    public $isApiRequest = false;

    //the dir in which the email templates are stored
    public $emailTemplateDir = 'template/email';


    /*
     *
     */
    public function __construct(array $user_roles_may_see = [], array $defaults = []) {
        parent::__construct($defaults);

        //set user rights, only these roles may see this page
        if(count($user_roles_may_see) === 0) {
            throw new \atk4\data\Exception('User rights array must always be passed to constructor of '.__CLASS__);
        }
        $this->userRolesMaySeeThisPage = $user_roles_may_see;

        //unfortunately, a layout needs to be initialized for dbconnect
        $this->initLayout(new \atk4\ui\View());

        //Database Connection
        $this->dbConnect(DB_STRING, DB_USER, DB_PASSWORD);

        //try to know device width; used in some views
        $this->getDeviceWidth();

        //set date format etc
        $this->setPersistenceFormat();

        //load custom Js and CSS
        $this->requireCustomJSAndCSS();

        //Add auth class
        $this->_addAuth();
    }


    /*
     * This function needs to be implemented in Child class
     */
    protected function _addAuth() {
        $this->auth = new \atk4\login\Auth(['check' => false]);
        $this->auth->setModel(new \PMRAtk\Data\User($this->db), 'username', 'password');
    }


    /*
     * add js and css that exceeds standard atk js&css here
     */
    public function requireCustomJSAndCSS() {

    }


    /*
     * tries to log in user by ApiToken
     */
    public function loadUserByToken(string $token_string) {
        $token = new \PMRAtk\Data\Token($this->db);
        $token->tryLoadBy('value', $token_string);
        if(!$token->loaded()) {
            throw new \atk4\data\Exception('Token could not be found');
        }
        if(!$user = $token->getParentObject()) {
            throw new \atk4\data\Exception('No matching User for Token found');
        }

        $this->auth->user = $user;
    }


    /*
     * set Date format to german
     */
    public function setPersistenceFormat() {
        $this->ui_persistence->date_format = 'Y-m-d';
        $this->ui_persistence->time_format = 'H:i';
        $this->ui_persistence->datetime_format = 'Y-m-d\TH:i:s';
        $this->ui_persistence->currency = '';
    }


    /**
     * overwrite standard atk method to return \PMRAtk\View\Template
     */
    public function loadTemplate($name){
        $template = new \PMRAtk\View\Template();
        $template->app = $this;

        if (in_array($name[0], ['.', '/', '\\']) || strpos($name, ':\\') !== false) {
            return $template->load($name);
        } else {
            $dir = is_array($this->template_dir) ? $this->template_dir : [$this->template_dir];
            foreach ($dir as $td) {
                if ($t = $template->tryLoad($td.'/'.$name)) {
                    return $t;
                }
            }
        }

        throw new \atk4\ui\Exception(['Can not find template file', 'name'=>$name, 'template_dir'=>$this->template_dir]);
    }


    /*
     *
     */
    protected function _getCustomTemplateFromModel(string $name, array $customFromModels):?\PMRAtk\Data\Email\EmailTemplate {
        $et = new \PMRAtk\Data\Email\EmailTemplate($this->db);
        foreach($customFromModels as $model) {
            if(!$model->loaded()) {
                throw new atk4\data\Exception('Model needs to be loaded in '.__FUNCTION__);
            }
            $et->addCondition('model_class', get_class($model));
            $et->addCondition('model_id', $model->get('id'));
            $et->tryLoadBy('ident', $name);
            if($et->loaded()) {
                return clone $et;
            }
        }

        return null;
    }

    /*
     * email templates get an extra function to load to distinguish
     * from HTML element templates
     */
    public function loadEmailTemplate(string $name, bool $raw_template = false, array $customFromModels = []) {
        $template = new \PMRAtk\View\Template();
        $template->app = $this;
        //try to load From EmailTemplate per Model
        $et = $this->_getCustomTemplateFromModel($name, $customFromModels);
        //else try to load from DB
        if(!$et) {
            $et = new \PMRAtk\Data\Email\EmailTemplate($this->db);
            $et->addCondition('model_class', null);
            $et->addCondition('model_id', null);
            $et->tryLoadBy('ident', $name);
        }

        if($et->loaded()) {
            if($raw_template) {
                return $et->get('value');
            }
            else {
                $template->loadTemplateFromString($et->get('value'));
                return $template;
            }
        }

        //now try to load from file
        if(file_exists(FILE_BASE_PATH.$this->emailTemplateDir.'/'.$name)) {
            if($raw_template) {
                return file_get_contents(FILE_BASE_PATH.$this->emailTemplateDir.'/'.$name);
            }
            elseif($t = $template->tryLoad(FILE_BASE_PATH.$this->emailTemplateDir.'/'.$name)) {
                return $t;
            }
        }

        throw new \atk4\data\Exception(['Can not find email template file', 'name' => $name, 'template_dir' => $this->emailTemplateDir]);
    }


    /*
     * Save a setting into Settings table
     */
    public function saveEmailTemplate(string $ident, string $value, string $model_class = '', $model_id = null) {
        $et = new \PMRAtk\Data\Email\EmailTemplate($this->db);
        if($model_class && $model_id) {
            $et->addCondition('model_class', $model_class);
            $et->addCondition('model_id', $model_id);
        }
        $et->tryLoadBy('ident', $ident);
        if(!$et->loaded()) {
            $et->set('ident', $ident);
        }
        $et->set('value', $value);
        if($model_class && $model_id) {
            $et->set('model_class', $model_class);
            $et->set('model_id', $model_id);
        }
        $et->save();
    }


    /*
     * Adds Js and CSS needed for summernote Textareas
     */
    public function addSummernote() {
        $this->requireJS(URL_BASE_PATH.'js/summernote-lite.js');
        $this->requireJS(URL_BASE_PATH.'js/lang/summernote-de-DE.js');
        $this->requireCSS(URL_BASE_PATH.'css/summernote-lite-bs3-libre.css');
    }


    /*
     * Sets $this->deviceWidth to a value found in $_POST. Typically, my forms
     * contain a hidden field 'device_width' which is used to send device width
     * to the server.
     */
    public function getDeviceWidth() {
        if(isset($_POST['device_width']) && intval($_POST['device_width']) > 0) {
            $this->deviceWidth = intval($_POST['device_width']);
            $_SESSION['device_width'] = $this->deviceWidth;
        }
        elseif(isset($_SESSION['device_width'])) {
            $this->deviceWidth = $_SESSION['device_width'];
        }
    }


    /*
     * Function to load all saved settings into the app as they are needed often
     * during a single request
     */
    public function getSetting(string $ident) {
        //load settings once
        $this->_loadSettings();

        if(isset($this->_settings[$ident])) {
            return $this->_settings[$ident];
        }
        elseif(isset($_ENV[$ident])) {
            return $_ENV[$ident];
        }
        elseif(defined($ident)) {
            return constant($ident);
        }

        return null;
    }


    /*
     *
     */
    protected function _loadSettings() {
        if($this->_settingsLoaded) {
            return;
        }
        foreach(new \PMRAtk\Data\Setting($this->db) as $m) {
            $this->_settings[$m->get('ident')] = $m->get('value');
        }
        $this->_settingsLoaded = true;
    }


    /*
     * Force reload of settings
     */
    public function unloadSettings() {
        $this->_settings = [];
        $this->_settingsLoaded = false;
    }


    /*
     * returns all STD_ settings, which are typically used in templates
     */
    public function getAllSTDSettings():array {
        $return = [];
        $this->_loadSettings();

        foreach($this->_settings as $key => $value) {
            if(substr($key, 0, 4) == 'STD_') {
                $return[$key] = $value;
            }
        }
        return $return;
    }


    /*
     *
     */
    public function settingExists(string $ident):bool {
        $this->_loadSettings();
        if(array_key_exists($ident, $this->_settings)) {
            return true;
        }
        return false;
    }


    /*
     * For "installers": Add a setting if it does not exist yet
     */
    public function addSetting(\PMRAtk\Data\Setting $s) {
        $this->_loadSettings();
        if(!array_key_exists($s->get('ident'), $this->_settings)) {
            $s->save();
            $this->_settingsLoaded = false;
        }
    }


    /*
     * Can be used to overwrite a setting, mostly for tests
     */
    public function setSetting(\PMRAtk\Data\Setting $s) {
        $s->save();
        $this->_settingsLoaded = false;
        $this->_loadSettings();
    }


    /*
     * get a cached model. Cached means within the same request. If Model
     * wanst cached yet, load, else return cached value
     */
    public function getCachedModel(string $model_name):array {
        if(!class_exists($model_name)) {
            throw new \atk4\data\Exception('Class '.$model_name.' does not exist in '.__FUNCTION__);
        }

        //if isset already, return that
        if(isset($this->_cachedModels[$model_name])) {
            return $this->_cachedModels[$model_name];
        }

        $model = new $model_name($this->db);
        $a = [];
        foreach($model as $m) {
            $a[$m->id] = clone $m;
        }

        $this->_cachedModels[$model_name] = $a;
        return $this->_cachedModels[$model_name];
    }


    /*
     * sends an email to EOO owner. The template is not editable in this case. Meant for short Emails like notifications
     * as Email and so on
     */
    public function sendEmailToAdmin(string $subject, string $message_template, array $set_to_template = [], array $from_models = []) {
        $email = new \PMRAtk\Data\Email\BaseEmail($this->db, ['addUserMessageOnSend' => false, 'template' => $message_template]);
        $email->processMessageTemplate = function($template) use ($set_to_template, $from_models) {
            foreach($set_to_template as $tag => $value) {
                $template->set($tag, $value);
            }
            foreach($from_models as $model) {
                $template->setTagsFromModel($model, [], strtolower((new \ReflectionClass($model))->getShortName()).'_');
            }
        };
        $email->loadInitialTemplate();
        $email->set('subject', $subject);
        $email->addRecipient($this->app->getSetting('STD_EMAIL'));
        $email->send();

        return $email;
    }
}