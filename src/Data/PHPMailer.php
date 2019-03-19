<?php
namespace PMRAtk\Data;

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
        $this->Host = EMAIL_HOST;
        $this->Port = EMAIL_PORT;
        $this->SMTPAuth = true;
        $this->Username = EMAIL_USERNAME;
        $this->Password = EMAIL_PASSWORD;
        $this->setFrom(STD_EMAIL, STD_EMAIL_NAME);

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
}
