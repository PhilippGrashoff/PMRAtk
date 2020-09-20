<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\Data\File;
use PMRAtk\Data\UserException;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\BaseModelClasses\FileMock;


class FileRelationTraitTest extends TestCase {

    public function testAddUploadedFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new BaseModelB(self::$app->db);
        self::assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }

    public function testRemoveFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $f = $this->createTestFile('Hansi', '', $m);
        self::assertEquals($m->ref('File')->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        self::assertEquals($m->ref('File')->action('count')->getOne(), 0);
    }

    public function testExceptionNonExistingFile() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $this->expectException(UserException::class);
        $m->removeFile(23432543635);
    }

    public function testaddUploadFileViaHookOnSave() {
        $m = new BaseModelB(self::$app->db);
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m->save();

        self::assertTrue(true);
    }

    public function testaddUploadFileFromAtkUi() {
        $m = new BaseModelB(self::$app->db);
        $m->save();
        $m->getRef('File')->model = new FileMock(self::$app->db);
        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/']);
        self::assertInstanceOf(File::class, $file);
        self::assertEquals(1 ,$m->ref('File')->action('count')->getOne());
    }

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
