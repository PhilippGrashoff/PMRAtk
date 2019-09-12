<?php
namespace PMRAtk\Data\Email;

class PHPMailer extends \PHPMailer\PHPMailer\PHPMailer {

    use \atk4\core\DIContainerTrait;
    use \atk4\core\AppScopeTrait;

    //the PMRAtk\Data\Email\EmailAccount to send from. If not set, use first one
    public $emailAccount;

    //header and footer which will be added to email before send
    public $header;
    public $footer;
    public $headerTemplate  = 'default_header.html';
    public $footerTemplate  = 'default_footer.html';



    /*
     *
     */
    public function __construct(\atk4\ui\App $app, array $defaults = []) {
        $this->app = $app;
        $this->setDefaults($defaults);
        $this->CharSet = 'utf-8';
        //set SMTP sending
        $this->isSMTP();
        $this->SMTPDebug = 0;
        $this->SMTPAuth = true;
        $this->_setEmailAccount();
        $this->Host = $this->emailAccount->get('smtp_host');
        $this->Port = $this->emailAccount->get('smtp_port');
        $this->Username = $this->emailAccount->get('user');
        $this->Password = $this->emailAccount->get('password');
        $this->setFrom($this->emailAccount->get('name'), $this->emailAccount->get('sender_name'));

        parent::__construct();

        $this->header = $this->app->loadEmailTemplate($this->headerTemplate);
        $this->header->setSTDValues();
        $this->footer = $this->app->loadEmailTemplate($this->footerTemplate);
        $this->footer->setSTDValues();
    }


    /*
     *
     */
    public function setBody(string $body) {
        $this->Body = $this->header->render().$body.$this->footer->render();
        $this->AltBody = $this->html2text($this->Body);
    }


    /*
     * for testing: add uuid to email subject if set
     */
    public function send():bool {
        if($this->app->getSetting('IS_TEST_MODE')
        && $this->app->getSetting('TEST_EMAIL_UUID')) {
            $this->Subject .= $this->app->getSetting('TEST_EMAIL_UUID');
        }

        return parent::send();
    }


    /*
     * load default EmailAccount if none is set
     */
    protected function _setEmailAccount() {
        if($this->emailAccount instanceof \PMRAtk\Data\Email\EmailAccount
        && $this->emailAccount->loaded()) {
            return;
        }
        //maybe just the ID of the emailaccount was passed?
        elseif(is_scalar($this->emailAccount)) {
            $val = $this->emailAccount;
            $this->emailAccount = new \PMRAtk\Data\Email\EmailAccount($this->app->db);
            if ($val) {
                $this->emailAccount->tryLoad($val);
                if ($this->emailAccount->loaded()) {
                    return;
                }
            }
        }

        //none found? load default
        $this->emailAccount = new \PMRAtk\Data\Email\EmailAccount($this->app->db);
        $this->emailAccount->tryLoadAny();
        if(!$this->emailAccount->loaded()) {
            throw new \atk4\core\Exception('No EmailAccount to send from found!');
        }
    }
}
