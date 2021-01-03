<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use auditforatk\Audit;
use notificationforatk\Notification;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelWithHasOneRef;
use PMRAtk\tests\TestClasses\BaseModelClasses\JustABaseModel;
use traitsforatkdata\TestCase;
use atk4\data\Exception;


class BaseModelTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        JustABaseModel::class
    ];

    public function testInit() {
        $model = new JustABaseModel($this->getSqliteTestPersistence());
        self::assertTrue($model->hasField('created_by'));
        self::assertTrue($model->hasField('created_date'));
        self::assertTrue($model->hasField('last_updated'));
        self::assertTrue($model->hasRef(Audit::class));
        self::assertTrue($model->hasRef(Notification::class));
    }
}
