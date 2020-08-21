<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\Data\File;
use PMRAtk\Data\UserException;
use PMRAtk\tests\phpunit\Data\BaseModelB;
use PMRAtk\tests\phpunit\TestCase;

/**
 *
 */
class FileMock extends File {

    public function uploadFile($f)
    {
        $this->set('value', $f['name']);
        $this->set('path', $f['path']);
        return true;
    }
}


/**
 *
 */
class FileReferenceTest extends TestCase {

    /*
     * tests the addRecipient and removeRecipient Function passing various params
     */
    public function testAddUploadedFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new BaseModelB(self::$app->db);
        $this->assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }


    /*
     *
     */
    public function testRemoveFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $f = $this->createTestFile('Hansi', '', $m);
        $this->assertEquals($m->ref('File')->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        $this->assertEquals($m->ref('File')->action('count')->getOne(), 0);
    }


    /*
     * trying to delete a non-related file using removeFile will throw exception
     */
    public function testExceptionNonExistingFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $this->expectException(UserException::class);
        $m->removeFile(23432543635);
    }


    /*
     * trying to delete a non-related file using removeFile will throw exception
     */
    public function testaddUploadFileViaHookOnSave() {
        $m = new BaseModelB(self::$app->db);
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m->save();

        self::assertTrue(true);
    }


    /*
     *
     */
    public function testaddUploadFileFromAtkUi() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $m->getRef('File')->model = new  FileMock(self::$app->db);
        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/']);
        self::assertInstanceOf(File::class, $file);
        self::assertEquals(1 ,$m->ref('File')->action('count')->getOne());
    }


    /*
     *
     */
    public function testAddTypeToFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $m->getRef('File')->model = new  FileMock(self::$app->db);
        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/']);
        self::assertEquals('', $file->get('type'));

        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/'], 'SOMETYPE');
        self::assertEquals('SOMETYPE', $file->get('type'));
    }
}
