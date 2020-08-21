<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Cron\TestClasses;

use PMRAtk\Data\Cron\BaseCronJob;

/**
 * Dummy Cron for Tests
 */
class SampleCron extends BaseCronJob {

    public $name = 'SomeTestCron';

    public $description = 'SomeDescriptionExplainingWhatThisIsDoing';

    protected function _execute() {

    }
}

