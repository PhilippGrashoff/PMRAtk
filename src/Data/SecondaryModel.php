<?php

declare(strict_types=1);

namespace PMRAtk\Data;

use traitsforatkdata\CreatedByTrait;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;
use traitsforatkdata\ModelWithAppTrait;

class SecondaryModel extends \secondarymodelforatk\SecondaryModel {

    use CreatedDateAndLastUpdatedTrait;
    use CreatedByTrait;
    use ModelWithAppTrait;

    protected function init(): void {
        parent::init();
        $this->addCreatedByFieldAndHook();
        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
    }
}