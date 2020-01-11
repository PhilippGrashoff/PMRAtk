<?php

namespace PMRAtk\tests\phpunit\Data\Cron;

use PMRAtk\Data\Cron\CronManager;
use PMRAtk\Data\Cron\BaseCronJob;


class CronManagerTest extends \PMRAtk\tests\phpunit\TestCase {

    /**
     *
     */
    public function testGetAvailableCrons() {
        $this->_addStandardEmailAccount();
        $cm = new CronManager(self::$app->db);
        $res = $cm->getAvailableCrons();
        self::assertTrue(array_key_exists('PMRAtk\Data\Cron\DBBackup', $res));
        self::assertFalse(array_key_exists('PMRAtk\Data\Cron\CronManager', $res));
    }


    /**
     * use DeleteUnsentBaseEmails Cron as it executes fast
     */
    public function testExecuteCron() {
        $this->_addStandardEmailAccount();
        $cm = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_MINUTE',
        ]);
        $cm->executeCron();
        self::assertEquals(1, count($cm->executedCrons));
        $cm->executeCron();
        self::assertEquals(1, count($cm->executedCrons));
        self::assertEquals(2, count($cm->executedCrons['PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron']));
    }


    /**
     *
     */
    public function testRunDaily() {
        $this->_addStandardEmailAccount();
        $testTime = new \DateTime();
        $testTime->setTime(3,3);
        //this one should be executed
        $cm1 = $this->_getRecord([
            'execute_daily' => 1,
            'time_daily' => '03:03',
        ]);
        $cm2 = $this->_getRecord([
            'execute_daily' => 1,
            'time_daily' => '03:02',
        ]);
        $cm3 = $this->_getRecord([
            'execute_daily' => 1,
            'time_daily' => '03:04',
        ]);

        //only one should be executed
        $cm = new CronManager(self::$app->db);
        $cm->run($testTime);
        self::assertEquals(1, count($cm->executedCrons['PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron']));
    }


    /**
     *
     */
    public function testRunHourly() {
        $this->_addStandardEmailAccount();
        $testTime = new \DateTime();
        $testTime->setTime(3,3);
        //this one should be executed
        $cm0 = $this->_getRecord([
            'execute_hourly' => 1,
            'minute_hourly' => 3,
        ]);
        //this one should be executed
        $cm1 = $this->_getRecord([
            'execute_hourly' => 1,
            'minute_hourly' => 3,
        ]);
        $cm2 = $this->_getRecord([
            'execute_hourly' => 1,
            'minute_hourly' => 2,
        ]);
        $cm3 = $this->_getRecord([
            'execute_hourly' => 1,
            'minute_hourly' => 4,
        ]);


        $cm = new CronManager(self::$app->db);
        $cm->run($testTime);
        self::assertEquals(2, count($cm->executedCrons['PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron']));
    }


    /**
     *
     */
    public function testRunMinutely() {
        $this->_addStandardEmailAccount();
        $testTime = new \DateTime();
        $testTime->setTime(3,16);
        //this one should be executed
        $cm0 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_MINUTE',
        ]);
        $cm1 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTH_MINUTE',
        ]);
        $cm3 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTEENTH_MINUTE',
        ]);


        $cm = new CronManager(self::$app->db);
        $cm->run($testTime);
        self::assertEquals(1, count($cm->executedCrons['PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron']));
    }


    /**
     *
     */
    public function testLastExecutedSaved() {
        $this->_addStandardEmailAccount();
        //this one should be executed
        $cm0 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_MINUTE',
        ]);

        $cm = new CronManager(self::$app->db);
        $cm->run();

        $cm0->reload();
        self::assertEquals((new \DateTime())->format('YmdHis'), $cm0->get('last_executed')->format('YmdHis'));
    }


    /**
     *
     */
    public function testRunMinutelyOffset() {
        $this->_addStandardEmailAccount();
        $testTime = new \DateTime();
        $testTime->setTime(3,18);
        //this one should be executed
        $cm1 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTH_MINUTE',
            'offset_minutely'   => 3,
        ]);
        //this one should be executed
        $cm3 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTEENTH_MINUTE',
            'offset_minutely'   => 3,
        ]);
        $cm2 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTH_MINUTE',
        ]);
        $cm4 = $this->_getRecord([
            'execute_minutely' => 1,
            'interval_minutely' => 'EVERY_FIFTEENTH_MINUTE',
        ]);


        $cm = new CronManager(self::$app->db);
        $cm->run($testTime);
        self::assertEquals(2, count($cm->executedCrons['PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron']));
    }


    /**
     *
     */
    public function testDescriptionLoadedOnInsert() {
        $this->_addStandardEmailAccount();
        $cm = new CronManager(self::$app->db);
        $cm->set('name', 'PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron');
        $cm->save();
        self::assertEquals($cm->get('description'), 'SomeDescriptionExplainingWhatThisIsDoing');
    }


    /**
     *
     */
    private function _getRecord(array $set):CronManager {
        $this->_addStandardEmailAccount();
        $cm = new CronManager(self::$app->db, ['cronFilesPath' => [
            'src/Data/Cron' => 'PMRAtk\\Data\\Cron',
            'tests/phpunit/Data/Cron/TestClasses' => 'PMRAtk\\tests\\phpunit\\Data\\Cron\\TestClasses',
        ]]);
        $cm->set('name', 'PMRAtk\tests\phpunit\Data\Cron\TestClasses\SampleCron');
        $cm->set('is_active', 1);
        $cm->set($set);
        $cm->save();
        return $cm;
    }
}