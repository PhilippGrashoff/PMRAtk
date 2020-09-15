<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\CronTestClasses;

use PMRAtk\Data\Cron\BaseCronJob;


class SampleCronWithEmailMessage extends BaseCronJob {

    public $name = 'TestName';

    public function _execute() {
        $this->recipients[] = 'test2@easyoutdooroffice.com';
        $this->app->addUserMessage('Test Test');
    }
}
