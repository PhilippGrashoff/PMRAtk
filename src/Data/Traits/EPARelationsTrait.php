<?php declare(strict_types=1);

namespace PMRAtk\Data\Traits;

/*
 * EPA is short for Email, Address, Phone. This adds functions to add,
 * alter and delete related EPAs
 */

use atk4\data\Exception;
use PMRAtk\Data\Address;
use PMRAtk\Data\Email;
use PMRAtk\Data\Phone;

trait EPARelationsTrait
{

    protected $_epaRefModelClass;
    protected $_epaRefModelIdField = 'id';

    /*
     * use this in init() to quickly setup email, phone and address relations
     */
    protected function _addEPARefs(bool $addDelete = true)
    {
        $this->hasMany(
            'Phone',
            [
                function () {
                    return (new Phone($this->persistence, ['parentObject' => $this]))->addCondition(
                        'model_class',
                        ($this->_epaRefModelClass ? : get_class($this))
                    );
                },
                'their_field' => 'model_id',
                'our_field'   => $this->_epaRefModelIdField,
            ]
        );
        $this->hasMany(
            'Email',
            [
                function () {
                    return (new Email($this->persistence, ['parentObject' => $this]))->addCondition(
                        'model_class',
                        ($this->_epaRefModelClass ? : get_class($this))
                    );
                },
                'their_field' => 'model_id',
                'our_field'   => $this->_epaRefModelIdField,
            ]
        );
        $this->hasMany(
            'Address',
            [
                function () {
                    return (new Address($this->persistence, ['parentObject' => $this]))->addCondition(
                        'model_class',
                        ($this->_epaRefModelClass ? : get_class($this))
                    );
                },
                'their_field' => 'model_id',
                'our_field'   => $this->_epaRefModelIdField,
            ]
        );

        if($addDelete) {
            $this->onHook(
                'beforeDelete',
                function ($m) {
                    $m->deleteHasMany('Phone');
                    $m->deleteHasMany('Email');
                    $m->deleteHasMany('Address');
                }
            );
        }
    }


    /*
     * Helper function for getFirstEmail, getFirstAddress, getFirstPhone
     */
    protected function _getFirstEPA(string $type): string
    {
        if (!$this->hasRef($type)) {
            throw new Exception('Model does not have ' . $type . ' reference in ' . __FUNCTION__);
        }
        foreach ($this->ref($type) as $a) {
            if (!empty($a->get('value'))) {
                return $a->get('value');
            }
        }
        return '';
    }


    /*
     * returns first Email/Phone/Address that is not empty if found,
     * else an empty string
     *
     * @return string
     */
    public function getFirstPhone()
    {
        return $this->_getFirstEPA('Phone');
    }

    public function getFirstEmail()
    {
        return $this->_getFirstEPA('Email');
    }

    public function getFirstAddress()
    {
        return $this->_getFirstEPA('Address');
    }


    /*
     * Helper function for getEmailById, getAddressById, getPhoneById
     *
     * @return string
     */
    protected function _getEPAById(string $type, int $id)
    {
        if (!$this->hasRef($type)) {
            throw new Exception('Model does not have ' . $type . ' reference in ' . __FUNCTION__);
        }
        foreach ($this->ref($type) as $a) {
            if ($a->get('id') == $id) {
                return $a->get('value');
            }
        }

        throw new Exception('The loaded object does not have the ' . $type . '-Reference with id ' . $id);
    }


    /*
     * returns the Email/Phone/Address with id $id. Only returns a value
     * if EPA is referenced with model, otherwise empty string.
     * TODO: If ref is not found, return false or throw exception?
     *
     * @param int id    The id of the Email/Phone/Address to load
     *
     * @return string
     */
    public function getPhoneById($id)
    {
        return $this->_getEPAById('Phone', $id);
    }

    public function getEmailById($id)
    {
        return $this->_getEPAById('Email', $id);
    }

    public function getAddressById($id)
    {
        return $this->_getEPAById('Address', $id);
    }


    /*
     * Convenience functions for adding, updating and deleting Email,
     * Phone and Addresses
     */
    public function addEmail($value)
    {
        return $this->createEPA('Email', $value);
    }

    public function addPhone($value)
    {
        return $this->createEPA('Phone', $value);
    }

    public function addAddress($value)
    {
        return $this->createEPA('Address', $value);
    }

    public function updateEmail($id, $value)
    {
        return $this->updateEPA('Email', $id, $value);
    }

    public function updatePhone($id, $value)
    {
        return $this->updateEPA('Phone', $id, $value);
    }

    public function updateAddress($id, $value)
    {
        return $this->updateEPA('Address', $id, $value);
    }

    public function deleteEmail($id)
    {
        return $this->deleteEPA('Email', $id);
    }

    public function deletePhone($id)
    {
        return $this->deleteEPA('Phone', $id);
    }

    public function deleteAddress($id)
    {
        return $this->deleteEPA('Address', $id);
    }


    /*
     * creates a new referenced Email, Phone or Address
     */
    public function createEPA(string $type, $value)
    {
        //check if reference exists
        if (!$this->hasRef($type)) {
            throw new Exception('The model does not have the reference ' . $type);
        }

        //if value is empty, do not do anything
        if (empty($value)) {
            return null;
        }

        $classname = get_class($this->refModel($type));
        //if $this has no ID yet, use afterSave hook
        if (!$this->loaded()) {
            $this->onHook(
                'afterSave',
                function ($m, $isUpdate) use ($type, $value) {
                    $m->createEPA($type, $value);
                }
            );
            return null;
        }
        //if ID is available, do now
        else {
            $new_epa = new $classname($this->persistence);
            $new_epa->set('value', $value);
            $modelClass = $this->_epaRefModelClass ? : get_class($this);
            $modelId = $this->get($this->_epaRefModelIdField ? : 'id');
            $new_epa->set('model_class', $modelClass);
            $new_epa->set('model_id', $modelId);
            if(!$new_epa->get('model_id')
                || !$new_epa->get('model_class')) {
                throw new Exception('Both model_class and model_id need to have a value');
            }
            $new_epa->save();
            if (method_exists($this, 'addSecondaryAudit')) {
                $this->addSecondaryAudit('ADD', $new_epa, 'value', $modelClass, $modelId);
            }

            return clone $new_epa;
        }
    }


    /*
     * Edits/Deletes referenced Emails, Phones, Addresses
     *
     * @param string type Either Email, Phone or Address
     * @param mixel id
     * @param string value the new value
     *
     * @return bool
     */
    public function updateEPA(string $type, int $id, string $value): bool
    {
        //check if reference exists
        if (!$this->hasRef($type)) {
            throw new Exception('The model does not have the reference ' . $type);
        }

        $epa = $this->ref($type);

        //check if record exists
        $epa->tryLoad($id);
        if (!$epa->loaded()) {
            return false;
        }

        //update if value does not match stored one, take care of damn new line signs
        if (preg_replace('~\r\n?~', "\n", $value) !== preg_replace('~\r\n?~', "\n", $epa->get('value'))) {
            $epa->set('value', $value);
            if (method_exists($this, 'addSecondaryAudit')) {
                $modelClass = $this->_epaRefModelClass ? : get_class($this);
                $modelId = $this->get($this->_epaRefModelIdField ? : 'id');
                $this->addSecondaryAudit('CHANGE', $epa, 'value', $modelClass, $modelId);
            }
            $epa->save();
        }

        return true;
    }


    /*
     * deletes a referenced EPA
     *
     * @param string type Either Email, Phone or Address
     * @param int id      the ID of the EPA to delete
     *
     * @return bool
     */
    public function deleteEPA(string $type, $id): bool
    {
        //check if reference exists
        if (!$this->hasRef($type)) {
            throw new Exception('The model does not have the reference ' . $type);
        }

        $epa = $this->ref($type);
        //check if record exists
        $epa->tryLoad($id);
        if (!$epa->loaded()) {
            return false;
        }

        $clone = clone $epa;
        $epa->delete();
        if (method_exists($this, 'addSecondaryAudit')) {
            $modelClass = $this->_epaRefModelClass ? : get_class($this);
            $modelId = $this->get($this->_epaRefModelIdField ? : 'id');
            $this->addSecondaryAudit('REMOVE', $clone, 'value', $modelClass, $modelId);
        }

        return true;
    }


    /**
     *
     */
    public function addEPAExpressions()
    {
        $this->addExpression(
            'emails',
            [
                $this->refLink('Email')
                    ->action('fx0', ['group_concat', 'value']),
                'type' => 'string',
                'system' => true,
            ]
        );

        $this->addExpression(
            'phones',
            [
                $this->refLink('Phone')
                    ->action('fx0', ['group_concat', 'value']),
                'type' => 'string',
                'system' => true,
            ]
        );

        $this->addExpression(
            'addresses',
            [
                $this->refLink('Address')
                    ->action('fx0', ['group_concat', 'value']),
                'type' => 'string',
                'system' => true,
            ]
        );
    }


    /**
     *
     */
    public function getEmailsAsArray(): array
    {
        return $this->_getEPAsAsArray('Email');
    }


    /**
     *
     */
    public function getPhonesAsArray(): array
    {
        return $this->_getEPAsAsArray('Phone');
    }


    /**
     *
     */
    public function getAddressesAsArray(): array
    {
        return $this->_getEPAsAsArray('Address');
    }


    /**
     *
     */
    protected function _getEPAsAsArray(string $epa): array
    {
        return array_map(
            function ($a) {
                return $a['value'];
            },
            $this->ref($epa)->export(['value'])
        );
    }
}
