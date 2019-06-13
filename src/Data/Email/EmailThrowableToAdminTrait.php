<?php

namespace PMRAtk\Data\Email;

trait EmailThrowableToAdminTrait {

    /*
     * Sends an Email if an Exception was thrown
     */
    public function sendErrorEmailToAdmin(\Throwable $e, string $subject, array $additional_recipients = []) {
        if(!$this->phpMailer) {
            $this->phpMailer = new \PMRAtk\Data\Email\PHPMailer($this->app);
        }
        //always send to tech admin
        $this->phpMailer->addAddress($this->app->getSetting('TECH_ADMIN_EMAIL'));
        foreach ($additional_recipients as $email_address) {
            $this->phpMailer->addAddress($email_address);
        }
        $this->phpMailer->Subject = $subject;
        $this->phpMailer->setBody('Folgender Fehler ist aufgetreten: <br />' .
            ($e instanceOf \atk4\core\Exception ? $e->getHTML() : $e->getMessage() . '<br />Line: ' . $e->getLine() . '<br />' . nl2br($e->getTraceAsString())) . '<br />Der Technische Administrator ' . $this->app->getSetting('TECH_ADMIN_NAME') . ' wurde informiert.');
        $this->phpMailer->send();
    }
}