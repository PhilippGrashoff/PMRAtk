<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Model;
use DateTimeInterFace;
use Exception;
use PMRAtk\App\App;

class Api extends \atk4\api\Api
{

    // to have normal data-level user rights, user has to be added to app
    public $app;

    public function __construct(App $app)
    {
        parent::__construct();
        $this->app = $app;
        $app->isApiRequest = true;
        $this->tokenLogin('token');
        $this->_removeURLParamsFromPath();
        $this->setupApiEndPoints();
    }

    /**
     * remove ? and all thats behind from path and save into $this->args
     */
    protected function _removeURLParamsFromPath()
    {
        if (strpos($this->path, '?') !== false) {
            $this->path = substr($this->path, 0, strpos($this->path, '?'));
        }
    }

    /**
     *  This Api always need Authentication passed by token
     */
    public function tokenLogin(string $login_field = 'token')
    {
        try {
            if (!isset($_REQUEST[$login_field])) {
                throw new \atk4\data\Exception(
                    'The required authentication ' . $login_field . ' was not sent in request', 401
                );
            }
            //throws Exception if user couldnt be loaded by Token
            $this->app->loadUserByToken($_REQUEST[$login_field]);
        } catch (Exception $e) {
            $this->caughtException($e);
        }
    }

    /**
     * export date and time fields as strings in ISO Code
     * //TODO This should go in ATK Api
     */
    protected function exportModel(Model $m)
    {
        //scan for date and time fields
        $datetime_fields = [];
        $date_fields = [];
        $time_fields = [];
        foreach ($m->getFields() as $elem) {
            if ($elem->type == 'datetime') {
                $datetime_fields[] = $elem->short_name;
            }
            if ($elem->type == 'date') {
                $date_fields[] = $elem->short_name;
            }
            if ($elem->type == 'time') {
                $time_fields[] = $elem->short_name;
            }
        }


        //no date and time fields? just export
        if (!$datetime_fields && !$date_fields && !$time_fields) {
            return $m->export($this->getAllowedFields($m, 'read'));
        } //else transform date and time fields
        else {
            $export = $m->export($this->getAllowedFields($m, 'read'));
            foreach ($export as &$record) {
                foreach ($datetime_fields as $field_name) {
                    if (isset($record[$field_name]) && $record[$field_name] instanceof DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format(DATE_ATOM);
                    }
                }
                foreach ($date_fields as $field_name) {
                    if (isset($record[$field_name]) && $record[$field_name] instanceof DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format('Y-m-d');
                    }
                }
                foreach ($time_fields as $field_name) {
                    if (isset($record[$field_name]) && $record[$field_name] instanceof DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format('H:i:s');
                    }
                }
            }

            return $export;
        }
    }

    public function setupApiEndPoints()
    {
    }
}