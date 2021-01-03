<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Traits\FileRelationTrait;


class ModelWithFileRelation extends BaseModel
{

    use FileRelationTrait;

    public $table = 'model_with_file_relation';

    protected function init(): void
    {
        parent::init();
        $this->addField('name');

        $this->addFileReferenceAndDeleteHook();
    }
}