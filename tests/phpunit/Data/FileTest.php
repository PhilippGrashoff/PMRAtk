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


    /*
     * should return false
     */
    public function testDeleteNonExistantFile() {
        $f = new \PMRAtk\Data\File(self::$app->db);
        $f->set('value', 'SomeNonExistantFile');
        $this->assertFalse($f->deleteFile());
    }


    /*
     * test exception on save if file does not exist
     */
    public function testExceptionOnSaveNonExistantFile() {
        $f = new \PMRAtk\Data\File(self::$app->db);
        $f->set('value', 'FDFLKSD LFSDHF KSJB');
        $this->expectException(\atk4\data\Exception::class);
        $f->save();
    }


    /*
     *
     */
    public function testCreateNewFileNameIfExists() {
        $f = new \PMRAtk\Data\File(self::$app->db);
        $f1 = $this->createTestFile('LALA.jpg');
        $f->createFileName($f1->get('value'));
        $this->assertNotEquals($f->get('value'), $f1->get('value'));
    }


    /*
     *
     */
    public function testSaveStringToFile() {
        $f = new \PMRAtk\Data\File(self::$app->db);
        $this->assertTrue($f->saveStringToFile('JLADHDDFEJD'));
    }


    /*
     *
     */
    public function testGetLink() {
        $f = new \PMRAtk\Data\File(self::$app->db);
        $f->set('path', 'somepath/');
        $f->set('value', 'Logo.jpg');
        $this->assertEquals(URL_BASE_PATH.'somepath/Logo.jpg', $f->getLink());
    }
}
