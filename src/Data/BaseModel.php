<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Model;
use auditforatk\ModelWithAuditTrait;
use notificationforatk\ModelWithNotificationTrait;
use traitsforatkdata\CreatedByTrait;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;
use traitsforatkdata\ExtraModelFunctionsTrait;
use traitsforatkdata\ModelWithAppTrait;


abstract class BaseModel extends Model
{

    use CreatedDateAndLastUpdatedTrait;
    use CreatedByTrait;
    use ModelWithNotificationTrait;
    use ModelWithAuditTrait;
    use ModelWithAppTrait;
    use ExtraModelFunctionsTrait;


    protected function init(): void
    {
        $this->skipFieldsFromAudit = ['last_updated', 'created_date', 'created_by'];

        parent::init();

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
        $this->addCreatedByFieldAndHook();
        $this->addNotificationReferenceAndHooks();
        $this->addAuditRefAndAuditHooks();
    }
}
