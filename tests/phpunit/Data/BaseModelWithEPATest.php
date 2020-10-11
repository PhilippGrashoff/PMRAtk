<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use auditforatk\Audit;
use notificationforatk\Notification;
use PMRAtk\Data\Address;
use PMRAtk\Data\Email;
use PMRAtk\Data\Phone;
use PMRAtk\tests\TestClasses\BaseModelClasses\ModelWithEPA;
use traitsforatkdata\TestCase;


class BaseModelWithEPATest extends TestCase
{

    protected $sqlitePersistenceModels = [
        ModelWithEPA::class,
        Notification::class,
        Email::class,
        Address::class,
        Phone::class,
        Audit::class,
    ];

    public function testAddEmail()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $return = $model->addEmail('someemail');
        self::assertInstanceOf(
            Email::class,
            $return
        );

        self::assertEquals(
            1,
            (new Email($persistence))->action('count')->getOne()
        );
    }

    public function testAddPhone()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $return = $model->addPhone('someemail');
        self::assertInstanceOf(
            Phone::class,
            $return
        );

        self::assertEquals(
            1,
            (new Phone($persistence))->action('count')->getOne()
        );

        self::assertCount(
            2,
            $model->ref(Audit::class)
        );
    }

    public function testAddAddress()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $return = $model->addAddress('someemail');
        self::assertInstanceOf(
            Address::class,
            $return
        );

        self::assertEquals(
            1,
            (new Address($persistence))->action('count')->getOne()
        );
    }

    public function testUpdateEPA()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $record = $model->addEPA(Email::class, 'someemail');
        $return = $model->updateEPA(Email::class, $record->get('id'), 'someotheremail');
        self::assertInstanceOf(
            Email::class,
            $return
        );

        self::assertEquals(
            'someotheremail',
            $record->reload()->get('value')
        );
    }

    public function testDeleteEPA()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $record = $model->addEPA(Email::class, 'someemail');
        $return = $model->deleteEPA(Email::class, $record->get('id'));
        self::assertInstanceOf(
            Email::class,
            $return
        );

        $record->tryLoad($record->get('id'));
        self::assertFalse($record->loaded());
    }

    public function testAddEPACreatesAudit()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();

        $audit = $model->ref(Audit::class);
        foreach ($audit as $a) {
            $a->delete();
        }
        self::assertEquals(
            0,
            $model->ref(Audit::class)->action('count')->getOne()
        );

        $record = $model->addEPA(Email::class, 'someemail');
        self::assertEquals(
            1,
            $model->ref(Audit::class)->action('count')->getOne()
        );
    }

    public function testUpdateEPACreatesAudit()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $record = $model->addEPA(Email::class, 'someemail');

        $audit = $model->ref(Audit::class);
        foreach ($audit as $a) {
            $a->delete();
        }

        self::assertEquals(
            0,
            $model->ref(Audit::class)->action('count')->getOne()
        );

        $model->updateEPA(Email::class, $record->get('id'), 'someOtherEmail');
        self::assertEquals(
            1,
            $model->ref(Audit::class)->action('count')->getOne()
        );
    }

    public function testDeleteEPACreatesAudit()
    {
        $persistence = $this->getSqliteTestPersistence();
        $model = new ModelWithEPA($persistence);
        $model->save();
        $record = $model->addEPA(Email::class, 'someemail');

        $audit = $model->ref(Audit::class);
        foreach ($audit as $a) {
            $a->delete();
        }

        self::assertEquals(
            0,
            $model->ref(Audit::class)->action('count')->getOne()
        );

        $model->deleteEPA(Email::class, $record->get('id'));
        self::assertEquals(
            1,
            $model->ref(Audit::class)->action('count')->getOne()
        );
    }
}
