<?php

namespace PMRAtk\Data\Traits;

trait GermanMoneyFormatFieldTrait {

    /*
     *
     */
    protected function _germanPriceForMoneyField(\atk4\data\Field $field) {
        $field->typecast = [
            null,
            function($value, $field, $persistence) {
                if (!$persistence instanceof \atk4\ui\Persistence\UI) {
                    return $value;
                }
                return round((float) str_replace(",",".", $value), 4);
            },
        ];
    }
}