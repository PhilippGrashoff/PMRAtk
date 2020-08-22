<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use PMRAtk\Data\File;
use PMRAtk\tests\phpunit\TestCase;

/**
 *
 */
class FileTest extends TestCase {

    /**
     *
     */
    public function testDelete() {
        $initial_file_count = (new File(self::$app->db))->action('count')->getOne();
        //copy some file to use
        $f = new File(self::$app->db);
        $f->createFileName('filetest.jpg');
        $this->_copyFile($f->get('value'));
        $f->save();
        $this->assertTrue($f->checkFileExists());
        $cf = clone $f;
        $f->delete();
        $this->assertFalse($cf->checkFileExists());
        $this->assertEquals($initial_file_count, (new File(self::$app->db))->action('count')->getOne());
    }


    /**
     * should return false
     */
    public function testDeleteNonExistantFile() {
        $f = new File(self::$app->db);
        $f->set('value', 'SomeNonExistantFile');
        $this->assertFalse($f->deleteFile());
    }


    /**
     * test exception on save if file does not exist
     */
    public function testExceptionOnSaveNonExistantFile() {
        $f = new File(self::$app->db);
        $f->set('value', 'FDFLKSD LFSDHF KSJB');
        $this->expectException(\atk4\data\Exception::class);
        $f->save();
    }


    /**
     *
     */
    public function testCreateNewFileNameIfExists() {
        $f = new File(self::$app->db);
        $f1 = $this->createTestFile('LALA.jpg');
        $f->createFileName($f1->get('value'));
        $this->assertNotEquals($f->get('value'), $f1->get('value'));
        $this->assertEquals($f->get('filetype'), $f1->get('filetype'));
    }


    /*
     *
     */
    public function testSaveStringToFile() {
        $f = new File(self::$app->db);
        $this->assertTrue($f->saveStringToFile('JLADHDDFEJD'));
    }


    /*
     *
     */
    public function testGetLink() {
        $f = new File(self::$app->db);
        $f->set('path', 'somepath/');
        $f->set('value', 'Logo.jpg');
        $this->assertEquals(URL_BASE_PATH.'somepath/Logo.jpg', $f->getLink());
    }


    /*
     *
     */
    public function testuploadFile() {
        $f = new File(self::$app->db);
        //false because move_uploaded_file knows it not an uploaded file
        $this->assertFalse($f->uploadFile(['name' => 'LALA', 'tmp_name' => 'sdfkjsdf.txt']));
    }


    /*
     *
     */
    public function testCryptId() {
        $g = new File(self::$app->db);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests/');
        $g->save();
        $c = $g->get('crypt_id');
        $this->assertEquals(21, strlen($g->get('crypt_id')));

        //see if it stays the same after another save
        $g->save();
        $this->assertEquals($c, $g->get('crypt_id'));
    }


    /*
     *
     */
    public function testCryptIdForRecordsWithoutCreatedOnLoad() {
        //id = 1 does not have a crypt_id
        $g = new File(self::$app->db);
        $g->load(1);

        //now a crypt_id should have been created and saved
        $this->assertEquals(21, strlen($g->get('crypt_id')));
    }


    /**
     * @throws \atk4\data\Exception
     */
    public function testDirectorySeparatorAddedToPath()
    {
        $g = new File(self::$app->db);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests');
        $g->save();
        self::assertEquals('tests/', $g->get('path'));
    }


    /**
     * @throws \atk4\data\Exception
     */
    public function testFileTypeSetIfNotThere()
    {
        $g = new File(self::$app->db);
        $g->set('value', 'demo_file.txt');
        $g->set('path', 'tests');
        $g->save();
        self::assertEquals('txt', $g->get('filetype'));
    }


    /*
     * File id = 2 should be saved after load due to missing crypt_id, but file does not exist.
     * Should be deleted and a message added to app
     */
    public function testNonExistantFileGetsDeletedOnUpdate() {
        $initial_file_count = $this->countModelRecords(File::class);
        $message_count = count(self::$app->userMessages);
        $g = new File(self::$app->db);
        $g->load(2);
        self::assertEquals($initial_file_count - 1, $this->countModelRecords(File::class));
        self::assertEquals($message_count + 1, count(self::$app->userMessages));
    }
}
