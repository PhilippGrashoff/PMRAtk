<?php

class BaseEmailTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * tests the addRecipient and removeRecipient Function passing various params
     */
    public function testAddRecipient() {
        $outbox_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $outbox_email->save();

        //pass a Guide, should have an email set
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->set('firstname', 'Lala');
        $g->set('lastname', 'Dusu');
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $this->assertTrue($outbox_email->addRecipient($g));
        $this->assertEquals(1, $outbox_email->ref('EmailRecipient')->action('count')->getOne());

        //adding the same guide again shouldnt change anything
        $this->assertFalse($outbox_email->addRecipient($g));
        $this->assertEquals(1, $outbox_email->ref('EmailRecipient')->action('count')->getOne());

        //pass a non-loaded Guide
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $this->assertFalse($outbox_email->addRecipient($g));

        //pass a Guide without an existing Email
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $this->assertFalse($outbox_email->addRecipient($g));

        //pass an email id
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $e = $g->addEmail('test3@easyoutdooroffice.com');
        $this->assertTrue($outbox_email->addRecipient($e->get('id')));
        $this->assertEquals(2, $outbox_email->ref('EmailRecipient')->action('count')->getOne());

        //pass a non existing email id
        $this->assertFalse($outbox_email->addRecipient(111111));

        //pass a valid Email
        $this->assertTrue($outbox_email->addRecipient('philipp@spame.de'));
        $this->assertEquals(3, $outbox_email->ref('EmailRecipient')->action('count')->getOne());

        //pass an invalid email
        $this->assertFalse($outbox_email->addRecipient('hannsedfsgs'));

        //now remove all
        foreach($outbox_email->ref('EmailRecipient') as $rec) {
            $this->assertTrue($outbox_email->removeRecipient($rec->get('id')));
        }
        $this->assertEquals(0, $outbox_email->ref('EmailRecipient')->action('count')->getOne());

        //remove some non_existing EmailRecipient
        $this->assertFalse($outbox_email->removeRecipient('11111'));

        //test adding not the first, but some other email
        $g = new \PMRAtk\tests\phpunit\Data\BaseModelA(self::$app->db);
        $g->save();
        $g->addEmail('test1@easyoutdooroffice.com');
        $test2_id = $g->addEmail('test2@easyoutdooroffice.com');
        $this->assertTrue($outbox_email->addRecipient($g, $test2_id->get('id')));
        //now there should be a single recipient and its email should be test2...
        foreach($outbox_email->ref('EmailRecipient') as $rec) {
            $this->assertEquals($rec->get('email'), 'test2@easyoutdooroffice.com');
        }
    }


    /*
     * tests send function
     */
    public function testSend() {
        //no recipients, should return false
        $outbox_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $this->assertFalse($outbox_email->send());

        //one recipient, should return true
        $outbox_email = new \PMRAtk\Data\Email\BaseEmail(self::$app->db);
        $outbox_email->set('subject', 'Hello from PHPUnit');
        $outbox_email->set('message', 'Hello from PHPUnit');
        $this->assertTrue($outbox_email->addRecipient('test2@easyoutdooroffice.com'));
        $this->assertTrue($outbox_email->send());
    }
}
