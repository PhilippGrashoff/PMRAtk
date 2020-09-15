<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Token;
use secondarymodelforatk\SecondaryModelRelationTrait;

class BaseModelC extends BaseModel {

    use SecondaryModelRelationTrait;

    public $table = 'BaseModelC';

    public function init(): void {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string', 'caption' => 'AName'],
            ['time_test', 'type' => 'time',   'caption' => 'Startzeit'],
            ['date_test', 'type' => 'date',   'caption' => 'Startdatum'],
        ]);

        $this->addSecondaryModelHasMany(Token::class);

    }
}