<?php

namespace PMRAtk\tests\phpunit;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

    public static $app;


    /*
     *
     */
    public static function setUpBeforeClass():void {
        self::$app = new TestApp(['admin']);
    }


    /*
     *
     */
    public static function tearDownAfterClass():void {
        self::$app = null;
    }


    /*
     *
     */
    public function setUp():void {
        self::$app->queryCount = 0;
        // start transaction
        self::$app->db->connection->beginTransaction();
        //add Headline to log to see which test function called which DB requests
        self::$app->addLogHeadLine('Test: '.$this->getName());
    }


    /*
     *
     */
    public function commit() {
        self::$app->db->connection->commit();
    }


    /*
     *
     */
    public function tearDown():void {
        //log query count
        self::$app->addLogFootLine('Total Queries in '.$this->getName().': '.self::$app->queryCount);
        // rollback after each test
        if(self::$app->db->connection->inTransaction()) {
            self::$app->db->connection->rollback();
        }
    }


    /*
     * Calls protected method.
     */
    public function callProtected($obj, $name, array $args = []) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }



    /*
     * copies a test file to use with function
     */
    protected function _copyFile(string $filename, string $path = '') {
        return copy(FILE_BASE_PATH.'tests/demo-img.jpg', FILE_BASE_PATH.$path.$filename);
    }


    /*
     * counts the files in a dir with a certain extension
     * useful for tests for custom RegisterForm, custom BC etc
     */
    public function countFilesInDirWithExtension(string $dir, string $extension):int {
        $count = 0;
        foreach(new \DirectoryIterator($dir) as $file) {
            if(strtolower($file->getExtension()) === strtolower($extension)) {
                $count++;
            }
        }
        return $count;
    }


    /*
     * see if object has an audit set, returns this audit entry for further testing
     */
    protected function _testAuditExists(\EOO\Data\BaseModel $m, string $type) {
        $audit = $m->getAuditViewModel();
        $audit->addCondition('value', $type);
        $audit->tryLoadAny();
        $this->assertTrue($audit->loaded());
        return clone $audit;
    }


    /*
     *
     */
    public function createTestFile(string $filename, string $path = '', \PMRAtk\Data\BaseModel $parent = null) {
        $file = new \PMRAtk\Data\File(self::$app->db, ['parentObject' => $parent]);
        $file->set('path', $path);
        $file->createFileName($filename);
        $this->_copyFile($file->get('value'), $file->get('path'));
        $file->save();

        return clone $file;
    }


    /*
     *
     */
    protected function _testMToM(\PMRAtk\Data\BaseModel $o, \PMRAtk\Data\BaseModel $other) {
        if(!$o->loaded()) {
            $o->save();
        }
        if(!$other->loaded()) {
            $other->save();
        }

        $shortname = (new \ReflectionClass($other))->getShortName();
        $hasname = 'has'.$shortname.'Relation';
        $addname = 'add'.$shortname;
        $removename = 'remove'.$shortname;
        $this->assertFalse($o->$hasname($other));
        $this->assertTrue($o->$addname($other));
        $this->assertTrue($o->$hasname($other));
        $this->assertTrue($o->$removename($other));
        $this->assertFalse($o->$hasname($other));
    }


    /*
     *
     */
    public function countModelRecords(string $model_class) {
        return intval((new $model_class(self::$app->db))->action('count')->getOne());
    }


    /*
     * create a UUID for email testing and set it to $_ENV
     */
    public function getEmailUUID():string {
        $_ENV['TEST_EMAIL_UUID'] = uniqid();
        return $_ENV['TEST_EMAIL_UUID'];
    }
}
