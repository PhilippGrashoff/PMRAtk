<?php

namespace PMRAtk\View;

class App extends \atk4\ui\App {

    use \PMRAtk\View\Traits\UserMessageTrait;

    public $deviceWidth;

    public $db;

    public $auth;

    public $userRolesMaySeeThisPage = [];

    protected $settings     = [];

    protected $cachedValues = [];

    public $isApiRequest = false;

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


    /*
     * email templates get an extra function to load to distinguish
     * from HTML element templates
     */
    public function loadEmailTemplate(string $name) {
        $template = new \PMRAtk\View\Template();
        $template->app = $this;

        if ($t = $template->tryLoad(FILE_BASE_PATH.$this->emailTemplateDir.'/'.$name)) {
            return $t;
        }

        throw new \atk4\data\Exception(['Can not find email template file', 'name' => $name, 'template_dir' => $this->emailTemplateDir]);
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
     * Function to load a setting from App. App is the central point
     * which both data and ui can access to get $_ENV etc settings
     * Using this function makes definition of the settings independent from
     * their definition, may it be $_ENV[], define() or stored in DB (preferred)
     */
    public function getSetting(string $ident) {
        if(isset($this->settings[$ident])) {
            return $this->settings[$ident];
        }
        elseif(isset($_ENV[$ident])) {
            return $_ENV[$ident];
        }
        elseif(defined($ident)) {
            return constant($ident);
        }
    }


    /*
     * returns all STD_ settings
     * TODO: Implement properly when implementing Settings class in PMRAtk
     */
    public function getAllSTDSettings():array {
        if(defined('STD_SET_ARRAY')) {
            return STD_SET_ARRAY;
        }

        return [];
    }


    /*
     * Load a cached value by ident
     * a timeout in seconds can be defined after which the setting becomes invalid
     */
    public function getCachedValue(string $ident, int $timeout = 0) {
        if($timeout > 0) {
            if(isset($this->cachedValues[$ident])
            && $this->cachedValues[$ident]->get('last_updated') >= (new \DateTime())->modify('-'.$timeout.' Seconds')) {
                return $this->cachedValues[$ident]->get('value');
            }
        }
        elseif(isset($this->cachedValues[$ident])) {
            return $this->cachedValues[$ident]->get('value');
        }
    }


    /*
     * set a cached value in the App
     */
    public function setCachedValue(string $ident, string $value) {
        $s = new \PMRAtk\Data\CachedValue($this->db);
        $s->set('ident', $ident);
        $s->set('value', $value);
        $s->save();
        $this->cachedValues[$ident] = $s;
    }
}