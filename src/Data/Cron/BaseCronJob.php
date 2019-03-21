<?php

namespace PMRAtk\Data\Cron;

/*
 * This class is meant as a Base to extend from for all Cronjobs.
 * Implement execute() in child cronjobs with all the Logic inside.
 * Automatically sends a Success Email if any Message was set to APP by Data layer.
 * Sends an Email if any exception was thrown.
 */
abstract class BaseCronJob {

    use \atk4\core\DIContainerTrait;

    //The name of the cronjob to display to a user
    public $name = 'Cronjob';
    //usually \EOO\View\CronjobApp instance
    public $app;
    //to all these recipients the success/fail email will be sent.
    //Plain Email Addresses go in here
    public $recipients = [];
    //should Admin also recieve success email?
    public $addAdminToSuccessEmail = false;

    public $phpMailer;
    //indicates if the cronjob was successful
    public $successful = false;


    /*
     *
     */
     public function __construct(\PMRAtk\View\App $app, array $defaults = []) {
        $this->setDefaults($defaults);
        $this->app = $app;
        $this->phpMailer = new \PMRAtk\Data\PHPMailer($this->app);
        //make sure execute exists, otherwise throw exception
        if(!method_exists($this, 'execute')) {
            throw new \atk4\data\Exception(__FUNCTION__.' needs to ne implemented in descendants of '.__CLASS__);
        }
        //try complete cronjob logic, exception leads to fail email to admin
        try {
            $this->execute();
            $this->successful = true;
            $this->sendSuccessEmail();
        }
        catch(\Exception $e) {
            $this->sendFailEmail($e);
        }
    }


    /*
     * sends an email if messages were set
     */
    public function sendSuccessEmail() {
        //no messages to send?
        if(!$this->app->getUserMessagesAsHTML()) {
            return;
        }
        //no recipients?
        if(!$this->recipients && !$this->addAdminToSuccessEmail) {
            return;
        }

        foreach($this->recipients as $email_address) {
            $this->phpMailer->addAddress($email_address);
        }
        if($this->addAdminToSuccessEmail) {
            $this->phpMailer->addAddress(TECH_ADMIN_EMAIL);
        }

        $this->phpMailer->Subject = 'Der Cronjob '. $this->name.' war erfolgreich';
        $this->phpMailer->setBody('Folgende Änderungen wurden durchgeführt: <br />'.$this->app->getUserMessagesAsHTML());
        $this->phpMailer->send();
    }


    /*
     * Sends an Email if an Exception was thrown
     */
    public function sendFailEmail(\Exception $e) {
        //always send to tech admin
        $this->phpMailer->addAddress(TECH_ADMIN_EMAIL);
        foreach($this->recipients as $email_address) {
            $this->phpMailer->addAddress($email_address);
        }
        $this->phpMailer->Subject = 'Im Cronjob '. $this->name.' ist ein Fehler aufgetreten';
        $this->phpMailer->setBody('Folgender Fehler ist aufgetreten: <br />'.
            ($e instanceOf \atk4\core\Exception ? $e->getHTML() : $e->getMessage()).'<br />Der Technische Administrator '.TECH_ADMIN_NAME.' wurde informiert.');
        $this->phpMailer->send();
    }
}