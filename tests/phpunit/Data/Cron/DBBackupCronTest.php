<?php

class DBBackupCronTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testCron() {

        $this->_addStandardEmailAccount();
        $c = new \PMRAtk\Data\Cron\DBBackup(self::$app);
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
