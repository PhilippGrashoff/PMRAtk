<?php

class MessageForUserModalTest extends \PMRAtk\tests\phpunit\TestCase
{
    /**
     *
     */
    public function testRenderModal() {
        $message = new \PMRAtk\Data\MessageForUser(self::$app->db);
        $message->set('title', 'someTitle');
        $message->save();
        $modal = self::$app->add(new \PMRAtk\View\MessageForUserModal());
        $modal->app = self::$app;
        $modal->renderView();
        self::assertTrue(true);
    }
}