<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\CronTestClasses;

use PMRAtk\Data\Cron\BaseCronJob;


class SampleCronWithException extends BaseCronJob {

    public function _execute() {
        $this->recipients[] = 'test2@easyoutdooroffice.com';
        throw new \atk4\data\Exception('Some shit happened');
    }
}
