<?php

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\MessageForUser;
use PMRAtk\Data\User;

class MessageForUserTest extends \PMRAtk\tests\phpunit\TestCase
{


    /*
     *
     */
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        self::$app->auth->user->addField('role', ['never_persist' => true]);
        self::$app->auth->user->set('role', 'admin');
    }


    /*
     *
     */
    public function testMtoM()
    {
        $user = new User(self::$app->db);
        $user->set(['username' => 'LALA', 'name' => 'BLABLAs']);
        $this->_testMToM(new MessageForUser(self::$app->db), $user);
    }


    /*
     *
     */
    public function testgetUnreadMessagesForLoggedInUser() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->set('param1', 'ALL');
        $message1->save();

        $message2 = new MessageForUser(self::$app->db);
        $message2->set('param1', 'ALL');
        $message2->save();
        $message2->addUser(self::$app->auth->user, ['is_read' => 1]);

        $message3 = new MessageForUser(self::$app->db);
        $message3->set('param1', 'SOMEOTHERROLE1');
        $message3->save();

        $message4 = new MessageForUser(self::$app->db);
        $message4->set('param1', 'admin');
        $message4->save();

        $res = $message3->getUnreadMessagesForLoggedInUser(['ALL', 'admin']);
        self::assertEquals(2, $res->action('count')->getOne());
    }


    /*
     *
     */
    public function testIsReadByLoggedInUser() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->save();
        self::assertFalse($message1->isReadByLoggedInUser());

        $message1->addUser(self::$app->auth->user);
        self::assertFalse($message1->isReadByLoggedInUser());

        $mfutu = $message1->ref('MessageForUserToUser');
        foreach ($mfutu as $x) {
            $x->set('is_read', 1);
            $x->save();
        }

        self::assertTrue($message1->isReadByLoggedInUser());
    }
    
    
    /*
     * 
     */
    public function testMarkMessageAsRead() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->save();

        self::assertFalse($message1->isReadByLoggedInUser());

        $message1->markAsRead();
        self::assertTrue($message1->isReadByLoggedInUser());
    }


    /*
     *
     */
    public function testExceptionGetUnreadMessagesUserNotLoaded() {
        $u = new User(self::$app->db);
        $cache = self::$app->auth->user;
        self::$app->auth->user = $u;
        $exceptionFound = false;
        try {
            $m = new MessageForUser(self::$app->db);
            $m->getUnreadMessagesForLoggedInUser();
        }
        catch(\atk4\data\Exception $e) {
            $exceptionFound = true;
        }
        self::$app->auth->user = $cache;
        self::assertTrue($exceptionFound);
    }


    /*
     *
     */
    public function testExceptionMarkAsReadNotLoaded() {
        $message1 = new MessageForUser(self::$app->db);
        self::expectException(\atk4\data\Exception::class);
        $message1->markAsRead();
    }


    /*
     *
     */
    public function testDifferentParamMatches() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->set('param1', 'LALALA');
        $message1->set('param2', 'Hansi');
        $message1->set('param3', 'Was');
        $message1->save();
        
        $message2 = new MessageForUser(self::$app->db);
        $message2->set('param1', 'gege');
        $message2->set('param2', 'Hansi');
        $message2->set('param3', '');
        $message2->set('created_date', (new \DateTime())->modify('-2 Month'));
        $message2->save();
        
        $res = $message1->getUnreadMessagesForLoggedInUser();
        self::assertEquals(2, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser(['LALALA', 'gege']);
        self::assertEquals(2, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser('LALALA');
        self::assertEquals(1, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser(null, ['Hansi']);
        self::assertEquals(2, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser('LALALA', null, '');
        self::assertEquals(0, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser(null,null, null, (new \DateTime())->modify('-1 Month'));
        self::assertEquals(1, $res->action('count')->getOne());

        $res = $message1->getUnreadMessagesForLoggedInUser(function($message) {
            $message->addCondition('param1', 'LIKE', '%geg%');
        });
        self::assertEquals(1, $res->action('count')->getOne());
    }

    /**/
}