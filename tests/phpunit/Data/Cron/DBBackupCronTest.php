<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Cron;


use PMRAtk\Data\Cron\DBBackup;
use PMRAtk\tests\phpunit\TestCase;

/**
 *
 */
class DBBackupCronTest extends TestCase {

    /*
     *
     */
    public function testCron() {

        $this->_addStandardEmailAccount();
        $c = new DBBackup(self::$app);
        /*$c->execute();
        /*
        //backup file should be there
        $sqlgzfilefound = false;
        foreach(new \DirectoryIterator(FILE_BASE_PATH.CRON_FILE_PATH) as $file) {
            if($file->getExtension() == 'gz') {
                $sqlgzfilefound = true;
            }
        }
        $this->assertTrue($sqlgzfilefound);

        //delete again
        foreach(new \DirectoryIterator(FILE_BASE_PATH.CRON_FILE_PATH) as $file) {
            if($file->getExtension() == 'gz' || $file->getExtension() == 'sql') {
                unlink($file->getPathName());
            }
        }*/
    }
}
