<?php declare(strict_types=1);

namespace PMRAtk\Data\Cron;

/*
 * This class is meant as a Base to extend from for all Cronjobs.
 * Implement execute() in child cronjobs with all the Logic inside.
 * Automatically sends a Success Email if any Message was set to APP by Data layer.
 * Sends an Email if any exception was thrown.
 */

use atk4\core\AppScopeTrait;
use atk4\core\DIContainerTrait;
use atk4\data\Exception;
use atk4\ui\App;
use PMRAtk\Data\Email\EmailThrowableToAdminTrait;
use PMRAtk\Data\Email\PHPMailer;
use ReflectionClass;
use Throwable;

abstract class BaseCronJob {

    use DIContainerTrait;
    use EmailThrowableToAdminTrait;
    use AppScopeTrait;

    //The name of the cronjob to display to a user
    public $name = '';

    //some description explaining what the cron is doing
    public $description = '';

    //to all these recipients the success/fail email will be sent.
    //Plain Email Addresses go in here
    public $recipients = [];

    //should Admin also recieve success email?
    public $addAdminToSuccessEmail = false;

    public $phpMailer;

    //indicates if the cronjob was successful
    public $successful = false;


    /**
     *
     */
    public function __construct(App $app, array $defaults = []) {
        $this->app = $app;
        $this->setDefaults($defaults);
        $this->phpMailer = new PHPMailer($this->app);
    }


    /**
     *
     */
    final public function execute() {
        //make sure execute exists, otherwise throw exception
        if (!method_exists($this, '_execute')) {
            throw new Exception('_execute needs to ne implemented in descendants of ' . __CLASS__);
        }
        //try complete cronjob logic, exception leads to fail email to admin
        try {
            $this->_execute();
            $this->successful = true;
            echo 'Cronjob ' . $this->getName() . ' successful';
            $this->sendSuccessEmail();
        } catch (Throwable $e) {
            $this->sendErrorEmailToAdmin($e, 'Im Cronjob ' . $this->getName() . ' ist ein Fehler aufgetreten');
        }
    }


    /**
     *
     */
    public function getName() {
        if (empty($this->name)) {
            return (new ReflectionClass($this))->getShortName();
        }
        return $this->name;
    }


    /**
     * sends an email if messages were set
     */
    public function sendSuccessEmail() {
        //no messages to send?
        if (empty($this->app->getUserMessagesAsHTML())) {
            return;
        }
        //no recipients?
        if (!$this->recipients && !$this->addAdminToSuccessEmail) {
            return;
        }

        foreach ($this->recipients as $email_address) {
            $this->phpMailer->addAddress($email_address);
        }
        if ($this->addAdminToSuccessEmail) {
            $this->phpMailer->addAddress(TECH_ADMIN_EMAIL);
        }

        $this->phpMailer->Subject = 'Der Cronjob ' . $this->getName() . ' war erfolgreich';
        $this->phpMailer->setBody('Folgende Änderungen wurden durchgeführt: <br />' . $this->app->getUserMessagesAsHTML());
        $this->phpMailer->send();
    }
}