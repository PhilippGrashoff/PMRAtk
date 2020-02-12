<?php

namespace PMRAtk\tests\phpunit\Data;

class BaseModelB extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;
    use \PMRAtk\Data\Traits\MToMTrait;
    use \PMRAtk\Data\Traits\AuditTrait;
    use \PMRAtk\Data\Traits\FileRelationTrait;

    public $table = 'BaseModelB';

    public function init() {
        parent::init();

        $this->addFields([
            ['name',      'type' => 'string', 'caption' => 'AName'],
            ['time_test', 'type' => 'time',   'caption' => 'Startzeit'],
            ['date_test', 'type' => 'date',   'caption' => 'Startdatum'],
        ]);

        $this->_addEPARefs();
        $this->_addAuditRef();
        $this->_addFileRef();

        $this->hasMany('MToMModel', new MToMModel());
    }
}