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
            ['name',      'type' => 'string'],
            ['date',      'type' => 'date'],
            ['time',      'type' => 'time'],
            ['dd_test',   'type' => 'string', 'ui' => ['form' => ['DropDown', 'values' => [0 => 'Nein', 1 => 'Ja']]]],
            ['dd_test_2', 'type' => 'string', 'ui' => ['form' => ['DropDown', 'empty' => 'Hans']]],
            ['firstname', 'type' => 'string'],
            ['lastname',  'type' => 'string'],

        ]);

        $this->_addEPARefs();

        $this->hasMany('MToMModel', new MToMModel());

        $this->hasOne('BaseModelB_id', new BaseModelB());

        //after save, create Audit
        $this->addHook('afterSave', function($m, $is_update) {
            $m->createAudit($is_update ? 'CHANGE' : 'CREATE');
        });
        //after delete, create Audit
        $this->addHook('afterDelete', function($m) {
            $m->createDeleteAudit();
        });
    }
}