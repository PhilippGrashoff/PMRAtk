<?php

namespace PMRAtk\tests\phpunit\Data;

class BaseModelA extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;
    use \PMRAtk\Data\Traits\MToMTrait;
    use \PMRAtk\Data\Traits\AuditTrait;

    public $table = 'BaseModelA';

    public function init() {
        parent::init();

        $this->_addAuditRef();

        $this->addFields([
            ['name', 'type' => 'string'],
            ['date', 'type' => 'date'],
            ['time', 'type' => 'time'],
        ]);

        $this->_addEPARefs();

        $this->hasMany('MToMModel', new MToMModel());

        $this->hasOne('BaseModelB_id', new BaseModelB());

        //after save, create Audit
        $this->addHook('afterSave', function($m, $is_update) {
            $m->createAudit($is_update ? 'CHANGE' : 'CREATE');
        });
    }
}