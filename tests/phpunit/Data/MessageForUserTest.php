<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\Exception;
use auditforatk\Audit;
use DateTime;
use PMRAtk\App\App;
use PMRAtk\Data\MessageForUser;
use PMRAtk\Data\MessageForUserToUser;
use PMRAtk\Data\User;
use PMRAtk\tests\phpunit\TestCase;
use Throwable;

class MessageForUserTest extends TestCase
{

    private $app;
    private $persistence;

    protected $sqlitePersistenceModels = [
        User::class,
        Audit::class,
        MessageForUser::class,
        MessageForUserToUser::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->app = new App(['nologin'], ['always_run' => false]);
        $this->persistence = $this->getSqliteTestPersistence();
        $this->app->db = $this->persistence;
        $this->persistence->app = $this->app;
        $user = new User($this->persistence);
        $user->set('role', 'admin');
        $user->set('username', 'Somename');
        $user->save();
        $this->app->auth->user = $user;
    }

    public function testgetUnreadMessagesForLoggedInUser()
    {
        $message1 = new MessageForUser($this->persistence);
        $message1->set('param1', 'ALL');
        $message1->save();

        $message2 = new MessageForUser($this->persistence);
        $message2->set('param1', 'ALL');
        $message2->save();
        $message2->addMToMRelation(
            new MessageForUserToUser($this->persistence),
            $this->app->auth->user
        );

        $message3 = new MessageForUser($this->persistence);
        $message3->set('param1', 'SOMEOTHERROLE1');
        $message3->save();

        $message4 = new MessageForUser($this->persistence);
        $message4->set('param1', 'admin');
        $message4->save();

        $res = $message3->getUnreadMessagesForLoggedInUser(['ALL', 'admin']);
        self::assertEquals(2, $res->action('count')->getOne());
    }
    
    public function testIsReadByLoggedInUser()
    {
        $message1 = new MessageForUser($this->persistence);
        $message1->save();
        self::assertFalse($message1->isReadByLoggedInUser());

        $message1->addMToMRelation(new MessageForUserToUser($this->persistence), $this->app->auth->user);
        self::assertTrue($message1->isReadByLoggedInUser());
    }
    
    public function testMarkMessageAsRead()
    {
        $message1 = new MessageForUser($this->persistence);
        $message1->save();

        self::assertFalse($message1->isReadByLoggedInUser());

        $message1->markAsRead();
        self::assertTrue($message1->isReadByLoggedInUser());
    }
    
    public function testExceptionGetUnreadMessagesUserNotLoaded()
    {
        $u = new User($this->persistence);
        $cache = $this->app->auth->user;
        $this->app->auth->user = $u;
        $exceptionFound = false;
        try {
            $m = new MessageForUser($this->persistence);
            $m->getUnreadMessagesForLoggedInUser();
        } catch (Exception $e) {
            $exceptionFound = true;
        }
        $this->app->auth->user = $cache;
        self::assertTrue($exceptionFound);
    }

    public function testExceptionMarkAsReadNotLoaded()
    {
        $message1 = new MessageForUser($this->persistence);
        self::expectException(Exception::class);
        $message1->markAsRead();
    }

    public function testDifferentParamMatches()
    {
        $message1 = new MessageForUser($this->persistence);
        $message1->set('param1', 'LALALA');
        $message1->set('param2', 'Hansi');
        $message1->set('param3', 'Was');
        $message1->save();

        $message2 = new MessageForUser($this->persistence);
        $message2->set('param1', 'gege');
        $message2->set('param2', 'Hansi');
        $message2->set('param3', '');
        $message2->set('created_date', (new DateTime())->modify('-2 Month'));
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

        $res = $message1->getUnreadMessagesForLoggedInUser(
            function ($message) {
                $message->addCondition('param1', 'LIKE', '%geg%');
            }
        );
        self::assertEquals(1, $res->action('count')->getOne());
    }

    public function testDateFilter()
    {
        $message1 = new MessageForUser($this->persistence);
        $message1->save();

        $message2 = new MessageForUser($this->persistence);
        $message2->set('created_date', (new DateTime())->modify('-2 Month'));
        $message2->save();

        $message3 = new MessageForUser($this->persistence);
        $message3->set('created_date', (new DateTime())->modify('-2 Month'));
        $message3->save();

        try {
            $res = $message1->getUnreadMessagesForLoggedInUser(null, null, null, (new DateTime())->modify('-1 Month'));
            self::assertEquals(1, $res->action('count')->getOne());
        } catch (Throwable $e) {
            echo $e->getColorfulText();
        }

        $message3->set('never_invalid', 1);
        $message3->save();
        $res = $message1->getUnreadMessagesForLoggedInUser(null, null, null, (new DateTime())->modify('-1 Month'));
        self::assertEquals(2, $res->action('count')->getOne());
    }
}