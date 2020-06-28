<?php

namespace PMRAtk\Data\Connector;

trait CurlTrait {

    // Curl\Curl instance, set in constructor
    public $curl;


    /*
     * This function takes care of how all functions making api calls respond:
     * 1) If the request itself failed (e.g. timeout), throw exception with code 0
     * 2) If some error was returned by Api, throw exception with code 1
     * 3) else, return the response
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function returnCurlResponse($dataSent = null) {
        //if curl could not succeed (e.g. timeout), throw exception
        if($this->curl->curlError) {
            throw new \PMRAtk\Data\Connector\CurlException('The Api request in '.debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']. 'was not successful', 0, 'Verbindungsfehler');
        }
        //if some error http status was returned by Api, return false;
        elseif($this->curl->error) {
            $message = 'The Server returned the HTTP error code '.$this->curl->httpStatusCode;
            if($this->curl->response) {
                //TODO: This should go to error log
                $message .= ' with the message: '.var_export($this->curl->response, true);
            }
            $message .= ' in '.debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];

            throw new \PMRAtk\Data\Connector\CurlException($message, ($this->curl->httpStatusCode ? : 1), $this->curl->response, $dataSent);
        }

        //else, return response
        return $this->curl->response;
    }
}