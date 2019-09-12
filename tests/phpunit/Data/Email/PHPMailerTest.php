<?php

class PHPMailerTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testAddUUID() {
        $this->_addStandardEmailAccount();
        $tt = new \PMRAtk\Data\Email\PHPMailer(self::$app);
        $_ENV['IS_TEST_MODE'] = true;
        $_ENV['TEST_EMAIL_UUID'] = 'DUDUDU';
        $this->assertFalse($tt->send());
    }


    /*
     *
     */
    public function testCustomEmailAccount() {
        $this->_addStandardEmailAccount();
        $pm = new \PMRAtk\Data\Email\PHPMailer(self::$app);
        self::assertEquals($pm->Host, EMAIL_HOST);

        $ea = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea->set('name',        'DUDU');
        $ea->set('sender_name', 'DUDU');
        $ea->set('user',        'DUDU');
        $ea->set('password',    'DUDU');
        $ea->set('smtp_host',   'DUDU');
        $ea->set('smtp_port',   'DUDU');
        $ea->set('imap_host',   'DUDU');
        $ea->set('imap_port',   'DUDU');
        $ea->set('imap_sent_folder', 'DUDU');
        $ea->save();

        $pm = new \PMRAtk\Data\Email\PHPMailer(self::$app, ['emailAccount' => $ea]);
        self::assertEquals('DUDU', $pm->Host);
    }


    /*
     *
     */
    public function testCustomEmailAccountById() {
        $this->_addStandardEmailAccount();
        $pm = new \PMRAtk\Data\Email\PHPMailer(self::$app);
        self::assertEquals($pm->Host, EMAIL_HOST);

        $ea = new \PMRAtk\Data\Email\EmailAccount(self::$app->db);
        $ea->set('name',        'DUDU');
        $ea->set('sender_name', 'DUDU');
        $ea->set('user',        'DUDU');
        $ea->set('password',    'DUDU');
        $ea->set('smtp_host',   'DUDU');
        $ea->set('smtp_port',   'DUDU');
        $ea->set('imap_host',   'DUDU');
        $ea->set('imap_port',   'DUDU');
        $ea->set('imap_sent_folder', 'DUDU');
        $ea->save();

        $pm = new \PMRAtk\Data\Email\PHPMailer(self::$app, ['emailAccount' => $ea->get('id')]);
        self::assertEquals('DUDU', $pm->Host);
    }
}
