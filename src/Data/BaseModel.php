<?php

namespace PMRAtk\Data;

class BaseModel extends \atk4\data\Model {

    //can be set to true by system code to always allow saving no matter what
    //user rights for saving are set. Handle with care
    public $maySave = false;


    /*
     *
     */
    public function init() {
        parent::init();

        // Adds created_date and created_by field to model
        $this->addFields([
            ['created_date', 'type' => 'datetime', 'default' => date('Y-m-d H:i:s', time()), 'persist_timezone' => 'Europe/Berlin', 'system' => true],
            ['last_updated', 'type' => 'datetime', 'persist_timezone' => 'Europe/Berlin', 'system' => true],
        ]);

        //save last_updated on update
        $this->addHook('beforeUpdate', function($m, &$data) {
            $data['last_updated'] = date('Y-m-d H:i:s', time());
        });

        //before load check if its allowed
        $this->addHook('beforeLoad', function($m) {
            if(!$m->userHasRight('read')) {
                throw new \PMRAtk\Data\UserException('Du bist nicht berechtigt diesen Eintrag zu laden');
            }
        });

        //before create check if its allowed
        $this->addHook('beforeInsert', function($m) {
            if($this->maySave) {
                return;
            }
            if(!$m->userHasRight('create')) {
                throw new \PMRAtk\Data\UserException('Du bist nicht berechtigt diesen Eintrag zu speichern');
            }
        });

        //before update check if its allowed
        $this->addHook('beforeUpdate', function($m) {
            if($this->maySave) {
                return;
            }
            if(!$m->userHasRight('update')) {
                throw new \PMRAtk\Data\UserException('Du bist nicht berechtigt diesen Eintrag zu speichern');
            }
        });

        //before delete check if its allowed
        $this->addHook('beforeDelete', function($m) {
            if($m->maySave) {
                return;
            }
            if(!$m->userHasRight('delete')) {
                throw new \PMRAtk\Data\UserException('Du bist nicht berechtigt diesen Eintrag zu löschen');
            }
        });
    }


    /*
     * loads all available values from given ID if found including Emails,
     * Addresses and Phone numbers. Does not Copy Files or MtoMs (Tours, Guests)
     *
     * @param mixed record   The object or its id to be copied
     *
     * @return object record Returns the record that was copied from
     */
    public function createCopyFromOtherRecord($record) {
        //load by id if only id is passed
        if(!is_object($record)) {
            $id = $record;
            $record = $this->newInstance();
            $record->tryLoad($id);
        }

        //record needs to be loaded
        if(!$record->loaded()) {
            throw new \atk4\data\Exception('The Object to create a copy from needs to be loaded in '.__FUNCTION__);
        }

        foreach($record->elements as $field) {
            if(!$field instanceOf \atk4\data\Field) {
                continue;
            }
            //copy all field values that make sense
            if($field->short_name !== 'id'
            && $field->short_name !== 'created_by'
            && $field->short_name !== 'created_date'
            && $field->short_name !== 'last_updated'
            && $field->short_name !== 'crypt_id'
            && $field->short_name !== 'code'
            && $field->read_only  !== true
            && $this->hasElement($field->short_name)) {
                $this->set($field->short_name, $field->get());
            }
        }

        //if model has name, add (Kopie)
        if($this->hasElement('name')) {
            $this->set('name', $this->get('name').' (Kopie)');
        }

        //Copy Emails, Addresses and Phone Numbers
        //add as hoook because $this->id might not be known at this point
        if($this->hasRef('Email')) {
            $this->addHook('afterSave', function($m) use($record) {
                foreach($record->ref('Email') as $m) {
                    $e = new Email($this->persistence, ['parentObject' => $this]);
                    $e->set('value', $m->get('value'));
                    $e->save();
                }
            });
        }
        if($this->hasRef('Phone')) {
            $this->addHook('afterSave', function($m) use($record) {
                foreach($record->ref('Phone') as $m) {
                    $e = new Phone($this->persistence, ['parentObject' => $this]);
                    $e->set('value', $m->get('value'));
                    $e->save();
                }
            });
        }
        if($this->hasRef('Address')) {
            $this->addHook('afterSave', function($m) use($record) {
                foreach($record->ref('Address') as $m) {
                    $e = new Address($this->persistence, ['parentObject' => $this]);
                    $e->set('value', $m->get('value'));
                    $e->save();
                }
            });
        }

        return $record;
    }


    /*
     * returns if the logged in user has the right to to the action passed
     */
    public function userHasRight(string $right):bool {
        if($right === 'create') {
            return $this->_userHasCreateRight();
        }
        if($right === 'read') {
            return $this->_userHasReadRight();
        }
        if($right === 'update') {
            return $this->_userHasUpdateRight();
        }
        if($right === 'delete') {
            return $this->_userHasDeleteRight();
        }
        return false;
    }


    /*
     * checks if user has create rights
     */
    protected function _userHasCreateRight():bool {
        return true;
    }


    /*
     * checks if user has read rights
     */
    protected function _userHasReadRight():bool {
        return true;
    }


    /*
     * checks if user has update rights
     */
    protected function _userHasUpdateRight():bool {
        return $this->_standardUserRights();
    }


    /*
     * checks if user has delete rights
     */
    protected function _userHasDeleteRight():bool {
        return $this->_standardUserRights();
    }


    /*
     * standard user rights: Without login nothing, admin everything,
     * normal user may only do if hes the owner of the record
     */
    protected function _standardUserRights() {
        //no logged in user?
        if(!$this->app->auth->user) {
            return false;
        }

        return true;
    }
}
