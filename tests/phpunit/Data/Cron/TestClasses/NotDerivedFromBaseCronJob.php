<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Cron\TestClasses;


/**
 * Dummy Cron for Tests
 */
class NotDerivedFromBaseCronJob {

    public $name = 'SomeTestCron';

    public $description = 'SomeDescriptionExplainingWhatThisIsDoing';

    protected function _execute() {

    }
}

