<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Token;
use secondarymodelforatk\SecondaryModelRelationTrait;

class BaseModelD extends BaseModel {

    use SecondaryModelRelationTrait;

    public $table = 'BaseModelD';

    protected function init(): void {
        parent::init();

        $this->addFields([
                             ['name',      'type' => 'string', 'caption' => 'AName'],
                             ['time_test', 'type' => 'time',   'caption' => 'Startzeit'],
                             ['date_test', 'type' => 'date',   'caption' => 'Startdatum'],
                             ['some_other_id_field', 'type' => 'integer']
                         ]);

        $this->addSecondaryModelHasMany(
            Token::class,
            true,
            BaseModelA::class,
            'some_other_id_field'
        );
    }
}