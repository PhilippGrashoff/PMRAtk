<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\data\Reference\HasMany;
use PMRAtk\Data\Traits\CreatedDateAndLastUpdatedTrait;


/**
 *
 */
class BaseModel extends Model
{

    use CreatedDateAndLastUpdatedTrait;

    /**
     *
     */
    public function init(): void
    {
        parent::init();

        $this->addCreatedDateAndLastUpdateFields();
        $this->addCreatedDateAndLastUpdatedHook();
    }


    /**
     * loads all objects for this reference and deletes them one by one.
     * Like this, it is ensured the delete hooks of these objects are executed
     */

    public function deleteHasMany(string $refName): void
    {
        //first check if reference exists
        if (!$this->hasRef($refName)) {
            throw new Exception('The reference ' . $refName . ' is not defined for this class: ' . __CLASS__);
        }
        $this->_exceptionIfThisNotLoaded();

        //distinguish MtoM and 1toM relations, only use each for MtoM
        if($this->getRef($refName) instanceof HasMany) {
            $this->ref($refName)->each('delete');
        }
        else {
            throw new Exception('The Reference ' . $refName . ' is not of type \atk4\data\Reference\HasMany in ' . __FUNCTION__);
        }
    }


    /**
     * simply checks if $this is loaded, if not, throws exception
     */
    protected function _exceptionIfThisNotLoaded(): void
    {
        if (!$this->loaded()) {
            throw new Exception(
                '$this needs to be loaded in ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            );
        }
    }


    /**
     * makes sure that a hasOne reference is loaded, if not throws exception. Workaround for https://github.com/atk4/data/issues/335
     */
    public function loadedHasOneRef(string $ref_name): Model
    {
        $model = $this->ref($ref_name);
        if (!$model->loaded()) {
            throw new Exception(
                'HasOne Reference Model ' . $ref_name . ' with id ' . $this->get($ref_name) . ' could not be loaded'
            );
        }

        return $model;
    }
}
