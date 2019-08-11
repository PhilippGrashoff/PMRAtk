<?php

namespace PMRAtk\View\Traits;

trait OutputExceptionTrait {

    /*
     *
     */
    public function outputExceptionAsJsNotify(\Throwable $e, string $text_before = ''):array {
        $return = [];
        foreach($this->outputException($e, $text_before) as $message) {
            $return[] = $this->failNotify($message);
        }

        return $return;
    }


    /*
     *
     */
    public function outputException(\Throwable $e, string $text_before = ''):array {
        $return = [];
        $r = new \ReflectionClass($e);

        //ValidationException should render each message
        if($r->getName() == 'atk4\data\ValidationException') {
            //more than one field has bad value
            if(isset($e->errors)
            && is_array($e->errors)) {
                foreach($e->errors as $error) {
                    $return[] = $text_before.': '.$error;
                }
            }
            //single error
            else {
                $return[] = $text_before.': '.$e->getMessage();
            }
        }

        //other exception meant for user
        elseif($r->getName() == 'PMRAtk\Data\UserException') {
            $return[] = $text_before.': '.$e->getMessage();
        }

        //any other Exception renders as technical error
        else {
            $return[] = $text_before.': Ein technischer Fehler ist aufgetreten. Bitte versuche es erneut. Der Administrator wurde informiert.';
        }

        return $return;
    }
}