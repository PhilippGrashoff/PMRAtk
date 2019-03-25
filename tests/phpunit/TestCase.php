<?php

namespace PMRAtk\tests\phpunit;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

    public static $app;
    public $dbQueryCounter = 0;


    /*
     *
     */
    public static function setUpBeforeClass() {
        self::$app = new TestApp(['admin']);
        self::$app->logger = new \PMRAtk\Data\FileLogger(FILE_BASE_PATH.'tests/logs/'.get_class($this).'.txt');
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
    public function setUp() {
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
    public function tearDown() {
        //log query count
        self::$app->addLogFootLine('Total Queries in '.$this->getName().': '.self::$app->queryCount);
        // rollback after each test
        if(self::$app->db->connection->inTransaction()) {
            self::$app->db->connection->rollback();
        }
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
