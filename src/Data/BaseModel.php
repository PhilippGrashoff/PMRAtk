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
            ['created_date', 'type' => 'datetime', 'persist_timezone' => 'Europe/Berlin', 'system' => true],
            ['last_updated', 'type' => 'datetime', 'persist_timezone' => 'Europe/Berlin', 'system' => true],
        ]);

        //save created date
        $this->addHook('beforeInsert', function($m, &$data) {
            if(!$data['created_date']) {
                $data['created_date'] = new \DateTime();
            }
        });

        //save last_updated on update
        $this->addHook('beforeUpdate', function($m, &$data) {
            $data['last_updated'] = new \DateTime();
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

        foreach($record->getFields() as $field) {

            //copy all field values that make sense
            if($field->short_name !== 'id'
            && $field->short_name !== 'created_by'
            && $field->short_name !== 'created_date'
            && $field->short_name !== 'last_updated'
            && $field->short_name !== 'crypt_id'
            && $field->short_name !== 'code'
            && $field->read_only  !== true
            && $this->hasField($field->short_name)) {
                $this->set($field->short_name, $field->get());
            }
        }

        //if model has name, add (Kopie)
        if($this->hasField('name')) {
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
        $this->_exceptionIfThisNotLoaded();

        //distinguish MtoM and 1toM relations, only use each for MtoM
        if($this->getRef($ref_name) instanceOf \atk4\data\Reference\HasMany) {
            $this->ref($ref_name)->each('delete');
            return true;
        }

        throw new \atk4\data\Exception('The Reference '.$ref_name.' is not of type \atk4\data\Reference\HasMany in '.__FUNCTION__);
    }


    /*
     * simply checks if $this is loaded, if not, throws exception
     */
    protected function _exceptionIfThisNotLoaded() {
        if(!$this->loaded()) {
            throw new \atk4\data\Exception('$this needs to be loaded in '.debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']);
        }
    }


    /*
     * makes sure that a hasOne reference is loaded, if not throws exception. Workaround for https://github.com/atk4/data/issues/335
     */
    public function loadedHasOneRef(string $ref_name) {
        $model = $this->ref($ref_name);
        if(!$model->loaded()) {
            throw new \atk4\data\Exception('HasOne Reference Model '.$ref_name.' with id '.$this->get($ref_name).' could not be loaded');
        }
        return $model;
    }
}
