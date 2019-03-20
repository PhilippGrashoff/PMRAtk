<?php
namespace PMRAtk\Data;

class API extends \atk4\api\Api {

    // to have normal data-level user rights, user has to be added to app
    public $app;


    /*
     *
     */
    public function __construct(\atk4\ui\App $app, $request = null) {
        parent::__construct($request);
        $this->app = $app;
        $this->tokenLogin('token');
        $this->_removeURLParamsFromPath();
        $this->setupApiEndPoints();
    }


    /*
     * remove ? and all thats behind from path and save into $this->args
     */
    protected function _removeURLParamsFromPath() {
        if(strpos($this->path, '?') !== false) {
            $this->path = substr($this->path, 0, strpos($this->path, '?'));
        }
    }


    /*
     *  This Api always need Authentication passed by token
     */
    public function tokenLogin(string $login_field = 'token') {
        try {
            if(!isset($_REQUEST[$login_field])) {
                throw new \atk4\data\Exception('The required authentication '.$login_field.' was not send in request', 405);
            }
            //throws Exception if user couldnt be loaded by Token
            $this->app->loadUserByToken($_REQUEST[$login_field]);
        }
        catch(\Exception $e) {
            $this->caughtException($e);
        }
    }


    /*
     * extend standard functionality by 2 things:
     * 1) export date and time fields as strings
     * 2) use model id as array key
     */
    protected function exportModel(\atk4\data\Model $m) {
        //scan for date and time fields
        $datetime_fields = [];
        $date_fields = [];
        $time_fields = [];
        foreach($m->elements as $elem) {
            if(!$elem instanceOf \atk4\data\Field) {
                continue;
            }
            if($elem->type == 'datetime') {
                $datetime_fields[] = $elem->short_name;
            }
            if($elem->type == 'date') {
                $date_fields[] = $elem->short_name;
            }
            if($elem->type == 'time') {
                $time_fields[] = $elem->short_name;
            }
        }


        //no date and time fields? just export
        if(!$datetime_fields && !$date_fields && !$time_fields) {
            return $m->export($this->getAllowedFields($m, 'read'), $m->id_field);
        }
        //else transform date and time fields
        else {
            $export = $m->export($this->getAllowedFields($m, 'read'), $m->id_field);
            foreach($export as &$record) {
                foreach($datetime_fields as $field_name) {
                    if(isset($record[$field_name]) && $record[$field_name] instanceOf \DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format(DATE_ATOM);
                    }
                }
                foreach($date_fields as $field_name) {
                    if(isset($record[$field_name]) && $record[$field_name] instanceOf \DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format('Y-m-d');
                    }
                }
                foreach($time_fields as $field_name) {
                    if(isset($record[$field_name]) && $record[$field_name] instanceOf \DateTimeInterFace) {
                        $record[$field_name] = $record[$field_name]->format('H:i:s');
                    }
                }
            }

            return $export;
        }
    }


    /*
     * Overwrite this function to set up custom Api Endpoints
     */
    public function setupApiEndPoints() {

    }
}