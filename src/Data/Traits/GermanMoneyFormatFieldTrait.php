<?php declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use atk4\data\Field;

trait GermanMoneyFormatFieldTrait {

    /*
     *
     */
    protected function _germanPriceForMoneyField(Field $field) {
        $field->typecast = [
            null,
            function($value, $field, $persistence) {
                return round((float) str_replace(",",".", $value), 4);
            },
        ];
    }
}