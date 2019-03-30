<?php

class FileTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testDelete() {
        $initial_file_count = (new \PMRAtk\Data\File(self::$app->db))->action('count')->getOne();
        //copy some file to use
        $f = new \PMRAtk\Data\File(self::$app->db);
        $f->createFileName('filetest.jpg');
        $this->_copyFile($f->get('value'));
        $f->save();
        $this->assertTrue($f->checkFileExists());
        $cf = clone $f;
        $f->delete();
        $this->assertFalse($cf->checkFileExists());
        $this->assertEquals($initial_file_count, (new \PMRAtk\Data\File(self::$app->db))->action('count')->getOne());
    }
}
