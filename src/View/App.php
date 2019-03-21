<?php

namespace PMRAtk\View;

class App extends \atk4\ui\App {

    use \PMRAtk\View\Traits\UserMessageTrait;

    public $deviceWidth;

    public $db;

    public $auth;

    public $userRolesMaySeeThisPage = [];

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
        $template = new \atk4\ui\Template();
        $template->app = $this;

        if ($t = $template->tryLoad($this->emailTemplateDir.'/'.$name)) {
            return $t;
        }

        throw new \atk4\data\Exception(['Can not find template file', 'name'=>$name, 'template_dir'=>$this->template_dir]);
    }


    /*
     * Adds Js and CSS needed for summernote Textareas
     */
    public function addSummernote() {
        $this->requireJS('js/summernote-lite.js');
        $this->requireJS('js/lang/summernote-de-DE.js');
        $this->requireCSS('css/summernote-lite-bs3-libre.css');
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
}