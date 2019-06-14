<?php

namespace PMRAtk\tests;

/*
 * This class is used to load the last recieved message from an Email
 * account to check if its content matches what should have been sent.
 * Implemented due to reoccuring Email issues, especially that attachments
 * didn't match.
 *
 * Built on top of Zend Frameworks Zend Email
 */
class SentEmailContentTest extends \PMRAtk\tests\phpunit\TestCase {

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
    public function loadLastEmailFromFolder(string $folder) {
        $es = new Zend\Mail\Storage\Imap([
            'host'     => $this->emailAccount->get('imap_host'),
            'user'     => $this->emailAccount->get('user'),
            'password' => $this->emailAccount->get('password'),
            'port'     => $this->emailAccount->get('imap_port'),
            'folder'   => $folder,
        ]);

        $this->loadedEmail = $es->getMessage($es->countMessages());

        return $this;
    }


    /*
     *
     */
    public function assertSubjectContains(string $search) {
        $this->assertTrue(strpos($this->loadedEmail->subject, $search) !== false);
    }


    /*
     *
     */
    public function assertBodyContains(string $search) {
        $this->assertTrue(strpos($this->loadedEmail->getContent(), $search) !== false);
    }
}