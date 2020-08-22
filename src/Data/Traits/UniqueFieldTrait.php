<?php declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use atk4\data\Exception;


/**
 *
 */
trait UniqueFieldTrait {

    /*
     * checks if another record has the same field value already
     */
    public function isFieldUnique(string $field_name):bool {
        //value may not be empty
        if(empty($this->get($field_name))) {
            throw new Exception('The value for a unique field may not be empty. Field name: '.$field_name.' in '.__FUNCTION__);
        }
        $other = new static($this->persistence);
        $other->only_fields = [$this->id_field, $field_name];
        $other->addCondition($this->id_field, '!=', $this->get($this->id_field));
        $other->tryLoadBy($field_name, $this->get($field_name));

        return $other->loaded();
    }
}
