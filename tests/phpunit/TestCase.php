<?php

namespace PMRAtk\tests\phpunit;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

    public static $app;


    /*
     *
     */
    public static function setUpBeforeClass() {
        self::$app = new TestApp(['admin']);
    }


    /*
     *
     */
    public static function tearDownAfterClass() {
        self::$app = null;
    }


    /*
     *
     */
    function setUp() {
        // start transaction
        self::$app->db->connection->beginTransaction();
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
    function tearDown() {
        // rollback after each test
        if(self::$app->db->connection->inTransaction()) {
            self::$app->db->connection->rollback();
        }
    }


    /*
     * copies a test file to use with function
     */
    protected function _copyFile($filename) {
        return copy(FILE_BASE_PATH.'output/image/demo-img.jpg', FILE_BASE_PATH.'output/image/'.$filename);
    }


    /**
     * Calls protected method.
     *
     * NOTE: this method must only be used for low-level functionality, not
     * for general test-scripts.
     *
     * @param object $obj
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function callProtected($obj, $name, array $args = []) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
