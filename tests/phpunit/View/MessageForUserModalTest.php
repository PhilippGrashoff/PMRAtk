<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View;

use atk4\ui\Layout\Admin;
use atk4\ui\Modal;
use auditforatk\Audit;
use PMRAtk\App\App;
use PMRAtk\Data\MessageForUser;
use PMRAtk\Data\MessageForUserToUser;
use PMRAtk\Data\User;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\MessageForUserModal;


class MessageForUserModalTest extends TestCase
{
    protected $sqlitePersistenceModels = [
        MessageForUser::class,
        MessageForUserToUser::class,
        Audit::class,
        User::class
    ];

    public function testRenderModal() {
        $persistence = $this->getSqliteTestPersistence();
        $message = new MessageForUser($persistence);
        $message->set('title', 'someTitle');
        $message->save();

        $user = new User($persistence);
        $user->set('username', 'lala');
        $user->save();

        $app = new App(['nologin'], ['always_run' => false]);
        $app->auth->user = $user;
        $app->initLayout([Admin::class]);
        $app->db = $persistence;
        $persistence->app = $app;

        $modal = MessageForUserModal::addTo($app);
        $modal->renderView();
        self::assertTrue(true);
    }
}