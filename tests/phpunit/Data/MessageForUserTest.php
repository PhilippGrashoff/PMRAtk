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
        $message1->set('for_user_roles', ['ALL']);
        $message1->save();

        $message2 = new MessageForUser(self::$app->db);
        $message2->set('for_user_roles', ['ALL']);
        $message2->save();
        $message2->addUser(self::$app->auth->user, ['is_read' => 1]);

        $message3 = new MessageForUser(self::$app->db);
        $message3->set('for_user_roles', ['SOMEOTHERROLE1', 'SOMEOTHERROLE2']);
        $message3->save();

        $message4 = new MessageForUser(self::$app->db);
        $message4->set('for_user_roles', ['SOMEOTHERROLE3', 'admin']);
        $message4->save();

        $res = $message3->getUnreadMessagesForLoggedInUser();
        self::assertEquals(2, count($res));
        self::assertEquals($message1->get('id'), $res[0]->get('id'));
        self::assertEquals($message4->get('id'), $res[1]->get('id'));
    }


    /*
     *
     */
    public function testIsReadByLoggedInUser() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->set('for_user_roles', ['ALL']);
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
    public function testMarkMessagesAsRead() {
        $message1 = new MessageForUser(self::$app->db);
        $message1->set('for_user_roles', ['ALL']);
        $message1->save();

        $message2 = new MessageForUser(self::$app->db);
        $message2->set('for_user_roles', ['ALL']);
        $message2->save();

        $message3 = new MessageForUser(self::$app->db);
        $message3->set('for_user_roles', ['ALL']);
        $message3->save();

        $message4 = new MessageForUser(self::$app->db);
        $message4->set('for_user_roles', ['SOMEOTHERROLE']);
        $message4->save();

        $message1->markMessagesAsRead([$message2]);

        self::assertFalse($message1->isReadByLoggedInUser());
        self::assertTrue($message2->isReadByLoggedInUser());
        self::assertFalse($message3->isReadByLoggedInUser());
        self::assertFalse($message4->isReadByLoggedInUser());

        $message1->markMessagesAsRead([$message1->get('id')]);

        self::assertTrue($message1->isReadByLoggedInUser());
        self::assertTrue($message2->isReadByLoggedInUser());
        self::assertFalse($message3->isReadByLoggedInUser());
        self::assertFalse($message4->isReadByLoggedInUser());

        $message1->markMessagesAsRead();

        self::assertTrue($message1->isReadByLoggedInUser());
        self::assertTrue($message2->isReadByLoggedInUser());
        self::assertTrue($message3->isReadByLoggedInUser());
        self::assertFalse($message4->isReadByLoggedInUser());
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
    /**/
}