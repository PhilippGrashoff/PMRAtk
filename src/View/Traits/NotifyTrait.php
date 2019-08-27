<?php

namespace PMRAtk\View\Traits;

trait NotifyTrait {

    /*
     *
     */
    public function successNotify(string $text):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'success',
            'displayTime' => 3000]);
    }


    /*
     *
     */
    public function failNotify(string $text):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'error',
            'displayTime' => 8000]);
    }


    /*
     *
     */
    public function warningNotify(string $text):\atk4\ui\jsToast {
        return new \atk4\ui\jsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'warning',
            'displayTime' => 8000]);
    }
}