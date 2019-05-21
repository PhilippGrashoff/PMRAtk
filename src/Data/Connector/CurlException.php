<?php

namespace PMRAtk\Data\Connector;

/*
 * This class is used to map errors of the curl library into Exception.
 * The param array of atk4\core\Exception always has 'response' which is
 * body if the response. In case of a network error, it contains
 */
class CurlException extends \atk4\core\Exception {


    /*
     * constructor gets params array as third param
     */
    public function __construct(string $message, int $error_code, $response, \Throwable $previous = null) {
        parent::__construct($message, $error_code, $previous);
        $this->addMoreInfo('response', $response);
    }


    /*
     * Most APIs return some JSON explaining the error. If found, return that,
     * else the standard message
     */
    public function getErrorMessage():string {
        if(($r = json_decode($this->getParams()['response'])) !== null) {
            //try finding some property that contains "error"
            if(is_object($r)) {
                return $this->_findErrorMessage(get_object_vars($r));
            }
            elseif(is_array($r)) {
                return $this->_findErrorMessage($r);
            }
        }

        return (string) $this->getParams()['response'];
    }


    /*
     * in a given error, try to find a key containing "error" and return its value as string
     * If not found, return normal Message
     */
    protected function _findErrorMessage(array $a):string {
        foreach($a as $key => $value) {
            if(strpos($key, 'error') !== false
            || strpos($key, 'message') !== false) {
                if(is_string($value)) {
                    return $value;
                }
                elseif(is_array($value)) {
                    return $this->_findErrorMessage($value);
                }
                elseif(is_object($value)) {
                    return $this->_findErrorMessage(get_object_vars($value));
                }
            }
        }

        return $this->getMessage();
    }
}