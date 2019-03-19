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
}