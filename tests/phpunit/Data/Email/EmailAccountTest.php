<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Email;

use auditforatk\Audit;
use PMRAtk\Data\Email\EmailAccount;
use PMRAtk\tests\TestClasses\BaseEmailTestClasses\EmailAccountNoDecrypt;
use traitsforatkdata\TestCase;


class EmailAccountTest extends TestCase {

    protected $sqlitePersistenceModels = [
        EmailAccount::class,
        Audit::class
    ];

    public function testHooks() {
        $persistence = $this->getSqliteTestPersistence();
        $ea = new EmailAccount($persistence);
        $ea->set('user',      'some1');
        $ea->set('password',  'some2');
        $ea->set('imap_host', 'some3');
        $ea->set('imap_port', 'some4');
        $ea->set('smtp_host', 'some5');
        $ea->set('smtp_port', 'some6');
        $ea->save();

        //check if its encrypted by using normal setting
        $setting = new EmailAccountNoDecrypt($persistence);
        $setting->load($ea->get('id'));
        //if encrypted, it shouldnt be unserializable
        self::assertFalse(@unserialize($setting->get('credentials')));
        self::assertFalse(strpos($setting->get('credentials'), 'some1'));

        $ea2 = new EmailAccount($persistence);
        $ea2->load($ea->get('id'));
        self::assertEquals($ea2->get('user'),       'some1');
        self::assertEquals($ea2->get('password'),   'some2');
        self::assertEquals($ea2->get('imap_host'),  'some3');
        self::assertEquals($ea2->get('imap_port'),  'some4');
        self::assertEquals($ea2->get('smtp_host'),  'some5');
        self::assertEquals($ea2->get('smtp_port'),  'some6');
    }
}
