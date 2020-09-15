<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\core\AppScopeTrait;
use atk4\data\Exception;
use atk4\data\Model;
use traitsforatkdata\CreatedDateAndLastUpdatedTrait;


/**
 *
 */
abstract class BaseModel extends Model
{

    use CreatedDateAndLastUpdatedTrait;
    use AppScopeTrait;


    /**
     * add App to each model. Used to get Settings and logged in user
     */
    public function __construct($persistence = null, $defaults = [])
    {
        if(isset($persistence->app)) {
            $this->app = $persistence->app;
        }
        parent::__construct($persistence, $defaults);
    }

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
