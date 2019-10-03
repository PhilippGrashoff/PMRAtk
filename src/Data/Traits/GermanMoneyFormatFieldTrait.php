<?php

namespace PMRAtk\Data\Traits;

trait GermanMoneyFormatFieldTrait {

    /*
     *
     */
    protected function _germanPriceForMoneyField(\atk4\data\Field $field) {
        $field->typecast = [
            function($value, $field, $persistence) {
                return $value;
            },
            function($value, $field, $persistence) {
                if (!$persistence instanceof \atk4\ui\Persistence\UI) {
                    return $value;
                }
                return (float) str_replace(",",".", $value);
            },
        ];
    }
}