<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use auditforatk\Audit;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use traitsforatkdata\TestCase;
use atk4\data\Exception;


/**
 *
 */
class BaseModelTest extends TestCase {

    protected $sqlitePersistenceModels = [
        BaseModelA::class,
        BaseModelB::class,
        Audit::class
    ];

    public function testAtLeastOneFieldDirty() {
        $model = new BaseModelA($this->getSqliteTestPersistence());
        $model->save();
        self::assertFalse($model->isAtLeastOneFieldDirty(['name', 'firstname', 'lastname']));
        $model->set('lastname', 'SOMENAME');
        self::assertTrue($model->isAtLeastOneFieldDirty(['name', 'firstname', 'lastname']));
    }

    public function testExceptionIfThisNotLoaded() {
        $u = new BaseModelA($this->getSqliteTestPersistence());
        $u->save();
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
        $u->unload();
        $this->expectException(Exception::class);
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
    }


    /**
     *
     */
    public function testLoadedHasOneRef() {
        $b = new BaseModelB(self::$app->db);
        $b->save();
        $u = new BaseModelA(self::$app->db);
        $u->set('BaseModelB_id', $b->get('id'));
        $u->save();
        $ref = $u->loadedHasOneRef('BaseModelB_id');
        self::assertEquals($b->get('id'), $ref->get('id'));
        $b->delete();
        $this->expectException(\atk4\data\Exception::class);
        $u->loadedHasOneRef('BaseModelB_id');
    }


    /**
     *
     */
    public function testLoadedHasOneRefFieldEmpty() {
        $u = new BaseModelA(self::$app->db);
        $u->save();
        $this->expectException(\atk4\data\Exception::class);
        $u->loadedHasOneRef('BaseModelB_id');
    }
}
