<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use auditforatk\Audit;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelWithHasOneRef;
use PMRAtk\tests\TestClasses\BaseModelClasses\JustABaseModel;
use traitsforatkdata\TestCase;
use atk4\data\Exception;


class BaseModelTest extends TestCase {

    protected $sqlitePersistenceModels = [
        JustABaseModel::class,
        BaseModelWithHasOneRef::class,
        Audit::class,
    ];

    public function testAtLeastOneFieldDirty() {
        $model = new JustABaseModel($this->getSqliteTestPersistence());
        $model->save();
        self::assertFalse($model->isAtLeastOneFieldDirty(['name', 'firstname', 'lastname']));
        $model->set('lastname', 'SOMENAME');
        self::assertTrue($model->isAtLeastOneFieldDirty(['name', 'firstname', 'lastname']));
    }

    public function testExceptionIfThisNotLoaded() {
        $model = new JustABaseModel($this->getSqliteTestPersistence());
        $model->save();
        $this->callProtected($model, '_exceptionIfThisNotLoaded', []);
        $model->unload();
        self::expectException(Exception::class);
        $this->callProtected($model, '_exceptionIfThisNotLoaded', []);
    }

    public function testLoadedHasOneRef() {
        $persistence = $this->getSqliteTestPersistence();
        $model = new BaseModelWithHasOneRef($persistence);
        $model->save();
        $referencedModel = new JustABaseModel($persistence);
        $referencedModel->save();
        $model->set('just_a_basemodel_id', $referencedModel->get('id'));
        $ref = $model->loadedHasOneRef('just_a_basemodel_id');
        self::assertEquals(
            $referencedModel->get('id'),
            $ref->get('id')
        );
        $referencedModel->delete();
        self::expectException(Exception::class);
        $model->loadedHasOneRef('just_a_basemodel_id');
    }

    public function testLoadedHasOneRefFieldEmpty() {
        $model = new BaseModelWithHasOneRef($this->getSqliteTestPersistence());
        $model->save();
        self::expectException(Exception::class);
        $model->loadedHasOneRef('just_a_basemodel_id');
    }
}
