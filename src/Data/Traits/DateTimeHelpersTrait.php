<?php

namespace PMRAtk\Data\Traits;

trait DateTimeHelpersTrait {


    /*
     * returns the difference of 2 datetime objects in minutes
     */
    public function getDateDiffTotalMinutes(\DateTimeInterFace $s, \DateTimeInterFace $e) {
        $diff = $s->diff($e);
        return $diff->days*24*60+$diff->h*60+$diff->i;
    }


    /*
     * makes german formatted strings from date, time and datetime fields
     */
    public function castDateTimeToGermanString($value, string $type):string {
        //no DateTimeInterFace passed? Just return given value
        if(!$value instanceOf \DateTimeInterFace) {
            return $value;
        }

        if($type == 'datetime') {
            return $value->format('d.m.Y H:i:s');
        }
        if($type == 'date') {
            return $value->format('d.m.Y');
        }
        if($type == 'time') {
            return $value->format('H:i:s');
        }
        return '';
    }
}