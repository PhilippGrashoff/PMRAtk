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
     * //TODO: Pass field would make more sense
     */
    public function castDateTimeToGermanString($value, string $type, bool $shorten_time = false):string {
        //no DateTimeInterFace passed? Just return given value
        if(!$value instanceof \DateTimeInterface) {
            return (string) $value;
        }

        if($type == 'datetime') {
            if($shorten_time) {
                return $value->format('d.m.Y H:i');
            }
            else {
                return $value->format('d.m.Y H:i:s');
            }
        }
        if($type == 'date') {
            return $value->format('d.m.Y');
        }
        if($type == 'time') {
            if($shorten_time) {
                return $value->format('H:i');
            }
            else {
                return $value->format('H:i:s');
            }
        }

        return '';
    }
}