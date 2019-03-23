<?php

namespace PMRAtk\Data\Traits;

trait DeleteHasManyTrait {

    /*
     * loads all objects for this reference and deletes them one by one.
     * Like this, it is ensured the delete hooks of these objects are executed.
     *
     * @param string      Reference to be deleted
     *
     * @return bool
     */
    public function deleteHasMany(string $ref_name):bool {
        //first check if reference exists
        if(!$this->hasRef($ref_name)) {
            throw new \atk4\data\Exception('The reference '.$ref_name.' is not defined for this class: '.__CLASS__);
        }
        if(!$this->loaded()) {
            throw new \atk4\data\Exception('$this needs to be loaded in '.__FUNCTION__);

        }

        //distinguish MtoM and 1toM relations, only use each for MtoM
        if($this->getRef($ref_name) instanceOf \atk4\data\Reference_Many) {
            $this->ref($ref_name)->each('delete');
            return true;
        }

        throw new \atk4\data\Exception('The Reference '.$ref_name.' is not of type \atk4\data\Reference_Many in '.__FUNCTION__);
    }
}