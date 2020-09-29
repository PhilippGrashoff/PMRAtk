<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use notificationforatk\Notification;
use PMRAtk\Data\Email;
use traitsforatkdata\TestCase;

class EmailTest extends TestCase {

    protected $sqlitePersistenceModels= [
        Email::class,
        Notification::class
    ];

    public function testNofiticationForValidFormat() {
        $email = new Email($this->getSqliteTestPersistence());
        $email->set('value', 'invalid');
        $email->save();
        self::assertEquals(
            1,
            $email->ref(Notification::class)->action('count')->getOne()
        );

        $email->set('value', 'somevalid@email.com');
        $email->save();

        self::assertEquals(
            0,
            $email->ref(Notification::class)->action('count')->getOne()
        );
    }
}
