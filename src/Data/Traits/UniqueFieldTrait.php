<?php

namespace PMRAtk\Data\Traits;

trait UniqueFieldTrait {

    /*
     * checks if another record has the same field value already
     */
    public function isFieldUnique(string $field_name):bool {
        //value may not be empty
        if(empty($this->get($field_name))) {
            throw new \atk4\data\Exception('The value for a unique field may not be empty. Field name: '.$field_name.' in '.__FUNCTION__);
        }
        $other = $this->newInstance();
        $other->addCondition($this->id_field, '!=', $this->get($this->id_field));
        $other->tryLoadBy($field_name, $this->get($field_name));
        if($other->loaded()) {
            return false;
        }
        return true;
    }
}
