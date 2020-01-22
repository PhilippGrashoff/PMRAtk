<?php

namespace PMRAtk\View\Traits;

trait NotifyTrait {

    /*
     *
     */
    public function successNotify(string $text, int $displayTime = 3000):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'success',
            'displayTime' => $displayTime]);
    }


    /*
     *
     */
    public function failNotify(string $text, int $displayTime = 10000):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'error',
            'displayTime' => $displayTime]);
    }


    /*
     *
     */
    public function warningNotify(string $text, int $displayTime = 7000):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'warning',
            'displayTime' => $displayTime]);
    }
}