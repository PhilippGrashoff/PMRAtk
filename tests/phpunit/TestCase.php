<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit;

use atk4\data\Model;
use DirectoryIterator;
use PMRAtk\Data\BaseModel;
use PMRAtk\Data\Email\EmailAccount;
use PMRAtk\Data\File;
use PMRAtk\Data\Setting;
use ReflectionClass;
use PMRAtk\tests\TestClasses\DeleteSetting;

abstract class TestCase extends \atk4\core\AtkPhpunit\TestCase
{

    public static $app;

    public static function setUpBeforeClass(): void
    {
        self::$app = new TestApp(['admin']);
        self::$app->isTestMode = true;
    }

    public static function tearDownAfterClass(): void
    {
        self::$app = null;
    }

    public function setUp(): void
    {
        self::$app->queryCount = 0;
        self::$app->db->connection->beginTransaction();
    }

    public function commit()
    {
        self::$app->db->connection->commit();
    }

    public function tearDown(): void
    {
        if (self::$app->db->connection->inTransaction()) {
            self::$app->db->connection->rollback();
        }
    }

    protected function copyFile(string $filename, string $path = ''): bool {
        if(file_exists(FILE_BASE_PATH . SAVE_FILES_IN . $filename)) {
            return copy(
                FILE_BASE_PATH . SAVE_FILES_IN . $filename,
                $this->addDirectorySeperatorToPath($path) . $filename
            );
        }
        return copy(
            FILE_BASE_PATH . SAVE_FILES_IN . '/demo-img.jpg',
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
        $this->assertTrue($audit->loaded());
        return clone $audit;
    }

    public function createTestFile(string $filename, string $path = '', BaseModel $parent = null)
    {
        $file = new File(self::$app->db, ['parentObject' => $parent]);
        $file->set('path', $path);
        $file->createFileName($filename);
        $this->copyFile($file->get('value'), $file->get('path'));
        $file->save();

        return clone $file;
    }

    protected function _testMToM(Model $o, Model $other)
    {
        if (!$o->loaded()) {
            $o->save();
        }
        if (!$other->loaded()) {
            $other->save();
        }

        $shortname = (new ReflectionClass($other))->getShortName();
        $hasname = 'has' . $shortname . 'Relation';
        $addname = 'add' . $shortname;
        $removename = 'remove' . $shortname;
        $getRelationName = 'get' . $shortname . 's';
        $this->assertFalse($o->$hasname($other));
        $this->assertTrue($o->$addname($other));
        $this->assertTrue($o->$hasname($other));
        if (method_exists($o, $getRelationName)) {
            $m = $o->$getRelationName();
            if ($m instanceof Model) {
                self::assertEquals(1, $o->$getRelationName()->action('count')->getOne());
            }
        }
        $this->assertTrue($o->$removename($other));
        $this->assertFalse($o->$hasname($other));
    }

    public function countModelRecords(string $model_class)
    {
        return intval((new $model_class(self::$app->db))->action('count')->getOne());
    }

    public function getEmailUUID(): string
    {
        $_ENV['TEST_EMAIL_UUID'] = uniqid();
        return $_ENV['TEST_EMAIL_UUID'];
    }

    protected function _addStandardEmailAccount()
    {
        $ea = new EmailAccount(self::$app->db);
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

    protected function _removeSettings(array $names)
    {
        foreach ($names as $name) {
            $setting = new DeleteSetting(self::$app->db);
            $setting->tryLoadBy('ident', $name);
            if ($setting->loaded()) {
                $setting->delete();
            }
        }
    }

    protected function _addSettingToApp(string $ident, $value)
    {
        $s = new Setting(self::$app->db);
        $s->tryLoadBy('ident', $ident);
        $s->set('ident', $ident);
        $s->set('value', $value);
        $s->save();
        self::$app->unloadSettings();
    }
}
