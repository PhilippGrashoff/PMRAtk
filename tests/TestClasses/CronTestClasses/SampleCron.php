<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\CronTestClasses;

use PMRAtk\Data\Cron\BaseCronJob;


class SampleCron extends BaseCronJob {

    public $name = 'SomeTestCron';

    public $description = 'SomeDescriptionExplainingWhatThisIsDoing';

    protected function _execute() {

    }
}

