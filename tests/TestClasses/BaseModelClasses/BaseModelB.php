<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Traits\AuditTrait;
use PMRAtk\Data\Traits\EPARelationsTrait;
use PMRAtk\Data\Traits\FileRelationTrait;
use PMRAtk\Data\Traits\MToMTrait;
use PMRAtk\tests\TestClasses\AToB;

class BaseModelB extends BaseModel {

    use EPARelationsTrait;
    use MToMTrait;
    use AuditTrait;
    use FileRelationTrait;

    public $table = 'BaseModelB';

    public function init(): void {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string', 'caption' => 'AName'],
            ['time_test', 'type' => 'time',   'caption' => 'Startzeit'],
            ['date_test', 'type' => 'date',   'caption' => 'Startdatum'],
        ]);

        $this->_addEPARefs();
        $this->_addAuditRef();
        $this->_addFileRef();

        $this->hasMany('AToB', new AToB());
    }
}