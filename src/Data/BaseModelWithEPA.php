<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Model;
use secondarymodelforatk\SecondaryModelRelationTrait;


abstract class BaseModelWithEPA extends BaseModel
{

    use SecondaryModelRelationTrait;

    protected function init(): void
    {
        parent::init();

        $this->addSecondaryModelHasMany(Email::class);
        $this->addSecondaryModelHasMany(Address::class);
        $this->addSecondaryModelHasMany(Phone::class);
    }

    public function addEmail(string $value): Email
    {
        return $this->addEPA(Email::class, $value);
    }

    public function addPhone(string $value): Phone
    {
        return $this->addEPA(Phone::class, $value);
    }

    public function addAddress(string $value): Address
    {
        return $this->addEPA(Address::class, $value);
    }

    public function addEPA(string $type, string $value): SecondaryModel {
        if(!$this->loaded()) {
            $this->save();
        }
        $record = $this->addSecondaryModelRecord($type, $value);
        $this->addSecondaryAudit('ADD', $record);

        return $record;
    }

    public function updateEPA(string $type, $id, string $value): SecondaryModel
    {
        $record = $this->updateSecondaryModelRecord($type, $id, $value);
        $this->addSecondaryAudit('UPDATE', $record);

        return $record;
    }

    public function deleteEPA(string $type, $id): SecondaryModel
    {
        $record = $this->deleteSecondaryModelRecord($type, $id);
        $this->addSecondaryAudit('DELETE', $record);

        return $record;
    }
}
