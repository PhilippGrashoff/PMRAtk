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

    public function init(): void {
        parent::init();
        $this->addCreatedByFields();
        $this->addCreatedByHook();
        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
    }
}