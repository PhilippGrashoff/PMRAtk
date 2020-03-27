<?php

namespace PMRAtk\Data;

class SecondaryBaseModel extends \atk4\data\Model {

    public $parentObject;

    public function init() {
        parent::init();

        $this->addFields([
            ['model_class',     'type'=>'text',       'system' => true],
            ['model_id',        'type'=>'integer',    'system' => true],
            ['value',           'type'=>'text'],
            ['created_date',    'type' => 'datetime', 'persist_timezone' => 'Europe/Berlin', 'system' => true],
            ['last_updated',    'type' => 'datetime', 'persist_timezone' => 'Europe/Berlin', 'system' => true],
        ]);


        //set model_class and model_id if parentObject was set
        if(isset($this->parentObject) && $this->parentObject instanceOf \atk4\data\Model) {
            $this->set('model_class', (new \ReflectionClass($this->parentObject))->getName());
            if($this->parentObject->get('id')) {
                $this->set('model_id', ($this->parentObject)->get('id'));
            }
        }

        //trim value before saving
        $this->addHook('beforeSave', function($m) {
            $m->set('value', trim($m->get('value')));
            //if its update, set last_updated
            if($m->get('id')) {
                $m->set('last_updated', new \DateTime());
            }
            //on insert set created_date
            else {
                $m->set('created_date', new \DateTime());
            }
        });
    }


    /*
     * tries to load its parent object based on model_class and model_id
     *
     * @return object|null
     */
    public function getParentObject() {
        if(empty($this->get('model_class')) || empty($this->get('model_id'))) {
            return null;
        }

        $classname = $this->get('model_class');
        if(!class_exists($classname)) {
            throw new \atk4\data\Exception('Class '.$classname.' does not exist in '.__FUNCTION__);
        }

        $o = new $classname($this->persistence);
        $o->tryLoad($this->get('model_id'));

        return clone $o;
    }
}
?>
