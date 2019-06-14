<?php

namespace PMRAtk\tests\phpunit;

/*
 * This class is used to load the last recieved message from an Email
 * account to check if its content matches what should have been sent.
 * Implemented due to reoccuring Email issues, especially that attachments
 * didn't match.
 *
 * Built on top of Zend Frameworks Zend Email
 */
class SentEmailContentTest extends TestCase {

    //instance of \PMRAtk\Data\Email\EmailAccount;
    public $emailAccount;

    public $loadedEmail;


    /*
     *
     */
    public function __construct(\PMRAtk\Data\Email\EmailAccount $ea) {
        $this->emailAccount = $ea;
        parent::__construct();
    }


    /*
     *
     */
    public function loadLastEmail() {
        $es = new Zend\Mail\Storage\Imap([
            'host'     => $this->emailAccount->get('imap_host'),
            'user'     => $this->emailAccount->get('user'),
            'password' => $this->emailAccount->get('password'),
            'port'     => $this->emailAccount->get('imap_port'),
        ]);

        $this->loadedEmail = $es->getMessage($es->countMessages());
    }
}