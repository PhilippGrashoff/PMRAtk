<?php
namespace PMRAtk\Data\Email;

class PHPMailer extends \PHPMailer\PHPMailer\PHPMailer {

    use \atk4\core\DIContainerTrait;

    public $app;
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
        $this->Host = $this->app->getSetting('EMAIL_HOST');
        $this->Port = $this->app->getSetting('EMAIL_PORT');
        $this->SMTPAuth = true;
        $this->Username = $this->app->getSetting('EMAIL_USERNAME');
        $this->Password = $this->app->getSetting('EMAIL_PASSWORD');
        $this->setFrom($this->app->getSetting('STD_EMAIL'), $this->app->getSetting('STD_EMAIL_NAME'));

        parent::__construct();

        $this->header = $this->app->loadEmailTemplate($this->headerTemplate);
        $this->footer = $this->app->loadEmailTemplate($this->footerTemplate);
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
}
