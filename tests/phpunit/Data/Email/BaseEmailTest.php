<?php

class Signature extends \PMRAtk\Data\User {
    public function getSignature() {
        return 'TestSignature';
    }
}


class EditPerRecipient extends \PMRAtk\Data\Email\BaseEmail {
    public function loadInitialTemplate() {
        $this->set('subject', 'Bla{$testsubject}');
        $this->set('message', 'Bla{$testbody}');
    }
}


class BaseEmailTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * tests the addRecipient and removeRecipient Function passing various params
     */
    public function testAddRecipient() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->save();

        //pass a Guide, should have an email set
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->set('firstname', 'Lala');
        $g->set('lastname', 'Dusu');
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($g));
        $this->assertEquals(1, $base_email->ref('EmailRecipient')->action('count')->getOne());

        //adding the same guide again shouldnt change anything
        $this->assertFalse($base_email->addRecipient($g));
        $this->assertEquals(1, $base_email->ref('EmailRecipient')->action('count')->getOne());

        //pass a non-loaded Guide
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->assertFalse($base_email->addRecipient($g));

        //pass a Guide without an existing Email
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $this->assertFalse($base_email->addRecipient($g));

        //pass an email id
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $e = $g->addEmail('test3@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($e->get('id')));
        $this->assertEquals(2, $base_email->ref('EmailRecipient')->action('count')->getOne());

        //pass a non existing email id
        $this->assertFalse($base_email->addRecipient(111111));

        //pass existing email id that does not belong to any parent model
        $e = new \PMRAtk\Data\Email(self::$app->db);
        $e->set('value', 'test1@easyoutdooroffice.com');
        $e->save();
        $this->assertFalse($base_email->addRecipient($e->get('id')));


        //pass a valid Email
        $this->assertTrue($base_email->addRecipient('philipp@spame.de'));
        $this->assertEquals(3, $base_email->ref('EmailRecipient')->action('count')->getOne());

        //pass an invalid email
        $this->assertFalse($base_email->addRecipient('hannsedfsgs'));

        //now remove all
        foreach($base_email->ref('EmailRecipient') as $rec) {
            $this->assertTrue($base_email->removeRecipient($rec->get('id')));
        }
        $this->assertEquals(0, $base_email->ref('EmailRecipient')->action('count')->getOne());

        //remove some non_existing EmailRecipient
        $this->assertFalse($base_email->removeRecipient('11111'));

        //test adding not the first, but some other email
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $test2_id = $g->addEmail('test2@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($g, $test2_id->get('id')));
        //now there should be a single recipient and its email should be test2...
        foreach($base_email->ref('EmailRecipient') as $rec) {
            $this->assertEquals($rec->get('email'), 'test2@easyoutdooroffice.com');
        }
    }


    /*
     * tests send function
     */
    public function testSend() {
        //no recipients, should return false
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $this->assertFalse($base_email->send());

        //one recipient, should return true
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->set('subject', 'Hello from PHPUnit');
        $base_email->set('message', 'Hello from PHPUnit');
        $this->assertTrue($base_email->addRecipient('test2@easyoutdooroffice.com'));
        $this->assertTrue($base_email->send());
    }


    /*
     *
     */
    public function testloadInitialValues() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->loadInitialValues();
        $this->assertTrue(true);
    }


    /*
     *
     */
    public function testEmailRecipientsDeletedOnDelete() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->save();

        $initial_count = (new \PMRAtk\Data\Email\EmailRecipient(self::$app->db))->action('count')->getOne();

        //pass a Guide, should have an email set
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->set('firstname', 'Lala');
        $g->set('lastname', 'Dusu');
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($g));
        $this->assertEquals(1, $base_email->ref('EmailRecipient')->action('count')->getOne());

        $base_email->delete();
        $this->assertEquals($initial_count, (new \PMRAtk\Data\Email\EmailRecipient(self::$app->db))->action('count')->getOne());
    }


    /*
     *
     */
    public function testAttachments() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->save();
        $file = $this->createTestFile('test.jpg');
        $base_email->addAttachment($file->get('id'));
        $this->assertEquals(1, count($base_email->get('attachments')));

        $base_email->removeAttachment($file->get('id'));
        $this->assertEquals(0, count($base_email->get('attachments')));
    }


    /*
     *
     */
    public function testSendAttachments() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->save();
        $file = $this->createTestFile('test.jpg');
        $base_email->addAttachment($file->get('id'));
        $this->assertTrue($base_email->addRecipient('test1@easyoutdooroffice.com'));
        $this->assertTrue($base_email->send());
    }


    /*
     *
     */
    public function testInitialTemplateLoading() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => 'tests/testemailtemplate.html']);
        $base_email->loadInitialValues();
        $this->assertEquals($base_email->get('subject'), 'TestBetreff');
        $this->assertTrue(strpos($base_email->get('message'), 'TestInhalt') !== false);
    }


    /*
     *
     */
    public function testInitialTemplateLoadingByString() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}Hellow{/Subject}Magada']);
        $base_email->loadInitialValues();
        $this->assertEquals($base_email->get('subject'), 'Hellow');
        $this->assertTrue(strpos($base_email->get('message'), 'Magada') !== false);
    }


    /*
     *
     */
    public function testLoadSignatureByUserSignature() {
        $initial = self::$app->auth->user;
        self::$app->auth->user = new Signature(self::$app->db);
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}Hellow{/Subject}Magada{Signature}{/Signature}']);
        $base_email->loadInitialValues();
        $this->assertTrue(strpos($base_email->get('message'), 'TestSignature') !== false);
        self::$app->auth->user = $initial;
    }


    /*
     *
     */
    public function testloadSignatureBySetting() {
        $_ENV['STD_EMAIL_SIGNATURE'] = 'TestSigSetting';
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}Hellow{/Subject}Magada{Signature}{/Signature}']);
        $base_email->loadInitialValues();
        $this->assertTrue(strpos($base_email->get('message'), 'TestSigSetting') !== false);
    }


    /*
     *
     */
    public function testSMTPKeepAlive() {
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}TestMoreThanOneRecipient{/Subject}TestMoreThanOneRecipient{Signature}{/Signature}']);
        $base_email->loadInitialValues();
        $base_email->save();
        $this->assertTrue($base_email->addRecipient('test1@easyoutdooroffice.com'));
        $this->assertTrue($base_email->addRecipient('test2@easyoutdooroffice.com'));
        $base_email->send();
    }


    /*
     *
     */
    public function testProcessSubjectAndMessage() {
        $base_email = new EditPerRecipient(self::$app->db, ['template' => '{Subject}BlaDu{$testsubject}{/Subject}BlaDu{$testbody}']);
        $base_email->loadInitialValues();
        $base_email->processSubjectPerRecipient = function($recipient, $template) {
            $template->set('testsubject', 'HARALD');
        };
        $base_email->processMessagePerRecipient = function($recipient, $template) {
            $template->set('testbody', 'MARTOR');
        };
        $base_email->addRecipient('test1@easyoutdooroffice.com');
        $this->assertTrue($base_email->send());
        $this->assertTrue(strpos($base_email->phpMailer->getSentMIMEMessage(), 'HARALD') !== false);
        $this->assertTrue(strpos($base_email->phpMailer->getSentMIMEMessage(), 'MARTOR') !== false);
    }
}
