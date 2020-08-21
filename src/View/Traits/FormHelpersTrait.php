<?php declare(strict_types=1);

namespace PMRAtk\View\Traits;

use atk4\ui\Form;

trait FormHelpersTrait {

    /*
     * Sets the HTML id for each field same as field's short name. Very handy
     * for selenium tests and custom JS
     */
    public function setHTMLIds(Form $form) {
        foreach($form->fields as $field) {
            $field->id = $field->name = $field->short_name;
        }

        //submit button
        if($form->buttonSave) {
            $form->buttonSave->id = $form->buttonSave->name = $form->id.'_submit';
        }
    }
}