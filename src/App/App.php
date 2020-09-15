<?php declare(strict_types=1);

namespace PMRAtk\App;

use atk4\login\Auth;
use PMRAtk\Data\Email\BaseEmail;
use PMRAtk\Data\Email\EmailTemplate;
use PMRAtk\Data\PersistenceWithApp;
use PMRAtk\Data\Token;
use PMRAtk\Data\User;
use PMRAtk\View\Traits\UserMessageTrait;
use ReflectionClass;
use PMRAtk\View\Template;
use PMRAtk\Data\Traits\SettingsTrait;
use atk4\data\Exception;


class App extends \atk4\ui\App
{

    use SettingsTrait;
    use UserMessageTrait;

    //should audits be created? Disabled e.g. for speeding up tests
    public bool $createAudit = true;
    //should notifications be created? Disabled e.g. for speeding up tests
    public bool $createNotification = true;
    //indicates if phpunit tests are running.
    public bool $isTestMode = false;

    //used to determine which layout to use
    public $deviceWidth;

    //atk data Persistence
    public $db;

    //atk login Auth
    public $auth;
    //array of user roles which may see the requested page. Checked in __construct
    public $userRolesMaySeeThisPage = [];

    //if Api uses App, it sets this property to true
    public $isApiRequest = false;

    //the dir in which the email templates are stored
    public $emailTemplateDir = 'template/email';
    //overwrite standard ATK Template seed
    public $templateClass = Template::class;


    /**
     *
     */
    public function __construct(array $user_roles_may_see = [], array $defaults = [])
    {
        parent::__construct($defaults);

        //set user rights, only these roles may see this page
        if (count($user_roles_may_see) === 0) {
            throw new \atk4\data\Exception('User rights array must always be passed to constructor of ' . __CLASS__);
        }
        $this->userRolesMaySeeThisPage = $user_roles_may_see;

        //Database Connection
        $this->db = PersistenceWithApp::connect(DB_STRING, DB_USER, DB_PASSWORD);
        $this->db->app = $this;

        //try to know device width; used in some views
        $this->getDeviceWidth();

        //set date format etc
        $this->setPersistenceFormat();

        //load custom Js and CSS
        $this->requireCustomJSAndCSS();

        //Add auth class
        $this->_addAuth();
    }


    /**
     * This function needs to be implemented in Child class
     */
    protected function _addAuth()
    {
        /*TODO DISABLED FOR NOW UNTIL ATK LOGIN IS 2.2$this->auth = new Auth(['check' => false]);
        $this->auth->setModel(new User($this->db), 'username', 'password');*/
    }


    /**
     * add js and css that exceeds standard atk js&css here
     */
    public function requireCustomJSAndCSS()
    {
    }


    /**
     * tries to log in user by ApiToken
     */
    public function loadUserByToken(string $token_string)
    {
        $token = new Token($this->db);
        $token->tryLoadBy('value', $token_string);
        if (!$token->loaded()) {
            throw new Exception('Token could not be found', 401);
        }
        $user = $token->getParentObject();
        if (!$user || !$user instanceof User) {
            throw new Exception('No matching User for Token found', 403);
        }

        $this->auth->user = $user;
    }


    /**
     * set Date format to german
     */
    public function setPersistenceFormat()
    {
        $this->ui_persistence->date_format = 'Y-m-d';
        $this->ui_persistence->time_format = 'H:i';
        $this->ui_persistence->datetime_format = 'Y-m-d\TH:i:s';
        $this->ui_persistence->currency = '';
    }



    /**
     * email templates get an extra function to load to distinguish from HTML element templates
     */
    public function loadEmailTemplate(string $name, bool $raw_template = false, array $customFromModels = [])
    {
        $template = new Template();
        $template->app = $this;
        //try to load From EmailTemplate per Model
        $et = $this->_getCustomEmailTemplateFromModel($name, $customFromModels);
        //else try to load from DB
        if (!$et) {
            $et = new EmailTemplate($this->db);
            $et->addCondition('model_class', null);
            $et->addCondition('model_id', null);
            $et->tryLoadBy('ident', $name);
        }

        if ($et->loaded()) {
            if ($raw_template) {
                return $et->get('value');
            } else {
                $template->loadTemplateFromString($et->get('value'));
                return $template;
            }
        }

        //now try to load from file
        $fileName = FILE_BASE_PATH . $this->emailTemplateDir . '/' . $name;
        if (file_exists($fileName)) {
            if ($raw_template) {
                return file_get_contents($fileName);
            } elseif ($t = $template->tryLoad($fileName)) {
                return $t;
            }
        }

        throw new Exception('Can not find email template file: ' . $name);
    }

    protected function _getCustomEmailTemplateFromModel(string $name, array $customFromModels): ?EmailTemplate
    {
        foreach ($customFromModels as $model) {
            if (!$model->loaded()) {
                throw new Exception('Model needs to be loaded in ' . __FUNCTION__);
            }
            $et = new EmailTemplate($this->db);
            $et->addCondition('model_class', get_class($model));
            $et->addCondition('model_id', $model->get('id'));
            $et->tryLoadBy('ident', $name);
            if ($et->loaded()) {
                return clone $et;
            }
        }

        return null;
    }

    /**
     * Adds Js and CSS needed for summernote Textareas
     */
    public function addSummernote()
    {
        $this->requireJS(URL_BASE_PATH . 'js/summernote-lite.js');
        $this->requireJS(URL_BASE_PATH . 'js/lang/summernote-de-DE.js');
        $this->requireCSS(URL_BASE_PATH . 'css/summernote-lite-bs3-libre.css');
    }


    /**
     * Sets $this->deviceWidth to a value found in $_POST. Typically, my forms
     * contain a hidden field 'device_width' which is used to send device width
     * to the server.
     */
    public function getDeviceWidth()
    {
        if (
            isset($_POST['device_width'])
            && intval($_POST['device_width']) > 0
        ) {
            $this->deviceWidth = intval($_POST['device_width']);
            $_SESSION['device_width'] = $this->deviceWidth;
        } elseif (isset($_SESSION['device_width'])) {
            $this->deviceWidth = $_SESSION['device_width'];
        }
    }




    /**
     * sends an email to EOO owner. The template is not editable in this case. Meant for short Emails like notifications
     * as Email and so on
     */
    public function sendEmailToAdmin(
        string $subject,
        string $message_template,
        array $set_to_template = [],
        array $from_models = []
    ) {
        $email = new BaseEmail($this->db, ['addUserMessageOnSend' => false, 'template' => $message_template]);
        $email->processMessageTemplate = function ($template) use ($set_to_template, $from_models) {
            foreach ($set_to_template as $tag => $value) {
                $template->set($tag, $value);
            }
            foreach ($from_models as $model) {
                $template->setTagsFromModel(
                    $model,
                    [],
                    strtolower((new ReflectionClass($model))->getShortName()) . '_'
                );
            }
        };
        $email->loadInitialTemplate();
        $email->set('subject', $subject);
        $email->addRecipient(CUSTOMER_ADMIN_EMAIL);
        $email->send();

        return $email;
    }
}