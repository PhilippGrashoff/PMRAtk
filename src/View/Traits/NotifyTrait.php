<?php declare(strict_types=1);

namespace PMRAtk\View\Traits;

use atk4\ui\JsToast;

trait NotifyTrait {

    /*
     *
     */
    public function successNotify(string $text, int $displayTime = 3000): JsToast {
        return new JsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'success',
            'displayTime' => $displayTime]);
    }


    /*
     *
     */
    public function failNotify(string $text, int $displayTime = 10000): JsToast {
        return new JsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'error',
            'displayTime' => $displayTime]);
    }


    /*
     *
     */
    public function warningNotify(string $text, int $displayTime = 7000): JsToast {
        return new JsToast([
            'message' => $text,
            'position' => 'bottom right',
            'showProgress' => 'bottom',
            'class' => 'warning',
            'displayTime' => $displayTime]);
    }
}