<?php

namespace PMRAtk\Data\Traits;

trait FieldChangedMessageTrait {

    /*
     * adds a Message to the app that a Field value was changed. This can be used if Data level
     * automatically updates field value to inform user about it
     */
    public function addFieldChangedMessage(string $field_name, $old_value, $new_value, string $class = 'warning') {
        //DateTimeHelpersTrait needs to be used!
        if(!method_exists($this, 'castDateTimeToGermanString')) {
            throw new \atk4\data\Exception('The Trait \PMRAtk\Data\Traits\DateTimeHelpersTrait needs to be used in order to use FieldChangedMessageTrait');
        }

        //nothing changed? no message
        if($old_value == $new_value) {
            return;
        }

        //value special if its a hasOne relation
        if($this->hasRef($field_name) && $this->getRef($field_name) instanceOf \atk4\data\Reference\HasOne) {
            $refmodel = $this->refModel($field_name);
            $old_value = $refmodel->tryLoad($old_value)->get($refmodel->title_field);
            $new_value = $refmodel->tryLoad($new_value)->get($refmodel->title_field);
        }

        //date handling
        if(in_array($this->getElement($field_name)->type, ['date', 'time', 'datetime']) && $old_value instanceOf \DateTimeInterFace) {
            $old_value = $this->castDateTimeToGermanString($old_value, $this->getElement($field_name)->type);
        }
        if(in_array($this->getElement($field_name)->type, ['date', 'time', 'datetime']) && $new_value instanceOf \DateTimeInterFace) {
            $new_value = $this->castDateTimeToGermanString($new_value, $this->getElement($field_name)->type);
        }

        $this->app->addUserMessage($this->elements[$field_name]->getCaption().' wurde geändert von '.$old_value.' in '.$new_value, $class);
    }
}