<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Token;
use secondarymodelforatk\SecondaryModelRelationTrait;

class BaseModelE extends BaseModel {

    use SecondaryModelRelationTrait;

    public $table = 'BaseModelE';

    protected function init(): void {
        parent::init();

        $this->addFields(
            [
                ['name',      'type' => 'string', 'caption' => 'AName'],
            ]
        );

        $this->addSecondaryModelHasMany(
            Token::class,
            false
        );
    }
}