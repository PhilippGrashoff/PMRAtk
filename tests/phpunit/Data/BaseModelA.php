<?php

namespace PMRAtk\tests\phpunit\Data;

class BaseModelA extends \PMRAtk\Data\BaseModel {

    use \PMRAtk\Data\Traits\EPARelationsTrait;
    use \PMRAtk\Data\Traits\MToMTrait;
    use \PMRAtk\Data\Traits\AuditTrait;

    public $table = 'BaseModelA';

    public $caption = 'BMACAPTION';

    public function init() {
        parent::init();

        $this->_addAuditRef();

        $this->addFields([
            ['name',      'type' => 'string'],
            ['date',      'type' => 'date'],
            ['time',      'type' => 'time'],
            ['dd_test',   'type' => 'integer', 'ui' => ['form' => ['DropDown', 'values' => [0 => 'Nein', 1 => 'Ja']]]],
            ['dd_test_2', 'type' => 'string', 'ui' => ['form' => ['DropDown', 'empty' => 'Hans']]],
            ['firstname', 'type' => 'string', 'caption' => 'Vorname'],
            ['lastname',  'type' => 'string'],

        ]);

        $this->_addEPARefs();

        $this->hasMany('MToMModel', new MToMModel());

        $this->hasOne('BaseModelB_id', new BaseModelB());
    }


    /*
     *
     */
    public function getFieldsForEmailTemplate():array {
        return ['name', 'firstname'];
    }
}