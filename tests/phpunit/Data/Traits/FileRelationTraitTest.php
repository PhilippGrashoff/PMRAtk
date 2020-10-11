<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use auditforatk\Audit;
use PMRAtk\Data\File;
use PMRAtk\App\App;
use PMRAtk\tests\TestClasses\BaseModelClasses\ModelWithFileRelation;
use traitsforatkdata\UserException;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\BaseModelClasses\FileMock;


class FileRelationTraitTest extends TestCase
{

    private $app;
    private $persistence;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = new App(['nologin'], ['always_run' => false]);
        $this->persistence = $this->getSqliteTestPersistence();
        $this->app->db = $this->persistence;
        $this->persistence->app = $this->app;
    }

    protected $sqlitePersistenceModels = [
        File::class,
        ModelWithFileRelation::class,
        Audit::class
    ];

    public function testAddUploadedFile()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new ModelWithFileRelation($this->persistence);
        self::assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }

    public function testRemoveFile()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $f = $this->createTestFile('Hansi', $this->persistence, $m);
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        self::assertEquals($m->ref(File::class)->action('count')->getOne(), 0);
    }

    public function testExceptionNonExistingFile()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        self::expectException(UserException::class);
        $m->removeFile(23432543635);
    }

    public function testaddUploadFileViaHookOnSave()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m->save();

        self::assertTrue(true);
    }

    public function testFilesAreDeletedOnModelDelete() {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $this->createTestFile('somefile.jpg', $this->persistence, $m);
        $this->createTestFile('someotherfile.jpg', $this->persistence, $m);
        self::assertEquals(
            2,
            (new File($this->persistence))->action('count')->getOne()
        );

        $m->delete();

        self::assertEquals(
            0,
            (new File($this->persistence))->action('count')->getOne()
        );
    }

    public function testaddUploadFileFromAtkUi()
    {
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $m->getRef(File::class)->model = new FileMock($this->persistence);
        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/']);

        self::assertInstanceOf(File::class, $file);
        self::assertEquals(1, $m->ref(File::class)->action('count')->getOne());
    }

    public function testAddTypeToFile()
    {;
        $m = new ModelWithFileRelation($this->persistence);
        $m->save();
        $m->getRef(File::class)->model = new  FileMock($this->persistence);
        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/']);
        self::assertEquals('', $file->get('type'));

        $file = $m->addUploadFileFromAtkUi(['name' => 'demo_file.txt', 'path' => 'tests/'], 'SOMETYPE');
        self::assertEquals('SOMETYPE', $file->get('type'));
    }
}
