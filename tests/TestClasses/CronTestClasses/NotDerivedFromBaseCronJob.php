<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\CronTestClasses;


class NotDerivedFromBaseCronJob {

    public $name = 'SomeTestCron';

    public $description = 'SomeDescriptionExplainingWhatThisIsDoing';

    protected function _execute() {

    }
}

