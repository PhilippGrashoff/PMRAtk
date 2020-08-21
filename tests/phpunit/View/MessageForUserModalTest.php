<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View;


use PMRAtk\Data\MessageForUser;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\MessageForUserModal;

/**
 * Class MessageForUserModalTest
 * @package PMRAtk\tests\phpunit\View
 */
class MessageForUserModalTest extends TestCase
{
    /**
     *
     */
    public function testRenderModal() {
        $message = new MessageForUser(self::$app->db);
        $message->set('title', 'someTitle');
        $message->save();
        $modal = self::$app->add(new MessageForUserModal());
        $modal->app = self::$app;
        $modal->renderView();
        self::assertTrue(true);
    }
}