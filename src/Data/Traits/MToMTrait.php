<?php

namespace PMRAtk\Data\Traits;

trait MToMTrait {

    /*
     * function used to add data to the MtoM relations like GroupToTour,
     * GuestToGroup etc.
     * First checks if record does exist already, and only then adds new relation.
     */
    protected function _addMToMRelation($object, \atk4\data\Model $mtom_object, string $object_class, string $our_field, string $their_field, array $addtional_fields = []):bool {
        //$this needs to be loaded to get ID
        $this->_exceptionIfThisNotLoaded();

        $object = $this->_mToMLoadObject($object, $object_class);

        //set values and conditions
        $mtom_object->set($our_field, $this->get('id'));
        $mtom_object->set($their_field, $object->get('id'));

        //set additional field values
        foreach($addtional_fields as $field_name => $value) {
            $mtom_object->set($field_name, $value);
        }

        //no reload neccessary after insert
        $mtom_object->reload_after_save = false;
        //if that record already exists mysql will throw an error if unique index is set, catch here
        try {
            $mtom_object->save();
        }
        catch(\Exception $e) {
            return false;
        }
    }


    /*
     * function used to remove a record the MtoM relations like GroupToTour,
     * GuestToGroup etc.
     */
    protected function _removeMToMRelation($object, \atk4\data\Model $mtom_object, string $object_class, string $our_field, string $their_field):bool {
        //$this needs to be loaded to get ID
        $this->_exceptionIfThisNotLoaded();

        $object = $this->_mToMLoadObject($object, $object_class);

        $mtom_object->addCondition($our_field, $this->get('id'));
        $mtom_object->addCondition($their_field, $object->get('id'));
        $mtom_object->loadAny();
        $mtom_object->delete();

        return true;
    }


    /*
     * checks if a MtoM reference to the given object exists or not
     *
     * @param object The object to check if its referenced with $this
     * @param object The MToM Refence class, e.g. GroupToTour
     *
     * @return bool
     */
    protected function _hasMToMRelation($object, \atk4\data\Model $mtom_model, string $object_class, string $our_field, string $their_field):bool {
        $this->_exceptionIfThisNotLoaded();

        $object = $this->_mToMLoadObject($object, $object_class);

        $mtom_model->addCondition($our_field, $this->get('id'));
        $mtom_model->addCondition($their_field, $object->get('id'));
        $mtom_model->tryLoadAny();

        return $mtom_model->loaded();
    }


    /*
     * helper function for MToMFunctions: Loads the object if only id is passed,
     * else checks if object matches rules
     */
    private function _mToMLoadObject($object, string $object_class) {
        //if object is passed, extract id
        if(is_object($object)) {
            //check if passed object is of desired type
            if(!$object instanceOf $object_class) {
                throw new \atk4\data\Exception('Wrong class:'.(new \ReflectionClass($object))->getName().' was passed, '.$object_class.' was expected in '.__FUNCTION__);
            }

        }
        //we need to have an Object to get table property
        else {
            $object_id = $object;
            $object = new $object_class($this->persistence);
            $object->tryLoad($object_id);
        }

        //make sure object is loaded
        if(!$object->loaded()) {
            throw new \atk4\data\Exception('Object could not be loaded in '.__FUNCTION__);
        }

        return $object;
    }
}