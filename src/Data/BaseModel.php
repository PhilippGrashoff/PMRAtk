<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\core\AppScopeTrait;
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
    use AppScopeTrait;


    /**
     *
     */
    public function __construct($persistence = null, $defaults = [])
    {
        parent::__construct($persistence, $defaults);
        $this->app = $persistence->app;
    }


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
