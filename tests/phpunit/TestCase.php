<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit;

use atk4\data\Model;
use atk4\data\Persistence;
use DirectoryIterator;
use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Email\EmailAccount;
use PMRAtk\Data\File;
use ReflectionClass;
use PMRAtk\tests\TestClasses\DeleteSetting;

abstract class TestCase extends \traitsforatkdata\TestCase
{

    protected function copyFile(string $filename, string $path = ''): bool {
        if(!$path) {
            $path = FILE_BASE_PATH . SAVE_FILES_IN;
        }
        if(file_exists($this->addDirectorySeperatorToPath(FILE_BASE_PATH . SAVE_FILES_IN) . $filename)) {
            return copy(
                $this->addDirectorySeperatorToPath(FILE_BASE_PATH . SAVE_FILES_IN) . $filename,
                $this->addDirectorySeperatorToPath($path) . $filename
            );
        }
        return copy(
            $this->addDirectorySeperatorToPath(FILE_BASE_PATH . SAVE_FILES_IN) . 'demo-img.jpg',



            $this->addDirectorySeperatorToPath($path) . $filename
        );
    }

    protected function addDirectorySeperatorToPath(string $path): string {
        if(substr($path, -1) !== DIRECTORY_SEPARATOR) {
            return $path . DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    public function countFilesInDirWithExtension(string $dir, string $extension): int
    {
        $count = 0;
        foreach (new DirectoryIterator($dir) as $file) {
            if (strtolower($file->getExtension()) === strtolower($extension)) {
                $count++;
            }
        }
        return $count;
    }

    protected function _testAuditExists(BaseModel $m, string $type)
    {
        $audit = $m->getAuditViewModel();
        $audit->addCondition('value', $type);
        $audit->tryLoadAny();
        self::assertTrue($audit->loaded());
        return clone $audit;
    }

    public function createTestFile(
        string $filename,
        Persistence $persistence,
        BaseModel $parent = null,
        string $path = ''
    ): File {
        if(!$path) {
            $path = SAVE_FILES_IN;
        }
        $file = new File($persistence, ['parentObject' => $parent]);
        $file->set('path', $path);
        $file->createFileName($filename);
        $this->copyFile($file->get('value'), $file->get('path'));
        $file->save();

        return $file;
    }


    protected function _testMToM(Model $model, Model $otherModel)
    {
        $shortname = (new ReflectionClass($otherModel))->getShortName();
        $hasname = 'has' . $shortname . 'Relation';
        $addname = 'add' . $shortname;
        $removename = 'remove' . $shortname;
        $getRelationName = 'get' . $shortname . 's';

        if(!method_exists($model, $addname)) {
            return;
        }

        if (!$model->loaded()) {
            $model->save();
        }
        if (!$otherModel->loaded()) {
            $otherModel->save();
        }

        self::assertFalse($model->$hasname($otherModel));
        self::assertTrue($model->$addname($otherModel));
        self::assertTrue($model->$hasname($otherModel));
        if (method_exists($model, $getRelationName)) {
            $m = $model->$getRelationName();
            if ($m instanceof Model) {
                self::assertEquals(1, $model->$getRelationName()->action('count')->getOne());
            }
        }
        self::assertTrue($model->$removename($otherModel));
        self::assertFalse($model->$hasname($otherModel));
    }

    protected function _addStandardEmailAccount(Persistence $persistence): EmailAccount
    {
        $ea = new EmailAccount($persistence);
        $ea->set('name', STD_EMAIL);
        $ea->set('sender_name', STD_EMAIL_NAME);
        $ea->set('user', EMAIL_USERNAME);
        $ea->set('password', EMAIL_PASSWORD);
        $ea->set('smtp_host', EMAIL_HOST);
        $ea->set('smtp_port', EMAIL_PORT);
        $ea->set('imap_host', IMAP_HOST);
        $ea->set('imap_port', IMAP_PORT);
        $ea->set('imap_sent_folder', IMAP_SENT_FOLDER);
        $ea->save();

        return $ea;
    }
}
