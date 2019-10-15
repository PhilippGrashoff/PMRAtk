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
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->save();

        //pass a Guide, should have an email set
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->set('firstname', 'Lala');
        $g->set('lastname', 'Dusu');
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($g));
        $this->assertEquals(1, $base_email->ref('email_recipient')->action('count')->getOne());

        //adding the same guide again shouldnt change anything
        $this->assertFalse($base_email->addRecipient($g));
        $this->assertEquals(1, $base_email->ref('email_recipient')->action('count')->getOne());

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
        $this->assertEquals(2, $base_email->ref('email_recipient')->action('count')->getOne());

        //pass a non existing email id
        $this->assertFalse($base_email->addRecipient(111111));

        //pass existing email id that does not belong to any parent model
        $e = new \PMRAtk\Data\Email(self::$app->db);
        $e->set('value', 'test1@easyoutdooroffice.com');
        $e->save();
        $this->assertFalse($base_email->addRecipient($e->get('id')));


        //pass a valid Email
        $this->assertTrue($base_email->addRecipient('philipp@spame.de'));
        $this->assertEquals(3, $base_email->ref('email_recipient')->action('count')->getOne());

        //pass an invalid email
        $this->assertFalse($base_email->addRecipient('hannsedfsgs'));

        //now remove all
        foreach($base_email->ref('email_recipient') as $rec) {
            $this->assertTrue($base_email->removeRecipient($rec->get('id')));
        }
        $this->assertEquals(0, $base_email->ref('email_recipient')->action('count')->getOne());

        //remove some non_existing EmailRecipient
        $this->assertFalse($base_email->removeRecipient('11111'));

        //test adding not the first, but some other email
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $test2_id = $g->addEmail('test2@easyoutdooroffice.com');
        $this->assertTrue($base_email->addRecipient($g, $test2_id->get('id')));
        //now there should be a single recipient and its email should be test2...
        foreach($base_email->ref('email_recipient') as $rec) {
            $this->assertEquals($rec->get('email'), 'test2@easyoutdooroffice.com');
        }
    }


    /*
     * tests send function
     */
    public function testSend() {
        $this->_addStandardEmailAccount();
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
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $base_email->loadInitialValues();
        $this->assertTrue(true);
    }


    /*
     *
     */
    public function testAttachments() {
        $this->_addStandardEmailAccount();
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
        $this->_addStandardEmailAccount();
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
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => 'testemailtemplate.html']);
        $base_email->loadInitialValues();
        $this->assertEquals($base_email->get('subject'), 'TestBetreff');
        $this->assertTrue(strpos($base_email->get('message'), 'TestInhalt') !== false);
    }


    /*
     *
     */
    public function testInitialTemplateLoadingByString() {
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}Hellow{/Subject}Magada']);
        $base_email->loadInitialValues();
        $this->assertEquals($base_email->get('subject'), 'Hellow');
        $this->assertTrue(strpos($base_email->get('message'), 'Magada') !== false);
    }


    /*
     *
     */
    public function testLoadSignatureByUserSignature() {
        $this->_addStandardEmailAccount();
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
        $this->_addStandardEmailAccount();
        $_ENV['STD_EMAIL_SIGNATURE'] = 'TestSigSetting';
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}Hellow{/Subject}Magada{Signature}{/Signature}']);
        $base_email->loadInitialValues();
        $this->assertTrue(strpos($base_email->get('message'), 'TestSigSetting') !== false);
    }


    /*
     *
     */
    public function testSMTPKeepAlive() {
        $this->_addStandardEmailAccount();
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
    public function testProcessSubjectAndMessagePerRecipient() {
        $this->_addStandardEmailAccount();
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


    /*
     *
     */
    public function testProcessMessageFunction() {
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}BlaDu{$testsubject}{/Subject}BlaDu{$testbody}']);
        $base_email->processMessageTemplate = function($template, $model) {
            $template->set('testbody', 'HALLELUJA');
        };
        $base_email->processSubjectTemplate = function($template, $model) {
            $template->set('testsubject', 'HALLELUJA');
        };
        $base_email->loadInitialValues();
        $this->assertTrue(strpos($base_email->get('message'), 'HALLELUJA') !== false);
        $this->assertTrue(strpos($base_email->get('subject'), 'HALLELUJA') !== false);
    }


    /*
     *
     */
    public function testOnSuccessFunction() {
        $this->_addStandardEmailAccount();
        $base_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db, ['template' => '{Subject}BlaDu{$testsubject}{/Subject}BlaDu{$testbody}']);
        $base_email->loadInitialValues();
        $base_email->model = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $base_email->onSuccess = function($model) {
            $model->set('name', 'PIPI');
        };
        $base_email->addRecipient('test1@easyoutdooroffice.com');
        $this->assertTrue($base_email->send());
        $this->assertEquals('PIPI', $base_email->model->get('name'));
    }


    /*
     * F***ing ref() function on non-loaded models!.
     * Make sure non-saved BaseEmail does not accidently
     * load any EmailRecipients
     */
    public function testNonLoadedBaseEmailHasNoRefEmailRecipients() {
        $this->_addStandardEmailAccount();
        //first create a baseEmail and some EmailRecipients
        $be1 = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $be1->save();
        //this baseEmail should not be sent. $be2->ref('email_recipient') will reference
        //the 2 EmailRecipients above as $be2->loaded() = false. BaseEmail needs to check this!
        $be2 = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $this->assertFalse($be2->send());
    }


    /*
     *
     */
    public function testEmailSendFail() {
        $this->_addStandardEmailAccount();
        $be = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $be->phpMailer = new class extends \PHPMailer\PHPMailer\PHPMailer { public function send() {return false;}};
        $be->addRecipient('test2@easyoutdooroffice.com');
        $be->set('subject', __FUNCTION__);
        $be->save();
        $messages = self::$app->userMessages;
        $this->assertFalse($be->send());
        //should add message to app
        $new_messages = self::$app->userMessages;
        $this->assertEquals(count($messages) + 1, count($new_messages));
    }


    /*
     *
     */
    public function testGetModelVars() {
        $this->_addStandardEmailAccount();
        $be = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $res = $be->getModelVars(new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db));
        $this->assertEquals(['name' => 'AName', 'time_test' => 'Startzeit', 'date_test' => 'Startdatum'], $res);

        $res = $be->getModelVars(new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db));
        $this->assertEquals(['name' => 'Name', 'firstname' => 'Vorname'], $res);
    }


    /*
     *
     */
    public function testGetModelVarsPrefix() {
        $this->_addStandardEmailAccount();
        $be = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $res = $be->getModelVars(new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db), 'tour_');
        $this->assertEquals(['tour_name' => 'Name', 'tour_firstname' => 'Vorname'], $res);
    }


    /*
     *
     */
    public function testgetTemplateEditVars() {
        $this->_addStandardEmailAccount();
        $be = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $be->model = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        self::assertEquals(['BMACAPTION' => ['basemodela_name' => 'Name', 'basemodela_firstname' => 'Vorname']], $be->getTemplateEditVars());
    }
}
